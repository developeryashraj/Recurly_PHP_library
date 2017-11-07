<?php

/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class Recurly extends AppModel {

    // <editor-fold defaultstate="collapsed" desc="This function will validate billing details">
    function validateBillingDetails($data = '') {
        $response = array();
        $response['status'] = 'success';
        if (empty($data)) {
            $response['msg'] = 'Please fill all the neccessary billing information.';
            goto end;
        }
        if (empty($data['quantity'])) {
            $response['msg'] = 'Please enter Quantity.';
            goto end;
        }
        if (empty($data['plan'])) {
            $response['msg'] = 'Please select a Plan.';
            goto end;
        }
        if (empty($data['company'])) {
            $response['msg'] = 'Please enter Company name.';
            goto end;
        }
        if (empty($data['fullname'])) {
            $response['msg'] = 'Please enter Name.';
            goto end;
        }
        if (empty($data['email'])) {
            $response['msg'] = 'Please enter at lease one Email address.';
            goto end;
        }
        if (empty($data['address'])) {
            $response['msg'] = 'Please enter address.';
            goto end;
        }
        if (empty($data['country'])) {
            $response['msg'] = 'Please enter country.';
            goto end;
        }
        if (empty($data['state'])) {
            $response['msg'] = 'Please enter state.';
            goto end;
        }
        if (empty($data['city'])) {
            $response['msg'] = 'Please enter city.';
            goto end;
        }
        if (empty($data['zipcode'])) {
            $response['msg'] = 'Please enter zipcode.';
            goto end;
        }
        if (empty($data['currency'])) {
            $response['msg'] = 'Please enter currency.';
            goto end;
        }
        if (empty($data['plan_code'])) {
            $response['msg'] = 'Please select a Plan.';
            goto end;
        }


        end:
        if (isset($response['msg'])) {
            $response['status'] = 'error';
        }
        return $response;
    }

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Setting fields to update for subscription. Using in Recurly Shell Also so keep it public">
    public function setCommonSubscriptionFields($subscription='') {
        $recurlySubscription = array();
        
        if(empty($subscription)){
            goto end;
        }
        
        //$recurlyResponse = $subscription->getValues();
        
        $recurlySubscription['state'] = !empty($subscription->state) ? $subscription->state : NULL;
        $recurlySubscription['quantity'] = !empty($subscription->quantity) ? $subscription->quantity : NULL;
        $recurlySubscription['unit_amount_in_cents'] = !empty($subscription->unit_amount_in_cents) ? $subscription->unit_amount_in_cents : NULL;
        $recurlySubscription['activated_at'] = !empty($subscription->activated_at) ? $subscription->activated_at->format('Y-m-d H:i:s') : NULL;
        $recurlySubscription['canceled_at'] = !empty($subscription->canceled_at) ? $subscription->canceled_at->format('Y-m-d H:i:s') : NULL;
        $recurlySubscription['expires_at'] = !empty($subscription->expires_at) ? $subscription->expires_at->format('Y-m-d H:i:s') : NULL;
        $recurlySubscription['updated_at'] = !empty($subscription->updated_at) ? $subscription->updated_at->format('Y-m-d H:i:s') : NULL;
        $recurlySubscription['current_period_started_at'] = !empty($subscription->current_period_started_at) ? $subscription->current_period_started_at->format('Y-m-d H:i:s') : NULL;
        $recurlySubscription['current_period_ends_at'] = !empty($subscription->current_period_ends_at) ? $subscription->current_period_ends_at->format('Y-m-d H:i:s') : NULL;
        $recurlySubscription['collection_method'] = !empty($subscription->collection_method) ? $subscription->collection_method : NULL;
        
        end:
            return $recurlySubscription;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Setting fields to update Account. Using in Recurly cron Also so keep it public">
    public function setCommonAccountFields($account='') {
        $recurlyAccount = array();
        
        if(empty($account)){
            goto end;
        }
        
        //$recurlyResponse = $account->getValues();
        // <editor-fold defaultstate="collapsed" desc="XML response is converted to object and values are protect so can not do json_encode directly. So preparing Array">
        $prepareAccountArray = array();
        $prepareAccountArray['account_code'] = !empty($account->account_code) ? $account->account_code : NULL;
        $prepareAccountArray['state'] = !empty($account->state) ? $account->state : NULL;
        $prepareAccountArray['username'] = !empty($account->username) ? $account->username : NULL;
        $prepareAccountArray['email'] = !empty($account->email) ? $account->email : NULL;
        $prepareAccountArray['cc_emails'] = !empty($account->cc_emails) ? $account->cc_emails : NULL;
        $prepareAccountArray['first_name'] = !empty($account->first_name) ? $account->first_name : NULL;
        $prepareAccountArray['last_name'] = !empty($account->last_name) ? $account->last_name : NULL;
        $prepareAccountArray['company_name'] = !empty($account->company_name) ? $account->company_name : NULL;
        $prepareAccountArray['address']['address1'] = !empty($account->address->address1) ? $account->address->address1 : NULL;
        $prepareAccountArray['address']['address2'] = !empty($account->address->address2) ? $account->address->address2 : NULL;
        $prepareAccountArray['address']['city'] = !empty($account->address->city) ? $account->address->city : NULL;
        $prepareAccountArray['address']['state'] = !empty($account->address->state) ? $account->address->state : NULL;
        $prepareAccountArray['address']['zip'] = !empty($account->address->zip) ? $account->address->zip : NULL;
        $prepareAccountArray['address']['country'] = !empty($account->address->country) ? $account->address->country : NULL;
        $prepareAccountArray['address']['phone'] = !empty($account->address->phone) ? $account->address->phone : NULL;
        $prepareAccountArray['hosted_login_token'] = !empty($account->hosted_login_token) ? $account->hosted_login_token : NULL;
        $prepareAccountArray['created_at'] = !empty($account->created_at) ? $account->created_at->format('Y-m-d H:i:s') : NULL;
        $prepareAccountArray['updated_at'] = !empty($account->updated_at) ? $account->updated_at->format('Y-m-d H:i:s') : NULL;
        $prepareAccountArray['closed_at'] = !empty($account->closed_at) ? $account->closed_at->format('Y-m-d H:i:s') : NULL;
        // </editor-fold>
        
        $recurlyAccount['state'] = !empty($account->state) ? $account->state : NULL;
        $recurlyAccount['recurly_account_data'] = json_encode($prepareAccountArray);
        
        end:
            return $recurlyAccount;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Setting fields to update Account. Using in Recurly cron Also so keep it public">
    public function setCommonBillingInfoFields($billing_info='') {
        $recurlyBillingInfo = array();
        
        if(empty($billing_info)){
            goto end;
        }
        
        //$recurlyResponse = $billing_info->getValues();
        // <editor-fold defaultstate="collapsed" desc="XML response is converted to object and values are protect so can not do json_encode directly. So preparing Array">
        $prepareBillingInfoArray = array();
        $prepareBillingInfoArray['first_name'] = !empty($billing_info->first_name) ? $billing_info->first_name : NULL;
        $prepareBillingInfoArray['last_name'] = !empty($billing_info->last_name) ? $billing_info->last_name : NULL;
        $prepareBillingInfoArray['company'] = !empty($billing_info->company) ? $billing_info->company : NULL;
        $prepareBillingInfoArray['email'] = !empty($billing_info->email) ? $billing_info->email : NULL;
        $prepareBillingInfoArray['address1'] = !empty($billing_info->address1) ? $billing_info->address1 : NULL;
        $prepareBillingInfoArray['address2'] = !empty($billing_info->address2) ? $billing_info->address2 : NULL;
        $prepareBillingInfoArray['city'] = !empty($billing_info->city) ? $billing_info->city : NULL;
        $prepareBillingInfoArray['state'] = !empty($billing_info->state) ? $billing_info->state : NULL;
        $prepareBillingInfoArray['zip'] = !empty($billing_info->zip) ? $billing_info->zip : NULL;
        $prepareBillingInfoArray['country'] = !empty($billing_info->country) ? $billing_info->country : NULL;
        $prepareBillingInfoArray['phone'] = !empty($billing_info->phone) ? $billing_info->phone : NULL;
        $prepareBillingInfoArray['vat_number'] = !empty($billing_info->vat_number) ? $billing_info->vat_number : NULL;
        $prepareBillingInfoArray['ip_address'] = !empty($billing_info->ip_address) ? $billing_info->ip_address : NULL;
        $prepareBillingInfoArray['ip_address_country'] = !empty($billing_info->ip_address_country) ? $billing_info->ip_address_country : NULL;
        $prepareBillingInfoArray['card_type'] = !empty($billing_info->card_type) ? $billing_info->card_type : NULL;
        $prepareBillingInfoArray['year'] = !empty($billing_info->year) ? $billing_info->year : NULL;
        $prepareBillingInfoArray['month'] = !empty($billing_info->month) ? $billing_info->month : NULL;
        $prepareBillingInfoArray['first_six'] = !empty($billing_info->first_six) ? $billing_info->first_six : NULL;
        $prepareBillingInfoArray['last_four'] = !empty($billing_info->last_four) ? $billing_info->last_four : NULL;
        // </editor-fold>
        
        $recurlyBillingInfo['recurly_billing_data'] = json_encode($prepareBillingInfoArray);
        
        end:
            return $recurlyBillingInfo;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Get Subscription from UUID. Using in Recurly Shell also.">
    public function getSubscriptionFromUUID($subscriptonUUID='') {
        if(empty($subscriptonUUID)){
            return array();
        }
        
        $recurlySubscriptionObj = ClassRegistry::init('RecurlySubscription');
        
        $recurlySubscriptionObj->recursive=2;
        $recurlySubscriptionObj->bindModel(
                array(
                    'belongsTo' => array(
                        'EntityPlanDetail'
                    )
        ));

        $planMasterFields = array('PlanMaster.id', 'PlanMaster.type', 'PlanMaster.plan_code', 'PlanMaster.plan_name', 'PlanMaster.plan_rate', 'PlanMaster.plan_type');
        $recurlySubscriptionObj->EntityPlanDetail->bindModel(
                array(
                    'belongsTo' => array(
                        'PlanMaster' => array(
                            'fields' => $planMasterFields,
                        )
                    )
        ));

        $options = array();
        $options['conditions'] = array('RecurlySubscription.uuid' => $subscriptonUUID, 'RecurlySubscription.iterate_status' => 1);
        $getSubscription = $recurlySubscriptionObj->find('first', $options);
        return $getSubscription;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Get entity plan details from account code">
    public function getEntityPlanDetailFromAccountCode($accountCode='') {
        if(empty($accountCode)){
            return array();
        }
        
        $entityPlanDetailObj = ClassRegistry::init('EntityPlanDetail');
        
        $planMasterFields = array('PlanMaster.id', 'PlanMaster.type', 'PlanMaster.plan_code', 'PlanMaster.plan_name', 'PlanMaster.plan_rate','PlanMaster.plan_type');
        $entityPlanDetailObj->bindModel(
                array(
                    'belongsTo' => array(
                        'PlanMaster' => array(
                            'type' => 'INNER',
                            'fields' => $planMasterFields,
                        )
                    ),
                    'hasOne' => array(
                        'RecurlySubscription'
                    )
        ));
        $options = array();
        $options['conditions'] = array('EntityPlanDetail.recurly_account_id' => $accountCode, 'RecurlySubscription.iterate_status' => 1);
        $getSubscription = $entityPlanDetailObj->find('first', $options);
        return $getSubscription;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Setting fields to update for subscription. Using in Recurly cron Also so keep it public">
    public function getFreePlanIDofSameCategory($planType='') {
        $getPlan = array();
        
        if(empty($planType)){
            goto end;
        }
        
        $planMasterObj = ClassRegistry::init('PlanMaster');
        
        $options = array();
        $options['conditions'] = array('PlanMaster.status'=>1,'PlanMaster.type'=>$planType,'PlanMaster.plan_type'=>'free');
        $getPlan = $planMasterObj->find('first',$options);
        
        end:
            return $getPlan;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="get colleagues with free plans">
    public function getColleagues() {
        
        $planMasterObj = ClassRegistry::init('PlanMaster');
        
        $options = array();
        $options['fields'] = array('PlanMaster.id','PlanMaster.id');
        $options['conditions'] = array('PlanMaster.plan_type'=>'free','PlanMaster.status'=>1);
        $getFreePlans = $planMasterObj->find('list',$options);
        
        $userMasterObj = ClassRegistry::init('UserMaster');
        
        $options = array();
        $userMasterObj->bindModel(
                array(
                    'belongsTo' => array(
                        'PersonMaster'
                    ), 
                    'hasOne' => array(
                        'EntityPlanDetail' => array(
                            'foreignKey' => 'entity_user_id',
                            //'conditions' => array('EntityPlanDetail.plan_master_id'=>$getFreePlans)
                        )
                    )
        ));

        $options['fields'] = array('UserMaster.id','UserMaster.email_address','PersonMaster.first_name','PersonMaster.last_name');
        $options['conditions'] = array('UserMaster.entity_type_master_id' => $GLOBALS['user_info']['UserMaster']['entity_type_master_id'],
            'UserMaster.id !=' => $GLOBALS['user_info']['UserMaster']['id'], 
            'UserMaster.status' => 1,
            'UserMaster.registration_step'=>3,
            'EntityPlanDetail.plan_master_id'=>$getFreePlans
            );
        $getColleagues = $userMasterObj->find('all', $options);
        return $getColleagues;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="get selected colleagues">
    public function getSelectedColleagues($findType='list') {
        
        $entityPlanDetailObj = ClassRegistry::init('EntityPlanDetail');
        
        $options = array();
        $options['fields'] = array('EntityPlanDetail.id','EntityPlanDetail.entity_user_id');
        $options['conditions'] = array('EntityPlanDetail.parent_id'=>$GLOBALS['user_info']['EntityPlanDetail']['id'],'EntityPlanDetail.status'=>1);
        $selectedUsers = $entityPlanDetailObj->find($findType,$options);
        return $selectedUsers;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="get current Subscription">
    public function getCurrentSubscription() {
        $entityPlanDetailObj = ClassRegistry::init('EntityPlanDetail');
        
        $planMasterFields = array('PlanMaster.id', 'PlanMaster.type', 'PlanMaster.plan_code', 'PlanMaster.plan_name', 'PlanMaster.plan_rate','PlanMaster.plan_type');
        $entityPlanDetailObj->bindModel(
                array(
                    'belongsTo' => array(
                        'PlanMaster' => array(
                            'type' => 'INNER',
                            'fields' => $planMasterFields,
                        //'conditions' => 'EntityTypeMaster.status = 1'
                        )
                    ),
                    'hasOne' => array(
                        'RecurlySubscription'
                    )
        ));
        $options = array();
        $options['conditions'] = array('EntityPlanDetail.entity_user_id' => $GLOBALS['user_info']['UserMaster']['id'], 'RecurlySubscription.iterate_status' => 1);
        $getSubscription = $entityPlanDetailObj->find('first', $options);
        return $getSubscription;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Get Plan details">
    public function getPlanDetail($planID='') {
        $plan = array();
        if(empty($planID)){
            goto end;
        }
        
        $planMasterObj = ClassRegistry::init('PlanMaster');
        
        $options = array();
        $options['conditions'] = array('PlanMaster.status'=>1,'PlanMaster.type'=>$GLOBALS['user_info']['EntityTypeMaster']['type'],'PlanMaster.id'=>$planID);
        $plan = $planMasterObj->find('first',$options);
        
        end:
            return $plan;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Set proper billing informations">
    public function getFormattedBillingInfo($billingInfoData='') {
        $billingInfo = $GLOBALS['user_info']['EntityPlanDetail']['recurly_billing_data'];
        
        if(!empty($billingInfoData)){
            $billingInfo = $billingInfoData;
        }
        
        if(!is_array($billingInfo)){
            $billingInfo = json_decode($billingInfo, true);
        }
        
        $formattedBillingInfo = $billingInfo;
        $formattedBillingInfo['zipcode'] = $formattedBillingInfo['zip'];
        //we are not unsetting keys currently
        
        echo "make proper table of city and state as per recurly as recurly give us state and country code. This message is in Recurly.php Model";exit;
        $commonObj = ClassRegistry::init('Common');
        $getCityID = !empty($formattedBillingInfo['city']) ? $commonObj->getCityByName($formattedBillingInfo['city']) : '';
        $formattedBillingInfo['city_id'] = !empty($getCityID) ? $getCityID : '';
        
        $getStateID = !empty($formattedBillingInfo['state']) ? $commonObj->getStateByCode($formattedBillingInfo['state']) : '';
        $formattedBillingInfo['state_id'] = !empty($getStateID) ? $getStateID : '';
        
        $getCountryID = !empty($formattedBillingInfo['country']) ? $commonObj->getCountryByCode($formattedBillingInfo['country']) : '';
        $formattedBillingInfo['country_id'] = !empty($getCountryID) ? $getCountryID : '';
        echo "<pre>";print_r($formattedBillingInfo);echo "</pre>";exit;
        
        return $formattedBillingInfo;
    }
    // </editor-fold>

}