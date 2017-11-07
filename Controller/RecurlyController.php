<?php

App::uses('AppController', 'Controller');
App::import('Vendor', 'recurly/lib/recurly');
App::import('Vendor', 'hashids/lib/Hashids/HashGenerator');
App::import('Vendor', 'hashids/lib/Hashids/Hashids');

/**
 * Test Controller
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class RecurlyController extends AppController {

    /**
     * This controller does not use a model
     *
     * @var array
     */
    public $uses = array('UserMaster', 'PersonMaster', 'EntityTypeMaster', 'PlanMaster', 'EntityPlanDetail', 'RecurlyResponse', 'RecurlySubscription', 
        'StateMaster', 'CityMaster','RecurlyWebhookSubscription','Recurly');
    var $components = array('LinkedIn.LinkedIn', 'Breadcrumb');
    public $hashidObj;

    /**
     * Displays a view
     *
     * @return void
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public function __construct($request = null, $response = null) {
        parent::__construct($request, $response);
        Recurly_Client::$apiKey = RECURLY_API_KEY;
        $this->hashidObj = new Hashids\Hashids(HASHID_SALT, 10);
    }
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->checkSession();
    }

    // <editor-fold defaultstate="collapsed" desc="Billing Details.">
    public function selectPlan() {
        $response = array();
        if ($this->Session->check('user_id')) {
            $this->set("meta_title", $GLOBALS['meta_titles']['meta_select_plan']);
            $options = array();
            $options['fields'] = array('Country.id', 'Country.countryName');
            $countryMaster = $this->Country->find('list', $options);

            $options = array();
            $options['conditions'] = array('StateMaster.status' => 1);
            $stateMaster = $this->StateMaster->find('list', $options);

            $options = array();
            $options['conditions'] = array('CityMaster.status' => 1);
            $cityMaster = $this->CityMaster->find('list', $options);

            $this->set('countryMaster', $countryMaster);
            $this->set('stateMaster', $stateMaster);
            $this->set('cityMaster', $cityMaster);
            if ($this->Session->check('planInfo')) {
                $this->set('plan', $this->Session->read('planInfo'));
                if (!empty($this->Session->read('planInfo'))) {
                    $options = array();
                    $options['conditions'] = array('PlanMaster.plan_name' => $this->Session->read('planInfo'), 'PlanMaster.status' => 1);
                    $options['group'] = array('PlanMaster.plan_code');
                    $options['order'] = array('PlanMaster.plan_rate DESC');
                    $options['fields'] = array('PlanMaster.plan_code','PlanMaster.id','PlanMaster.plan_name','PlanMaster.plan_rate','PlanMaster.plan_subscription');
                    $planDetail = $this->PlanMaster->find('all', $options);
                    
                    $this->set('planDetail', $planDetail);
                }
            }
            if ($this->Session->check('BillingInfo')) {
                $this->set('billDetail', $this->Session->read('BillingInfo'));
            }

            if ($this->request->is('ajax') && $this->request->is('post')) {
                App::import('Model', 'Recurly');
                $this->recurlyObj = new Recurly();
                // <editor-fold defaultstate="collapsed" desc="Validate Post data of billing detail">
                $response = $this->recurlyObj->validateBillingDetails($this->request->data);
                // </editor-fold>
                if ($response['status'] == 'error') {
                    echo json_encode($response);
                    exit;
                }
                $billingInfo = array();
                $billingInfo = $this->request->data;
                $this->sanitizeAll($billingInfo);
                $this->Session->write('BillingInfo', $billingInfo);
                echo json_encode($response);
                exit;
            }
        } else {
            $this->redirect(array('action' => 'login'));
        }
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="payment integration">
    public function doPayment() {
        if ($this->Session->check('user_id')) {
            $this->set("meta_title", $GLOBALS['meta_titles']['meta_plan']);
            if ($this->Session->check('planInfo')) {
                $this->set('plan', $this->Session->read('planInfo'));
            }
            if ($this->Session->check('BillingInfo')) {
                $this->set("billingInfo", $this->Session->read('BillingInfo'));
            }
            if ($this->Session->check('recurlyErrorMsg')) {
                $this->set('errorMsg',$this->Session->read('recurlyErrorMsg'));
            }
            
        } else {
            $this->redirect(array('action' => 'login'));
        }
    }

    // </editor-fold>

    public function recurly_test() {
        $this->checkSession();
        /* App::import('Vendor', 'reculry/lib/recurly');
          Recurly_Client::$apiKey = '012345678901234567890123456789ab'; */
        $this->layout = '';
    }

    // <editor-fold defaultstate="collapsed" desc="Recurly subscription">
    public function raecurlySubscription() {
        $this->layout = '';
        //date_default_timezone_set('Asia/Kolkata');
        //echo "<pre>";print_r($_POST);echo "</pre>";exit;
        //echo $_POST['recurly-token'];exit;
        try {
            // <editor-fold defaultstate="collapsed" desc="Server side validation for the credit card">
            $response = $this->validateCreditCardNumber($_POST['card_number']);
            // </editor-fold>
            if ($response['status'] == 'false') {
                $this->Session->write('recurlyErrorMsg',$response['msg']);
                $this->redirect($this->referer());
            }
            // <editor-fold defaultstate="collapsed" desc="Server side validation for the credit card expiration date">
            $isValidExpireDate = $this->validateCreditCardExpirationDate($_POST['card_month'], $_POST['card_year']);
            // </editor-fold>
            if ($isValidExpireDate['status'] == 'false') {
                $this->Session->write('recurlyErrorMsg',$isValidExpireDate['msg']);
                $this->redirect($this->referer());
            }
            // <editor-fold defaultstate="collapsed" desc="Server side validation for the credit card cvv">
            $isValidCVV = $this->validateCVV($_POST['card_number'], $_POST['card_cvv']);
            // </editor-fold>
            if ($isValidCVV['status'] == 'false') {
                $this->Session->write('recurlyErrorMsg',$isValidCVV['msg']);
                $this->redirect($this->referer());
            }
            
            $this->Session->delete('recurlyErrorMsg');
            $options = array();
            $options['conditions'] = array('EntityPlanDetail.entity_user_id' => $GLOBALS['user_info']['UserMaster']['id']);
            $verifyPlanAndAccount = $this->EntityPlanDetail->find('count', $options);

            if ($verifyPlanAndAccount > 0) {
                $error = "You already have assigned plan";
                goto skipTryCatch;
            }

            $token = $_POST['recurly-token'];
            //Recurly_Client::$subdomain = 'dg';
            // Specify the minimum subscription attributes: plan_code, account, and currency
            $subscription = new Recurly_Subscription();
            $subscription->plan_code = $_POST['plan_code']; //'basic'
            $subscription->currency = $_POST['currency'];
            $subscription->quantity = $_POST['quantity'];

            // Create an account with a uniqid and the customer's first and last name
            $accountCode = uniqid();
            $subscription->account = new Recurly_Account($accountCode);
            $subscription->account->first_name = $_POST['first-name'];
            $subscription->account->last_name = $_POST['last-name'];
            $subscription->account->email = $_POST['email'];


            // Now we create a bare BillingInfo with a token
            $subscription->account->billing_info = new Recurly_BillingInfo();
            $subscription->account->billing_info->token_id = $token; //$_POST['recurly-token'];

            /* No other attributes are allowed when token is provided
             * $subscription->account->billing_info->number = $_POST['address1'];
              $subscription->account->billing_info->month = $_POST['address1'];
              $subscription->account->billing_info->year = $_POST['address1'];
              $subscription->account->billing_info->address1 = $_POST['address1'];
              $subscription->account->billing_info->city = $_POST['city'];
              $subscription->account->billing_info->state = $_POST['state'];
              $subscription->account->billing_info->country = $_POST['country'];
              $subscription->account->billing_info->postal_code = $_POST['postal-code']; */
            // Create the subscription
            $recurlyrequest = $subscription->getValues();

            $x = $subscription->create();
            $recurlyResponse = $subscription->getValues();
            
            // <editor-fold defaultstate="collapsed" desc="User's Billing Data">
             $userBillingData = array();
             $userBillingData['first_name'] = $_POST['first-name']; 
             $userBillingData['last_name'] = $_POST['last-name']; 
             $userBillingData['company'] = $_POST['company']; 
             $userBillingData['address1'] = $_POST['address1']; 
             //$userBillingData['address2'] = $_POST['last-name']; 
             $userBillingData['city'] = $_POST['city']; 
             $userBillingData['state'] = $_POST['state']; 
             $userBillingData['zip'] = $_POST['postal-code']; 
             $userBillingData['country'] = $_POST['country']; 
             //$userBillingData['phone'] = $_POST['last-name'];
             
             $userBillingDataJSON = json_encode($userBillingData);
            // </editor-fold>
            
            //echo "Request :- <pre>";print_r($recurlyrequest);echo "</pre>";//exit;
            //echo "Response :- <pre>";print_r($recurlyResponse);echo "</pre>";exit;
            // <editor-fold defaultstate="collapsed" desc="Save entity plan and subscription">
            $entityPlanDetails = array();
            $entityPlanDetails['entity_type_master_id'] = $GLOBALS['user_info']['EntityTypeMaster']['id'];
            $entityPlanDetails['plan_master_id'] = $_POST['planid']; //plan id from session;
            $entityPlanDetails['entity_user_id'] = $GLOBALS['user_info']['UserMaster']['id'];
            $entityPlanDetails['recurly_account_id'] = $accountCode;
            $entityPlanDetails['user_billing_data'] = $userBillingDataJSON;

            if ($this->EntityPlanDetail->save($entityPlanDetails)) {

                $recurlySubscription = array();

                $recurlySubscription['entity_plan_detail_id'] = $this->EntityPlanDetail->id;
                $recurlySubscription['user_master_id'] = $GLOBALS['user_info']['UserMaster']['id'];
                $recurlySubscription['recurly_account_code'] = $accountCode;
                $recurlySubscription['uuid'] = !empty($recurlyResponse['uuid']) ? $recurlyResponse['uuid'] : NULL;
                $recurlySubscription['state'] = !empty($recurlyResponse['state']) ? $recurlyResponse['state'] : NULL;
                $recurlySubscription['quantity'] = !empty($recurlyResponse['quantity']) ? $recurlyResponse['quantity'] : NULL;
                $recurlySubscription['unit_amount_in_cents'] = !empty($recurlyResponse['unit_amount_in_cents']) ? $recurlyResponse['unit_amount_in_cents'] : NULL;
                $recurlySubscription['activated_at'] = !empty($recurlyResponse['activated_at']) ? $recurlyResponse['activated_at']->format('Y-m-d H:i:s') : NULL;
                $recurlySubscription['updated_at'] = !empty($recurlyResponse['updated_at']) ? $recurlyResponse['updated_at']->format('Y-m-d H:i:s') : NULL;
                $recurlySubscription['current_period_started_at'] = !empty($recurlyResponse['current_period_started_at']) ? $recurlyResponse['current_period_started_at']->format('Y-m-d H:i:s') : NULL;
                $recurlySubscription['current_period_ends_at'] = !empty($recurlyResponse['current_period_ends_at']) ? $recurlyResponse['current_period_ends_at']->format('Y-m-d H:i:s') : NULL;
                $recurlySubscription['collection_method'] = !empty($recurlyResponse['collection_method']) ? $recurlyResponse['collection_method'] : NULL;

                if (!$this->RecurlySubscription->save($recurlySubscription)) {
                    $email = $GLOBALS['developerEmails'];
                    $message_subject = 'Error while saving subscription data';
                    $message_body = 'Email from signup process. Looks like Entity Plan is saved but subscription is not saved. '
                            . 'Entity Plan Detail data :- ' . json_encode($entityPlanDetails) .
                            ' ===== AND ==== Subscription Data :- ' . json_encode($recurlySubscription) .
                            ' ===== AND ==== Validation Error :- ' . !empty($this->RecurlySubscription->validationErrors) ? json_encode($this->RecurlySubscription->validationErrors) : 'No error';
                    $this->send_email_static($email, '', $message_subject, $message_body);
                }
            }
            // </editor-fold>


            $data = array();
            $data['plan_code'] = $recurlyResponse['plan_code'];
            $data['account_id'] = $recurlyrequest['account']->getValues()['account_code'];
            $data['account_href'] = $recurlyResponse['account']->getHref();
            $data['invoice_href'] = $recurlyResponse['invoice']->getHref();
            $data['uuid'] = $recurlyResponse['uuid'];
            $data['state'] = $recurlyResponse['state'];

            $save = array();
            $save['user_master_id'] = $GLOBALS['user_info']['UserMaster']['id'];
            $save['recurly_data'] = json_encode($data);
            $save['uuid'] = $recurlyResponse['uuid'];
            $this->RecurlyResponse->save($save);

            // <editor-fold defaultstate="collapsed" desc="Update Registration step">
            $update_step = array();
            $update_step['id'] = $this->Session->read('user_id');
            $update_step['registration_step'] = 2;
            $this->UserMaster->save($update_step);
            // </editor-fold>
            
        } catch (Exception $e) {
            // Assign the error message and use it to handle any customer
            // messages or logging
            //print "Error $e";exit;
            $error = $e->getMessage();
        }

        skipTryCatch:
        // Now we may wish to redirect to a confirmation
        // or back to the form to fix errors.
        if (isset($error)) {
            //echo "<pre>";print_r($error);echo "</pre>";
            //echo "<pre>";print_r($subscription);echo "</pre>";exit;
            //$this->redirect("ERROR_URL?error=$error");
            $this->redirect("raecurlySubscriptionError?error=$error");
        } else {
            //echo $subscription;exit;
            //echo "<pre>";print_r($subscription);echo "</pre>";exit;
            //$this->redirect("raecurlySubscriptionSuccess");
            $this->redirect(array('controller' => 'users', 'action' => 'add'));
        }

        //echo "<pre>";print_r($abc);echo "</pre>";
        //echo "<pre>";print_r($subscription);echo "</pre>";exit;exit;
        //echo "subscription created";exit;
    }
    // </editor-fold>

    public function recurlySubscriptionSuccess() {
        echo "Thank you for subscription";
        exit;
    }

    public function recurlySubscriptionError() {
        $error = "Error while subscribe.";
        if (!empty($_GET['error'])) {
            $error .= "<br/><br/> Error :- " . $_GET['error'];
        }
        echo $error;
        exit;
    }

    public function listSubscription() {
        $subscriptions = Recurly_SubscriptionList::getActive();
        foreach ($subscriptions as $subscription) {
            //print "Subscription: $subscription<br/>";
            echo "<pre>";
            print_r($subscription);
            echo "</pre>";
        }
        exit;
    }

    public function MySubscription() {
        $options = array();
        $options['conditions'] = array('user_master_id' => $GLOBALS['user_info']['UserMaster']['id']);
        $options['order'] = 'modified DESC';
        $getData = $this->RecurlyResponse->find('all', $options);

        foreach ($getData as $singleRecord) {
            $DecodeData = json_decode($singleRecord['RecurlyResponse']['recurly_data'], true);
            $cancelURL = SITE_URL . 'recurly/cancelSubscription/' . $DecodeData['uuid'];
            echo 'Cancel subscription   <a href="' . $cancelURL . '">' . $DecodeData['uuid'] . '</a><br/>';
        }
        exit;
    }

    // <editor-fold defaultstate="collapsed" desc="Cancel And Update Subscription, Add User Landing Page">
    public function listAccounts() {
        $this->checkSession();
        $GLOBALS['module_js'] = array('/js/recurly');
        /*$accounts = Recurly_AccountList::getActive();
        foreach ($accounts as $account) {
            echo "<pre>";print_r($account);echo "</pre>";
        }*/
        $preparePlans = array();
        
        $accessSubscription = 1;
        if(!empty($GLOBALS['user_info']['EntityPlanDetailParent']['plan_master_id'])){
            $accessSubscription=0;
            // currently child user can not change or update subscription
            goto end;
        }
        
        $options = array();
        $options['conditions'] = array('PlanMaster.status'=>1,'PlanMaster.type'=>$GLOBALS['user_info']['EntityTypeMaster']['type'],'PlanMaster.plan_type !='=>'free');
        $getPlans = $this->PlanMaster->find('all',$options);
        
        $currentPlan = array();
        foreach ($getPlans as $key => $value) {
            $display_string = $value['PlanMaster']['plan_name'] . " $" . $value['PlanMaster']['plan_rate'] . $value['PlanMaster']['plan_subscription'];
            $preparePlans[$value['PlanMaster']['id']] = $display_string;
            if($value['PlanMaster']['id']==$GLOBALS['user_info']['EntityPlanDetail']['plan_master_id']){
                $currentPlan['PlanMaster'] = $value['PlanMaster'];
            }
        }
        $this->set('currentPlan',$currentPlan);
        
        if(isset($preparePlans[$GLOBALS['user_info']['EntityPlanDetail']['plan_master_id']])){
            unset($preparePlans[$GLOBALS['user_info']['EntityPlanDetail']['plan_master_id']]);
        }
        
        //get users with free plans only
        $getColleagues = $this->Recurly->getColleagues();
        $this->set('colleagues',$getColleagues);
        
        $selectedUsers = $this->Recurly->getSelectedColleagues();
        $this->set('selectedUsers',$selectedUsers);
        
        $getCurrentSubscription = $this->Recurly->getCurrentSubscription();
        $this->set('getCurrentSubscription',$getCurrentSubscription);

        end:
            $this->set("accessSubscription",$accessSubscription);
            $this->set('plans',$preparePlans);
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Manage Users">
    public function manageUsers() {
        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'Error while adding users.';
        
        if ($this->request->is('post') && $this->request->is('ajax')) {
            // <editor-fold defaultstate="collapsed" desc="Validations and checks">
            if(empty($GLOBALS['user_info']['EntityPlanDetail']) || empty($GLOBALS['user_info']['EntityPlanDetail']['id'])){
                $response['msg'] = 'Details related to your plan could not be found. Please refresh the page.';
                goto end;
            }
            
            $getColleagues = $this->Recurly->getColleagues();
            if(empty($getColleagues)){
                $response['msg'] = 'No team member is available to manage.';
                goto end;
            }
            
            $getCurrentSubscription = $this->Recurly->getCurrentSubscription();
            if(empty($getCurrentSubscription)){
                $response['msg'] = 'Error while retrieving your current subscription.';
                goto end;
            }
            
            $postUsers = !empty($this->request->data['AddUser']['ids']) ? $this->request->data['AddUser']['ids'] : array();
            if(count($postUsers)+1 > $getCurrentSubscription['RecurlySubscription']['quantity']){
                $response['msg'] = 'Count of selected users are greater than the available limit including yourself.';
                goto end;
            }
            
            $listOfColleagues = array();
            foreach ($getColleagues as $singleData) {
                $listOfColleagues[] = $singleData['UserMaster']['id'];
            }
            
            foreach($postUsers as $userID){
                if(!in_array($userID, $listOfColleagues)){
                    $response['msg'] = 'Looks like some users are now not available. Please refresh the page.';
                    goto end;
                }
            }
            
            $dataSource = $this->EntityPlanDetail->getDataSource();
            $dataSource->begin();
            $success = true;
        
            $updateFields = array('EntityPlanDetail.parent_id'=>NULL);
            $updateConditions = array('EntityPlanDetail.parent_id'=>$GLOBALS['user_info']['EntityPlanDetail']['id'],'EntityPlanDetail.status'=>1);
            if(!$this->EntityPlanDetail->updateAll($updateFields,$updateConditions)){
                $success = false;
            }
            
            $updateFields = array('EntityPlanDetail.parent_id'=>$GLOBALS['user_info']['EntityPlanDetail']['id']);
            $updateConditions = array('EntityPlanDetail.entity_user_id'=>$postUsers,'EntityPlanDetail.status'=>1);
            if(!$this->EntityPlanDetail->updateAll($updateFields,$updateConditions)){
                $success = false;
            }
            
            if($success===true){
                $dataSource->commit();
                $response['status'] = 'success';
                $response['msg'] = 'Your changes saved successfully.';
            }else{
                $dataSource->rollback();
            }
            // </editor-fold>
        }
        
        end:
            echo json_encode($response);exit;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Update Subscription">
    public function updateSubscription() {
        $this->checkSession();

        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'Data is not found';
        
        if(!empty($GLOBALS['user_info']['EntityPlanDetailParent']['plan_master_id'])){
            // currently child user can not change or update subscription
            $response['msg'] = 'You are not allowed to make changes in this. Contact your administrator.';
            goto end;
        }
        
        $getSubscription = $this->Recurly->getCurrentSubscription();

        //echo "<pre>";print_r($getSubscription);echo "</pre>";exit;
        if (empty($getSubscription)) {
            goto end;
        }

        if ($this->request->is('post') && $this->request->is('ajax')) {
            $planToUpdate = !empty($this->request->data['UpdateSubscription']['id']) ? $this->Recurly->getPlanDetail($this->request->data['UpdateSubscription']['id']) : array();
            $quantity = !empty($this->request->data['UpdateSubscription']['quantity']) && $this->request->data['UpdateSubscription']['quantity']>0 && $this->request->data['UpdateSubscription']['quantity']!=$getSubscription['RecurlySubscription']['quantity'] ? $this->request->data['UpdateSubscription']['quantity'] : '';
            /*if(empty($planToUpdate)){
                goto end;
            }*/
            
            if(!empty($planToUpdate) && $planToUpdate['PlanMaster']['id']==$getSubscription['PlanMaster']['id']){
                $response['msg'] = 'You are already on this plan.';
                goto end;
            }
            
            if(!empty($planToUpdate) && $planToUpdate['PlanMaster']['plan_type']!='enterprise'){
                $quantity=1; // set quantity to 1 if plan is not enterprise
            }
            
            if($quantity!=''){
                
                if(!($quantity>0 && $quantity==(int)$quantity)){
                    $response['msg'] = 'Please enter only positive integer values greater than 0 as quantity.';
                    goto end;
                }
                
                $selectedUsers = $this->Recurly->getSelectedColleagues('count');
                $selectedUsers = $selectedUsers+1;//include user (parent) itself;
                if($quantity < $selectedUsers){
                    $response['msg'] = "Please remove ".($selectedUsers-$quantity)." users from listing to make this changes.";
                    goto end;
                }
            }
            
            try {
                $update = false;
                $subscriptionUuid = $getSubscription['RecurlySubscription']['uuid'];
                $recurlySubscription = array();
                $subscription = Recurly_Subscription::get($subscriptionUuid);
                if(!empty($planToUpdate)){
                    $update = true;
                    $subscription->plan_code = $planToUpdate['PlanMaster']['plan_code'];
                }
                if($quantity!=''){
                    $update = true;
                    $subscription->quantity = (int)$quantity;
                }
                //$update=false;
                if($update===false){
                    $response['msg'] = 'No changes to made.';
                    goto end;
                }
                //$subscription->quantity = 2;
                $subscription->updateImmediately();     // Update immediately.
                // or $subscription->updateAtRenewal(); // Update when the subscription renews.

                //$recurlyResponse = $subscription->getValues();
                $recurlySubscription = $this->Recurly->setCommonSubscriptionFields($subscription);
                $recurlySubscription['id'] = $getSubscription['RecurlySubscription']['id'];
                
                if (!$this->RecurlySubscription->save($recurlySubscription)) {
                    $email = $GLOBALS['developerEmails'];
                    $message_subject = 'Error while cancelling subscription';
                    $message_body = 'Email from cancel subscription. Looks like subscription is cancelled but not updated in our db. '
                            . 'Entity subscription data before cancellation :- ' . json_encode($getSubscription) .
                            ' ===== AND ==== Subscription Data going to update :- ' . json_encode($recurlySubscription) .
                            ' ===== AND ==== Validation Error :- ' . !empty($this->RecurlySubscription->validationErrors) ? json_encode($this->RecurlySubscription->validationErrors) : 'No error';
                    $this->send_email_static($email, '', $message_subject, $message_body);
                }
                
                if(!empty($planToUpdate)){
                    //update plan mapping
                    $updatePlan = array();
                    $updatePlan['id'] = $getSubscription['EntityPlanDetail']['id'];
                    $updatePlan['plan_master_id'] = $planToUpdate['PlanMaster']['id'];
                    $this->EntityPlanDetail->save($updatePlan);
                }
                
                $response['status'] = 'success';
                $response['msg'] = 'Your subscription changes is made successfully';
            } catch (Recurly_ValidationError $e) {
                $response['msg'] = 'Invalid Subscription data.';
                //print "Invalid Subscription data: $e";
            } catch (Recurly_NotFoundError $e) {
                $response['msg'] = 'Subscription Not Found.';
                //print "Subscription Not Found: $e";
            }
        }
        
        end:

        echo json_encode($response);
        exit;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="List Invoices">
    public function listInvoices() {
        $this->set('hashidObj', $this->hashidObj);
        $message = '';
        if(empty($GLOBALS['user_info']['EntityPlanDetail']) || empty($GLOBALS['user_info']['EntityPlanDetail']['recurly_account_id'])){
            $message = 'Invalid account id';
            goto end;
        }
        
        $usersInvoices = array();
        try {
            $invoices = Recurly_InvoiceList::getForAccount($GLOBALS['user_info']['EntityPlanDetail']['recurly_account_id']);
            if(!empty($invoices)){
                $usersInvoices = $invoices;
            }
            /*foreach ($invoices as $invoice) {
                print "Invoice: {$invoice}\n";
            }*/
        } catch (Recurly_NotFoundError $e) {
            $message = "Account not found";
            //print "Account not found: $e";
        }
        //exit;
        end:
            $this->set('message',$message);
            $this->set('usersInvoices',$usersInvoices);
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Invoice in PDF">
    public function invoicePdf($invoice_number = '') {
        if ($invoice_number == '') {
            echo "please specify invoice number";
            exit;
        }
        $invoice_number = str_replace(".pdf","",$invoice_number);
        $actualInvoiceNumber = $this->hashidObj->decode($invoice_number);
        $actualInvoiceNumber = is_array($actualInvoiceNumber) ? $actualInvoiceNumber[0] : $actualInvoiceNumber;
        
        $filename = "invoice_".$actualInvoiceNumber.".pdf";
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        
        try {
            $pdf = Recurly_Invoice::getInvoicePdf($actualInvoiceNumber);
            echo $pdf;
            ob_clean();
            flush();
        } catch (Recurly_NotFoundError $e) {
            print "Invoice not found: $e";
        }
        exit;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Cancel Subscription.">
    public function cancelSubscription() {
        $this->checkSession();

        $response = array();
        $response['status'] = 'error';
        $response['msg'] = 'Data is not found';
        
        $getSubscription = $this->Recurly->getCurrentSubscription();

        //echo "<pre>";print_r($getSubscription);echo "</pre>";exit;
        if (empty($getSubscription)) {
            goto end;
        }

        if ($this->request->is('post') && $this->request->is('ajax')) {
            if (empty($getSubscription['RecurlySubscription']) || empty($getSubscription['RecurlySubscription']['uuid'])) {
                goto end;
            }
            $subscriptionUuid = $getSubscription['RecurlySubscription']['uuid'];
            
            try {
                $subscription = Recurly_Subscription::get($subscriptionUuid);
                $subscription->cancel();

                // <editor-fold defaultstate="collapsed" desc="Manage subscription cancel in db. remains active and then expires at the end of the current bill cycle">
                $recurlyResponse = $subscription->getValues();
                $recurlySubscription = $this->Recurly->setCommonSubscriptionFields($subscription);
                $recurlySubscription['id'] = $getSubscription['RecurlySubscription']['id'];
                
                if (!$this->RecurlySubscription->save($recurlySubscription)) {
                    $email = $GLOBALS['developerEmails'];
                    $message_subject = 'Error while cancelling subscription';
                    $message_body = 'Email from cancel subscription. Looks like subscription is cancelled but not updated in our db. '
                            . 'Entity subscription data before cancellation :- ' . json_encode($getSubscription) .
                            ' ===== AND ==== Subscription Data going to update :- ' . json_encode($recurlySubscription) .
                            ' ===== AND ==== Validation Error :- ' . !empty($this->RecurlySubscription->validationErrors) ? json_encode($this->RecurlySubscription->validationErrors) : 'No error';
                    $this->send_email_static($email, '', $message_subject, $message_body);
                }
                //echo "<pre>";print_r($recurlyResponse);echo "</pre>";exit;
                // </editor-fold>
                //print "Subscription: $subscription";
                $response['status'] = 'success';
                $response['msg'] = 'You plan is cancelled and will expire on next billing cycles :- '.date('F d, Y \a\t h:i a', strtotime($recurlySubscription['expires_at'])).' UTC';
            } catch (Recurly_NotFoundError $e) {
                $response['msg'] = "Subscription Not Found"; //$e
            } catch (Recurly_Error $e) {
                $response['msg'] = "Subscription already canceled"; //$e
            }
        }
        end:

        echo json_encode($response);
        exit;
    }

    // </editor-fold>
    
}