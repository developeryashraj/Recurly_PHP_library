<?php

App::uses('AppController', 'Controller');
App::import('Vendor', 'recurly/lib/recurly');

/**
 * Cron controller for recurly
 *
 * We can not transfer this cron to shell as recurly send headers with xml and not pure xml so loadXML of DOM document gives error if we run through shell
 * 
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
$GLOBALS['LIMITS']['webhookProcessLimit'] = 100;
define('RECURLY_API_KEY', 'RECURLY_PRIVATE_KEY');
class CronsRecurlyController extends AppController {

    public $uses = array('UserMaster','RecurlyWebhookSubscription','RecurlySubscription','EntityPlanDetail','PlanMaster',
        'RecurlyWebhookAccount','Recurly');
    private $recurlyControllerObj;
    
    public function __construct($stdout = null, $stderr = null, $stdin = null) {
        parent::__construct($stdout, $stderr, $stdin);

        Recurly_Client::$apiKey = RECURLY_API_KEY;
        //App::import('Controller', 'Recurly');
        //$this->recurlyControllerObj = new RecurlyController();
    }

    // <editor-fold defaultstate="collapsed" desc="Process Subscrption Related Webhooks.">
    /**
     * Logic is as per below
     * 1]. Set unix time for couple of records and get those records only for cron so that no two crons get overlapped.
     * 2]. We will not process each and every webhook entry and make changes accordingly in DB. Rather we plan to lookup the subscription by API 
     * for the lastest state and make changes from that lookup API response :- https://dev.recurly.com/docs/lookup-subscription-details
     * 3]. So that from the records we do group by on uuid and process for unique subscription. if there are more than one entry for any subscription like
     * Close subscription, remove or update subscription than we will consider it as one entry. Just for record that we recieve webhook for this subscription
     * and we have to lookup it. whether it is closed or updated.
     * 4]. After processing make changes in recurly subscriptions accordingly.
     * 5]. Make changes in entity_plan_details only if you get plan_code different than the currently assigned. (This will happen rarely)
     * 6]. Update cron flag for all records with same unix group and uuid.
     */
    public function processSubscriptionWebhooks() {
        
        $currentUnixTime = time();
        
        // <editor-fold defaultstate="collapsed" desc="Get List of ids to update cron_group_id with $currentUnixTime">
        $options = array();
        $options['fields'] = array('RecurlyWebhookSubscription.id','RecurlyWebhookSubscription.id');
        $options['conditions'] = array('RecurlyWebhookSubscription.cron_group_id'=>0, 'RecurlyWebhookSubscription.recurly_app_status' => 1,'RecurlyWebhookSubscription.cron_flag'=>0);
        $options['limit'] = $GLOBALS['LIMITS']['webhookProcessLimit'];
        $options['order'] = array('RecurlyWebhookSubscription.id'=>'ASC');
        $getSubscriptionLogLists = $this->RecurlyWebhookSubscription->find('list', $options);
        // </editor-fold>
        
        if(empty($getSubscriptionLogLists)){
            //no records to proceed
            goto end;
        }
        
        // <editor-fold defaultstate="collapsed" desc="update selected records with $currentUnixTime">
        $updateFields = array('RecurlyWebhookSubscription.cron_group_id' => $currentUnixTime, 'RecurlyWebhookSubscription.modified' => "'" . date('Y-m-d H:i:s') . "'");
        $updateConditions = array('RecurlyWebhookSubscription.id' => $getSubscriptionLogLists, 'RecurlyWebhookSubscription.cron_group_id'=>0, 'RecurlyWebhookSubscription.recurly_app_status' => 1);
        $this->RecurlyWebhookSubscription->updateAll($updateFields, $updateConditions);
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="Get records based on $currentUnixTime and group by uuid">
        $options = array();
        $options['fields'] = array('RecurlyWebhookSubscription.id','RecurlyWebhookSubscription.uuid');
        $options['group'] = array('RecurlyWebhookSubscription.uuid');
        $options['conditions'] = array('RecurlyWebhookSubscription.recurly_app_status' => 1, 'RecurlyWebhookSubscription.cron_group_id' => $currentUnixTime,'RecurlyWebhookSubscription.cron_flag'=>0);
        $options['order'] = array('RecurlyWebhookSubscription.id'=>'ASC');
        $getSubscriptionLogs = $this->RecurlyWebhookSubscription->find('list', $options);
        // </editor-fold>
        
        if(empty($getSubscriptionLogs)){
            //no records to proceed
            goto end;
        }
        
        foreach($getSubscriptionLogs as $singleData){
            $subscriptonUUID = $singleData;
            try {
                
                $getSubscription = $this->Recurly->getSubscriptionFromUUID($subscriptonUUID);
                if(empty($getSubscription)){
                    goto end;
                }
                
                $subscription = Recurly_Subscription::get($subscriptonUUID);
                
                $recurlySubscription = $this->Recurly->setCommonSubscriptionFields($subscription);
                $recurlySubscription['id'] = $getSubscription['RecurlySubscription']['id'];
                
                if (!$this->RecurlySubscription->save($recurlySubscription)) {
                    // <editor-fold defaultstate="collapsed" desc="Email to developers regarding errors">
                    $email = $GLOBALS['developerEmails'];
                    $message_subject = 'Error while updating subscription from recurly cron';
                    $message_body = 'Email from recurly cron. Looks like error while updating subscription. '
                            . 'Subscription UUID :- ' . $subscriptonUUID .
                            'Cron Group id (UNIX TIME) :- ' . $currentUnixTime .
                            ' ===== AND ==== Subscription Data going to update :- ' . json_encode($recurlySubscription) .
                            ' ===== AND ==== Validation Error :- ' . !empty($this->RecurlySubscription->validationErrors) ? json_encode($this->RecurlySubscription->validationErrors) : 'No error';
                    $this->send_email_static($email, '', $message_subject, $message_body);
                    // </editor-fold>
                    
                    goto tryEnd;
                }
                
                // <editor-fold defaultstate="collapsed" desc="Manage Plan according to the plan code and subscription status">
                $stateToConvertFreePlan = array('expired','future');
                /**
                 * Logic :-
                 * 1]. if subscription state is from $stateToConvertFreePlan than set plan id with free plan. Process this only if db state is other than $stateToConvertFreePlan and skip section of updating plan based on plan code.
                 * 2]. if state in recurly response is different than $stateToConvertFreePlan than set plan id based on plan code we recieve from recurly if both (DB and recurly) plan codes are different.
                 */
                if(!(!empty($getSubscription['EntityPlanDetail']) && !empty($getSubscription['EntityPlanDetail']['PlanMaster']))){
                    // do not process if plan master or entity plan detail is empty
                    goto updateWebhook;
                }
                
                // <editor-fold defaultstate="collapsed" desc="Handle if subscription is expired or set for future">
                /**
                 * change plan to free if expire. get state related info here :- https://dev.recurly.com/docs/list-subscriptions#section-subscription-query-states
                 */
                if(!empty($subscription->state) && in_array($subscription->state,$stateToConvertFreePlan) && !in_array($getSubscription['RecurlySubscription']['state'],$stateToConvertFreePlan)){
                    $getFreePlan = $this->Recurly->getFreePlanIDofSameCategory($getSubscription['EntityPlanDetail']['PlanMaster']['type']);
                    if(empty($getFreePlan)){
                        goto end;
                    }
                    
                    $updatePlan = array();
                    $updatePlan['id'] = $getSubscription['EntityPlanDetail']['id'];
                    $updatePlan['plan_master_id'] = $getFreePlan['PlanMaster']['id'];
                    
                    if($this->EntityPlanDetail->save($updatePlan)){
                        goto updateWebhook;
                    }
                }
                // </editor-fold>
                
                // <editor-fold defaultstate="collapsed" desc="Update plan if plan code is different than in DB and state is not future or expire">
                if(!in_array($subscription->state,$stateToConvertFreePlan) && $getSubscription['EntityPlanDetail']['PlanMaster']['plan_code']!=$subscription->plan->plan_code){
                    $options = array();
                    $options['fields'] = array('PlanMaster.id');
                    $options['conditions'] = array('PlanMaster.plan_code'=>$subscription->plan->plan_code,'PlanMaster.status'=>1);
                    $getPlan = $this->PlanMaster->find('first',$options);
                    
                    if(!empty($getPlan)){
                        //update plan mapping
                        $updatePlan = array();
                        $updatePlan['id'] = $getSubscription['EntityPlanDetail']['id'];
                        $updatePlan['plan_master_id'] = $getPlan['PlanMaster']['id'];
                        $this->EntityPlanDetail->save($updatePlan);
                    }
                    
                }
                // </editor-fold>
                
                // </editor-fold>
                
                updateWebhook:
                
                $updateFields = array('RecurlyWebhookSubscription.cron_flag'=>1, 'RecurlyWebhookSubscription.modified' => "'" . date('Y-m-d H:i:s') . "'");
                $updateConditions = array('RecurlyWebhookSubscription.cron_group_id' => $currentUnixTime, 'RecurlyWebhookSubscription.uuid' => $subscriptonUUID);
                $this->RecurlyWebhookSubscription->updateAll($updateFields, $updateConditions);
                
                tryEnd:
                
            } catch (Recurly_NotFoundError $e) {
                print "Subscription Not Found: $e";
            }
        }
        
        end:
            //cron completed
            echo "cron completed";exit;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Process Account related webhooks. Excluding update billing info">
    /**
     * Logic is as per below
     * 
     */
    public function processAccountWebhooks() {
        
        $currentUnixTime = time();
        
        // <editor-fold defaultstate="collapsed" desc="Get List of ids to update cron_group_id with $currentUnixTime">
        $options = array();
        $options['fields'] = array('RecurlyWebhookAccount.id','RecurlyWebhookAccount.id');
        $options['conditions'] = array('RecurlyWebhookAccount.type !='=>'billing_info_updated_notification','RecurlyWebhookAccount.cron_group_id'=>0, 'RecurlyWebhookAccount.recurly_app_status' => 1,'RecurlyWebhookAccount.cron_flag'=>0);
        $options['limit'] = $GLOBALS['LIMITS']['webhookProcessLimit'];
        $options['order'] = array('RecurlyWebhookAccount.id'=>'ASC');
        $getAccountLogLists = $this->RecurlyWebhookAccount->find('list', $options);
        // </editor-fold>
        
        if(empty($getAccountLogLists)){
            //no records to proceed
            goto end;
        }
        
        // <editor-fold defaultstate="collapsed" desc="update selected records with $currentUnixTime">
        $updateFields = array('RecurlyWebhookAccount.cron_group_id' => $currentUnixTime, 'RecurlyWebhookAccount.modified' => "'" . date('Y-m-d H:i:s') . "'");
        $updateConditions = array('RecurlyWebhookAccount.type !='=>'billing_info_updated_notification','RecurlyWebhookAccount.id' => $getAccountLogLists, 'RecurlyWebhookAccount.cron_group_id'=>0, 'RecurlyWebhookAccount.recurly_app_status' => 1);
        $this->RecurlyWebhookAccount->updateAll($updateFields, $updateConditions);
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="Get records based on $currentUnixTime and group by account_code">
        $options = array();
        $options['fields'] = array('RecurlyWebhookAccount.id','RecurlyWebhookAccount.account_code');
        $options['group'] = array('RecurlyWebhookAccount.account_code');
        $options['conditions'] = array('RecurlyWebhookAccount.type !='=>'billing_info_updated_notification','RecurlyWebhookAccount.recurly_app_status' => 1, 'RecurlyWebhookAccount.cron_group_id' => $currentUnixTime,'RecurlyWebhookAccount.cron_flag'=>0);
        $options['order'] = array('RecurlyWebhookAccount.id'=>'ASC');
        $getSubscriptionLogs = $this->RecurlyWebhookAccount->find('list', $options);
        // </editor-fold>
        
        if(empty($getSubscriptionLogs)){
            //no records to proceed
            goto end;
        }
        
        foreach($getSubscriptionLogs as $singleData){
            $accountCode = $singleData;
            try {
                
                $getSubscription = $this->Recurly->getEntityPlanDetailFromAccountCode($accountCode);
                if(empty($getSubscription)){
                    goto end;
                }
                
                $account = Recurly_Account::get($accountCode);
                
                $recurlyAccount = $this->Recurly->setCommonAccountFields($account);
                $recurlyAccount['id'] = $getSubscription['EntityPlanDetail']['id'];
                
                if (!$this->EntityPlanDetail->save($recurlyAccount)) {
                    // <editor-fold defaultstate="collapsed" desc="Email to developers regarding errors">
                    $email = $GLOBALS['developerEmails'];
                    $message_subject = 'Error while updating account from recurly cron';
                    $message_body = 'Email from recurly cron. Looks like error while updating account details. '
                            . 'Account code :- ' . $accountCode .
                            'Cron Group id (UNIX TIME) :- ' . $currentUnixTime .
                            ' ===== AND ==== Subscription Data going to update :- ' . json_encode($recurlyAccount) .
                            ' ===== AND ==== Validation Error :- ' . !empty($this->EntityPlanDetail->validationErrors) ? json_encode($this->EntityPlanDetail->validationErrors) : 'No error';
                    $this->send_email_static($email, '', $message_subject, $message_body);
                    // </editor-fold>
                    
                    goto tryEnd;
                }
                
                // <editor-fold defaultstate="collapsed" desc="Manage Plan if account status is expire">
                $stateToConvertFreePlan = array('closed');
                
                if(empty($getSubscription['EntityPlanDetail'])){
                    // do not process if plan master or entity plan detail is empty
                    goto updateWebhook;
                }
                
                // <editor-fold defaultstate="collapsed" desc="Change plan to free if recurly state is closed and current db status is not closed">
                if(!empty($account->state) && in_array($account->state, $stateToConvertFreePlan) && !in_array($getSubscription['EntityPlanDetail']['state'], $stateToConvertFreePlan)){
                    $getFreePlan = $this->Recurly->getFreePlanIDofSameCategory($getSubscription['PlanMaster']['type']);
                    if(empty($getFreePlan)){
                        goto end;
                    }
                    
                    $updatePlan = array();
                    $updatePlan['id'] = $getSubscription['EntityPlanDetail']['id'];
                    $updatePlan['plan_master_id'] = $getFreePlan['PlanMaster']['id'];
                    if($this->EntityPlanDetail->save($updatePlan)){
                        goto updateWebhook;
                    }
                }
                // </editor-fold>
                
                // <editor-fold defaultstate="collapsed" desc="Change Plan status and plan code if status change from closed to active">
                /*
                 * Logic :-
                 * 1]. If account state is change to active from close than just update any of the subscription related record using uuid with cron_flag=0 and cron_group_id=0. Rest will be handle by processSubscriptionWebhooks cron.
                 * 2]. Otherwise you have to call lookup subscription API here to get plan_code (as plan code not return in account lookup) and change plan as per plan_code.
                 * 
                 * First option may take some time but we will have latest account and subscription status.
                 */
                if(!empty($account->state) && !in_array($account->state, $stateToConvertFreePlan) && in_array($getSubscription['EntityPlanDetail']['state'], $stateToConvertFreePlan)){
                    $options = array();
                    $options['conditions'] = array('RecurlyWebhookSubscription.uuid'=>$getSubscription['RecurlySubscription']['uuid'],'RecurlyWebhookSubscription.recurly_app_status'=>1);
                    $options['order'] = array('RecurlyWebhookSubscription.id'=>'DESC');
                    $getLastRecord = $this->RecurlyWebhookSubscription->find('first',$options);
                    
                    if(!empty($getLastRecord) && $getLastRecord['RecurlyWebhookSubscription']['cron_flag']!=0 && $getLastRecord['RecurlyWebhookSubscription']['cron_group_id']!=0){
                        $updateSubscription = array();
                        $updateSubscription['id'] = $getLastRecord['RecurlyWebhookSubscription']['id'];
                        $updateSubscription['cron_flag'] = 0;
                        $updateSubscription['cron_group_id'] = 0;
                        
                        $this->RecurlyWebhookSubscription->save($updateSubscription);
                    }
                    
                }
                // </editor-fold>
                                
                // </editor-fold>
                
                updateWebhook:
                    
                $updateFields = array('RecurlyWebhookAccount.cron_flag'=>1, 'RecurlyWebhookAccount.modified' => "'" . date('Y-m-d H:i:s') . "'");
                $updateConditions = array('RecurlyWebhookAccount.type !='=>'billing_info_updated_notification','RecurlyWebhookAccount.cron_group_id' => $currentUnixTime, 'RecurlyWebhookAccount.account_code' => $accountCode);
                $this->RecurlyWebhookAccount->updateAll($updateFields, $updateConditions);
                
                tryEnd:
            } catch (Recurly_NotFoundError $e) {
                print "Subscription Not Found: $e";
            }
        }
        
        end:
            //cron completed
            echo "cron completed";exit;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Process Account related webhooks. Update billing info ONLY">
    /**
     * Logic is as per below
     * 
     */
    public function processAccountBillingWebhooks() {
        
        $currentUnixTime = time();
        
        // <editor-fold defaultstate="collapsed" desc="Get List of ids to update cron_group_id with $currentUnixTime">
        $options = array();
        $options['fields'] = array('RecurlyWebhookAccount.id','RecurlyWebhookAccount.id');
        $options['conditions'] = array('RecurlyWebhookAccount.type'=>'billing_info_updated_notification','RecurlyWebhookAccount.cron_group_id'=>0, 'RecurlyWebhookAccount.recurly_app_status' => 1,'RecurlyWebhookAccount.cron_flag'=>0);
        $options['limit'] = $GLOBALS['LIMITS']['webhookProcessLimit'];
        $options['order'] = array('RecurlyWebhookAccount.id'=>'ASC');
        $getAccountLogLists = $this->RecurlyWebhookAccount->find('list', $options);
        // </editor-fold>
        
        if(empty($getAccountLogLists)){
            //no records to proceed
            goto end;
        }
        
        // <editor-fold defaultstate="collapsed" desc="update selected records with $currentUnixTime">
        $updateFields = array('RecurlyWebhookAccount.cron_group_id' => $currentUnixTime, 'RecurlyWebhookAccount.modified' => "'" . date('Y-m-d H:i:s') . "'");
        $updateConditions = array('RecurlyWebhookAccount.type'=>'billing_info_updated_notification','RecurlyWebhookAccount.id' => $getAccountLogLists, 'RecurlyWebhookAccount.cron_group_id'=>0, 'RecurlyWebhookAccount.recurly_app_status' => 1);
        $this->RecurlyWebhookAccount->updateAll($updateFields, $updateConditions);
        // </editor-fold>
        
        // <editor-fold defaultstate="collapsed" desc="Get records based on $currentUnixTime and group by account_code">
        $options = array();
        $options['fields'] = array('RecurlyWebhookAccount.id','RecurlyWebhookAccount.account_code');
        $options['group'] = array('RecurlyWebhookAccount.account_code');
        $options['conditions'] = array('RecurlyWebhookAccount.type'=>'billing_info_updated_notification','RecurlyWebhookAccount.recurly_app_status' => 1, 'RecurlyWebhookAccount.cron_group_id' => $currentUnixTime,'RecurlyWebhookAccount.cron_flag'=>0);
        $options['order'] = array('RecurlyWebhookAccount.id'=>'ASC');
        $getSubscriptionLogs = $this->RecurlyWebhookAccount->find('list', $options);
        // </editor-fold>
        
        if(empty($getSubscriptionLogs)){
            //no records to proceed
            goto end;
        }
        
        foreach($getSubscriptionLogs as $singleData){
            $accountCode = $singleData;
            try {
                
                $getSubscription = $this->Recurly->getEntityPlanDetailFromAccountCode($accountCode);
                if(empty($getSubscription)){
                    goto end;
                }
                
                $billing_info = Recurly_BillingInfo::get($accountCode);
                
                $recurlyBillingInfo = $this->Recurly->setCommonBillingInfoFields($billing_info);
                $recurlyBillingInfo['id'] = $getSubscription['EntityPlanDetail']['id'];
                
                if (!$this->EntityPlanDetail->save($recurlyBillingInfo)) {
                    // <editor-fold defaultstate="collapsed" desc="Email to developers regarding errors">
                    $email = $GLOBALS['developerEmails'];
                    $message_subject = 'Error while updating account from recurly cron';
                    $message_body = 'Email from recurly cron. Looks like error while updating billing info. '
                            . 'Account code :- ' . $accountCode .
                            'Cron Group id (UNIX TIME) :- ' . $currentUnixTime .
                            ' ===== AND ==== Billing info going to update :- ' . json_encode($recurlyBillingInfo) .
                            ' ===== AND ==== Validation Error :- ' . !empty($this->EntityPlanDetail->validationErrors) ? json_encode($this->EntityPlanDetail->validationErrors) : 'No error';
                    $this->send_email_static($email, '', $message_subject, $message_body);
                    // </editor-fold>
                    
                    goto tryEnd;
                }
                
                $updateFields = array('RecurlyWebhookAccount.cron_flag'=>1, 'RecurlyWebhookAccount.modified' => "'" . date('Y-m-d H:i:s') . "'");
                $updateConditions = array('RecurlyWebhookAccount.type'=>'billing_info_updated_notification','RecurlyWebhookAccount.cron_group_id' => $currentUnixTime, 'RecurlyWebhookAccount.account_code' => $accountCode);
                $this->RecurlyWebhookAccount->updateAll($updateFields, $updateConditions);
                
                tryEnd:
            } catch (Recurly_NotFoundError $e) {
                print "Subscription Not Found: $e";
            }
        }
        
        end:
            //cron completed
            echo "cron completed";exit;
    }
    // </editor-fold>
    
}