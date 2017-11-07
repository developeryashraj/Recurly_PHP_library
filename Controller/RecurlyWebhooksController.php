<?php

App::uses('AppController', 'Controller');
App::import('Vendor', 'recurly/lib/recurly');
/**
 * This is recurly webhook controller for notifying us when some event occures at recurly.
 * 
 * ACH :- Payment From Bank Accounts. - https://docs.recurly.com/docs/check-gateway-ach
 *
 * @package       app.Controller
 * @link https://github.com/mbeale/recurly-push-notification-example/blob/master/application/routes.php check route recurly-notification for detail
 * @link https://dev.recurly.com/page/webhooks List of available APIs
 * @link https://docs.recurly.com/docs/webhooks how webhook work and things to keep in mind.
 * 
 */
define('RECURLY_API_KEY', 'RECURLY_PRIVATE_KEY');
class RecurlyWebhooksController extends AppController {

    /**
     * Loaded models
     *
     * @var array
     */
    public $uses = array('RecurlyWebhookPayment','RecurlyWebhookSubscription','RecurlyWebhookInvoice','RecurlyWebhookAccount','RecurlyResponse','Common');
    private $webhookNotification;
    private $paymentData;
    private $subscriptionData;
    private $invoiceData;
    private $accountData;
    private $log;
    private $httpRespondCode;
    private $recurlyIPAddresses;
    
    /**
     * Construct function
     * 
     * @param type $request
     * @param type $response
     */
    public function __construct($request = null, $response = null) {
        parent::__construct($request, $response);
        Recurly_Client::$apiKey = RECURLY_API_KEY;
        $this->layout = '';
        $this->webhookNotification = '';
        $this->paymentData = array();
        $this->subscriptionData = array();
        $this->invoiceData = array();
        $this->accountData = array();
        $this->log = '';
        $this->httpRespondCode = 200; //respond with status code to recurly. Read more about it @link https://docs.recurly.com/docs/webhooks
        $this->recurlyIPAddresses = array('50.18.192.88','52.8.32.100','52.9.209.233','50.0.172.150','52.203.102.94','52.203.192.184');
    }
    
    // <editor-fold defaultstate="collapsed" desc="Function to make function name based on webhook type">
    protected function underscoreToCamelCase($string, $capitalizeFirstCharacter = false) {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Main webhook function which is submitted as endpoint">
    /**
     * Recurly will request here with details. This will be the Endpoint in recurly.
     * 
     */
    public function mainWebhook() {
        
        $clientIP = $this->Common->getClientIP();
        if(!in_array($clientIP, $this->recurlyIPAddresses)){
            //skip execution and notify yourself about this
            $email = $GLOBALS['developerEmails'];
            $message_subject = 'Recurly Webhook Alert';
            $message_body = 'Looks like webhook notification are coming from other IP which is not allowed. IP :- '.$clientIP.' Verify this here :- https://docs.recurly.com/docs/webhooks in allowed IP list';
            $this->send_email_static($email, '', $message_subject, $message_body);
            goto end;
        }
        
        $post_xml = file_get_contents ("php://input");
	$notification = new Recurly_PushNotification($post_xml);
        
        if(empty($notification)|| empty($notification->type)){
            return true;
        }
        
        $getFunctionName = $this->underscoreToCamelCase($notification->type);
        
        /*$this->log = "Start Function :- ".$getFunctionName;
        $this->log .= "\n Notification type :- ".$notification->type;
        $start_log_id = $this->logToFile($this->log, DIR_LOG . 'recurly.log');*/
        
        $this->log = '';
        if(method_exists($this,$getFunctionName)){
            $this->webhookNotification = $notification;
            $this->{$getFunctionName}();
        }
        
        /*$this->log .= "Function :- ".$getFunctionName;
        $this->log .= "\n Notification type :- ".$notification->type;
        $start_log_id = $this->logToFile($this->log, DIR_LOG . 'recurly.log');*/
        
        if($this->httpRespondCode!=''){
            http_response_code($this->httpRespondCode);
        }
        
        end:
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Managing boolean values to store in MYSQL format">
    private function handleBooleanValues($booleanKey) {
        $boolean = 0;
        if(isset($booleanKey) && $booleanKey===true){
            $boolean = 1;
        }
        return $boolean;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Format Data type values. Default conversion to 'Y-m-d H:i:s'">
    /**
     * Format date/datetime to DD/MM/YYYY or any given format
     * 
     * @param string $date
     * @param string $response_format [optional] default = d/m/Y
     * @return string return actual date string if any exception occured
     */
    private function formatDate($date, $response_format = 'Y-m-d H:i:s')
    {
        try {
            $formated_date = $date;
            $dateTime = DateTime::createFromFormat(DateTime::ISO8601, $date);
            if(!empty($dateTime)) {
                $formated_date = $dateTime->format($response_format);
            }
        } catch(Exception $e) {
            return $formated_date;
        }
        
        return $formated_date;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Set common account fields in array">
    /**
     * Beware while making changes as this will also affect Account notification section. 
     * We are not having separate comman field set function as this function is common and used by all other functions
     * @param type $dataArray
     */
    private function setCommonAccountFields(&$dataArray){
        $accountObj = $this->webhookNotification->account;
        
        $dataArray['type'] = $this->webhookNotification->type;
        $dataArray['account_code'] = !empty($accountObj->account_code)?$accountObj->account_code:NULL;
        $dataArray['username'] = !empty($accountObj->username)?$accountObj->username:NULL;
        $dataArray['email'] = !empty($accountObj->email)?$accountObj->email:NULL;
        $dataArray['first_name'] = !empty($accountObj->first_name)?$accountObj->first_name:NULL;
        $dataArray['last_name'] = !empty($accountObj->last_name)?$accountObj->last_name:NULL;
        $dataArray['company_name'] = !empty($accountObj->company_name)?$accountObj->company_name:NULL;
        $dataArray['whole_request'] = json_encode($this->webhookNotification);
    }
    // </editor-fold>
        
    // <editor-fold defaultstate="collapsed" desc="Payment Notifications :- https://dev.recurly.com/page/webhooks#payment-notifications">
        
    // <editor-fold defaultstate="collapsed" desc="Set common payment fields and its validations">
    /**
     * This function will manage common fields and its validation or other related task.
     */
    private function setCommonPaymentFields() {
        $this->setCommonAccountFields($this->paymentData);
        $transactionObj = $this->webhookNotification->transaction;
        
        $this->paymentData['transaction_id'] = !empty($transactionObj->id) ? $transactionObj->id : NULL;
        $this->paymentData['invoice_id'] = !empty($transactionObj->invoice_id) ? $transactionObj->invoice_id : NULL;
        $this->paymentData['invoice_number'] = !empty($transactionObj->invoice_number) ? $transactionObj->invoice_number : NULL;
        $this->paymentData['subscription_id'] = !empty($transactionObj->subscription_id) ? $transactionObj->subscription_id : NULL;
        $this->paymentData['action'] = !empty($transactionObj->action) ? $transactionObj->action : NULL;
        $this->paymentData['date'] = !empty($transactionObj->date) ? $this->formatDate($transactionObj->date) : NULL;
        $this->paymentData['amount_in_cents'] = !empty($transactionObj->amount_in_cents) ? $transactionObj->amount_in_cents : NULL;
        $this->paymentData['status'] = !empty($transactionObj->status) ? $transactionObj->status : NULL;
        $this->paymentData['message'] = !empty($transactionObj->message) ? $transactionObj->message : NULL;
        $this->paymentData['reference'] = !empty($transactionObj->reference) ? $transactionObj->reference : NULL;
        $this->paymentData['source'] = !empty($transactionObj->source) ? $transactionObj->source : NULL;
        $this->paymentData['cvv_result'] = !empty($transactionObj->cvv_result) ? $transactionObj->cvv_result : NULL;
        $this->paymentData['avs_result'] = !empty($transactionObj->avs_result) ? $transactionObj->avs_result : NULL;
        $this->paymentData['avs_result_street'] = !empty($transactionObj->avs_result_street) ? $transactionObj->avs_result_street : NULL;
        $this->paymentData['avs_result_postal'] = !empty($transactionObj->avs_result_postal) ? $transactionObj->avs_result_postal : NULL;
        $this->paymentData['test'] = $this->handleBooleanValues($transactionObj->test);
        $this->paymentData['voidable'] = $this->handleBooleanValues($transactionObj->voidable);
        $this->paymentData['refundable'] = $this->handleBooleanValues($transactionObj->refundable);
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Save Payment / Transaction data">\
    private function savePaymentData() {
        $this->sanitizeAll($this->paymentData);
        if(!empty($this->paymentData)){
            if(!$this->RecurlyWebhookPayment->save($this->paymentData)){
                $this->httpRespondCode = ''; //Leave response code blank or set other than 2XX so that recurly can again make request.
            }
        }
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Scheduled Payment (Only for ACH payments) :- scheduled_payment_notification">
    /**
     * Function to do extra task and operation for type scheduled_payment_notification. Get more details about ACH in class description.
     * @description A scheduled_payment_notification is sent when Recurly initiates an ACH payment from a customer entering payment or the renewal procces.
     */
    private function scheduledPaymentNotification() {
        return true;// not needed currently as payment using bank account is not integrated. Only card payments are allowed currently.
        $this->setCommonPaymentFields();
        $this->paymentData['invoice_number_prefix'] = !empty($this->webhookNotification->invoice_number_prefix)?$this->webhookNotification->invoice_number_prefix:NULL;
        $this->savePaymentData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Processing Payment (Only for ACH payments) :- processing_payment_notification">
    /**
     * Function to do extra task and operation for type processing_payment_notification. Get more details about ACH in class description.
     * @description A processing_payment_notification is sent when an ACH payment moves from the scheduled state to the processing state. An ACH payment enters a processing state when it has been submitted to the ACH bank network by Check Gateway.
     */
    private function processingPaymentNotification() {
        return true;// not needed currently as payment using bank account is not integrated. Only card payments are allowed currently.
        $this->setCommonPaymentFields();
        $this->paymentData['invoice_number_prefix'] = !empty($this->webhookNotification->invoice_number_prefix)?$this->webhookNotification->invoice_number_prefix:NULL;
        $this->savePaymentData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Successful Payment & Manual Payment :- successful_payment_notification">
    /**
     * Function to do extra task and operation for type successful_payment_notification.
     * @description 1]. A successful_payment_notification is sent when a payment is successfully captured. 2]. And is also sent when a manual offline payment is recorded.
     */
    private function successfulPaymentNotification() {
        $this->setCommonPaymentFields();
        
        // <editor-fold defaultstate="collapsed" desc="For manual offline payment">
        $this->paymentData['manually_entered'] = $this->handleBooleanValues($this->webhookNotification->manually_entered);
        $this->paymentData['payment_method'] = !empty($this->webhookNotification->payment_method)?$this->webhookNotification->payment_method:NULL;
        // </editor-fold>

        $this->savePaymentData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Failed Payment :- failed_payment_notification">
    /**
     * Function to do extra task and operation for type failed_payment_notification.
     * @description A failed_payment_notification is sent when a payment attempt is declined by the payment gateway.
     */
    private function failedPaymentNotification() {
        $this->setCommonPaymentFields();
        
        $this->paymentData['gateway'] = !empty($this->webhookNotification->gateway)?$this->webhookNotification->gateway:NULL;
        $this->paymentData['gateway_error_codes'] = !empty($this->webhookNotification->gateway_error_codes)?$this->webhookNotification->gateway_error_codes:NULL;
        $this->paymentData['failure_type'] = !empty($this->webhookNotification->failure_type)?$this->webhookNotification->failure_type:NULL;
        
        $this->savePaymentData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Successful Refund :- successful_refund_notification">
    /**
     * Function to do extra task and operation for type successful_refund_notification. transaction action will be "credit".
     * @description If you refund an amount through the API or admin interface, a successful_refund_notification is sent. Failed refund attempts do not generate a notification.
     */
    private function successfulRefundNotification() {
        $this->setCommonPaymentFields();
        $this->savePaymentData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Void Payment :- void_payment_notification">
    /**
     * Function to do extra task and operation for type void_payment_notification. voidable will be "false" and will get data for cvv_result and avs_result.
     * @description If you void a successfully captured payment before it settles, a void_payment_notification is sent. Payments can only be voided before the funds settle into your merchant account.
     */
    private function voidPaymentNotification() {
        $this->setCommonPaymentFields();
        $this->savePaymentData();
    }
    // </editor-fold>

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Subscription Notifications :- https://dev.recurly.com/page/webhooks#subscription-notifications">

    // <editor-fold defaultstate="collapsed" desc="Set common subscription fields and its validations">
    /**
     * This function will manage common fields and its validation or other related task.
     */
    private function setCommonSubscriptionFields() {
        $this->setCommonAccountFields($this->subscriptionData);
        $subscriptionObj = $this->webhookNotification->subscription;
        
        $this->subscriptionData['plan_code'] = !empty($subscriptionObj->plan->plan_code) ? $subscriptionObj->plan->plan_code : NULL;
        $this->subscriptionData['plan_name'] = !empty($subscriptionObj->plan->name) ? $subscriptionObj->plan->name : NULL;
        $this->subscriptionData['uuid'] = !empty($subscriptionObj->uuid) ? $subscriptionObj->uuid : NULL;
        $this->subscriptionData['state'] = !empty($subscriptionObj->state) ? $subscriptionObj->state : NULL;
        $this->subscriptionData['quantity'] = !empty($subscriptionObj->quantity) ? $subscriptionObj->quantity : NULL;
        $this->subscriptionData['total_amount_in_cents'] = !empty($subscriptionObj->total_amount_in_cents) ? $subscriptionObj->total_amount_in_cents : NULL;
        $this->subscriptionData['activated_at'] = !empty($subscriptionObj->activated_at) ? $this->formatDate($subscriptionObj->activated_at) : NULL;
        $this->subscriptionData['canceled_at'] = !empty($subscriptionObj->canceled_at) ? $this->formatDate($subscriptionObj->canceled_at) : NULL;
        $this->subscriptionData['expires_at'] = !empty($subscriptionObj->expires_at) ? $this->formatDate($subscriptionObj->expires_at) : NULL;
        $this->subscriptionData['current_period_started_at'] = !empty($subscriptionObj->current_period_started_at) ? $this->formatDate($subscriptionObj->current_period_started_at) : NULL;
        $this->subscriptionData['current_period_ends_at'] = !empty($subscriptionObj->current_period_ends_at) ? $this->formatDate($subscriptionObj->current_period_ends_at) : NULL;
        $this->subscriptionData['trial_started_at'] = !empty($subscriptionObj->trial_started_at) ? $this->formatDate($subscriptionObj->trial_started_at) : NULL;
        $this->subscriptionData['trial_ends_at'] = !empty($subscriptionObj->trial_ends_at) ? $this->formatDate($subscriptionObj->trial_ends_at) : NULL;
        $this->subscriptionData['collection_method'] = !empty($subscriptionObj->collection_method) ? $subscriptionObj->collection_method : NULL;
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Save Subscription Data">\
    private function saveSubscriptionData() {
        $this->sanitizeAll($this->subscriptionData);
        if(!empty($this->subscriptionData)){
            if(!$this->RecurlyWebhookSubscription->save($this->subscriptionData)){
                $this->httpRespondCode = ''; //Leave response code blank or set other than 2XX so that recurly can again make request.
            }
        }
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="New Subscription :- new_subscription_notification">
    /**
     * Function to do extra task and operation for type new_subscription_notification.
     * @description Sent when a new subscription is created.
     */
    private function newSubscriptionNotification() {
        $this->setCommonSubscriptionFields();
        $this->saveSubscriptionData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Updated Subscription :- updated_subscription_notification">
    /**
     * Function to do extra task and operation for type updated_subscription_notification.
     * @description When a subscription is upgraded or downgraded, Recurly will send an updated_subscription_notification. The notification is sent after the modification is performed. If you modify a subscription and it takes place immediately, the notification will also be sent immediately. If the subscription change takes effect at renewal, then the notification will be sent when the subscription renews. Therefore, if you receive an updated_subscription_notification, it contains the latest subscription information.
     */
    private function updatedSubscriptionNotification() {
        $this->setCommonSubscriptionFields();
        $this->saveSubscriptionData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Canceled Subscription :- canceled_subscription_notification">
    /**
     * Function to do extra task and operation for type canceled_subscription_notification. 'State' is canceled.
     * @description The canceled_subscription_notification is sent when a subscription is canceled. This means the subscription will not renew. The subscription state is set to canceled but the subscription is still valid until the expires_at date. The next notification is sent when the subscription is completely terminated.
     */
    private function canceledSubscriptionNotification() {
        $this->setCommonSubscriptionFields();
        $this->saveSubscriptionData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Expired Subscription :- expired_subscription_notification">
    /**
     * Function to do extra task and operation for type expired_subscription_notification. 'State' is expired.
     * @description The expired_subscription_notification is sent when a subscription is no longer valid. This can happen if a canceled subscription expires or if an active subscription is refunded (and terminated immediately). If you receive this message, the account no longer has a subscription.
     */
    private function expiredSubscriptionNotification() {
        $this->setCommonSubscriptionFields();
        $this->saveSubscriptionData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Renewed Subscription :- renewed_subscription_notification">
    /**
     * Function to do extra task and operation for type renewed_subscription_notification. 'State' is expired.
     * @description The renewed_subscription_notification is sent whenever a subscription renews. This notification is sent regardless of a successful payment being applied to the subscription---it indicates the previous term is over and the subscription is now in a new term. If you are performing metered or usage-based billing, use this notification to reset your usage stats for the current billing term.
     */
    private function renewedSubscriptionNotification() {
        $this->setCommonSubscriptionFields();
        $this->saveSubscriptionData();
    }
    // </editor-fold>

    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Invoice Notifications :- https://dev.recurly.com/page/webhooks#invoice-notifications">

    // <editor-fold defaultstate="collapsed" desc="Set common invoice fields and its validations">
    /**
     * This function will manage common fields and its validation or other related task.
     */
    private function setCommonInvoiceFields() {
        $this->setCommonAccountFields($this->invoiceData);
        $invoiceObj = $this->webhookNotification->invoice;
        
        $this->invoiceData['uuid'] = !empty($invoiceObj->uuid) ? $invoiceObj->uuid : NULL;
        $this->invoiceData['subscription_id'] = !empty($invoiceObj->subscription_id) ? $invoiceObj->subscription_id : NULL;
        $this->invoiceData['state'] = !empty($invoiceObj->state) ? $invoiceObj->state : NULL;
        $this->invoiceData['invoice_number_prefix'] = !empty($invoiceObj->invoice_number_prefix) ? $invoiceObj->invoice_number_prefix : NULL;
        $this->invoiceData['invoice_number'] = !empty($invoiceObj->invoice_number) ? $invoiceObj->invoice_number : NULL;
        $this->invoiceData['po_number'] = !empty($invoiceObj->po_number) ? $invoiceObj->po_number : NULL;
        $this->invoiceData['vat_number'] = !empty($invoiceObj->vat_number) ? $invoiceObj->vat_number : NULL;
        $this->invoiceData['total_in_cents'] = !empty($invoiceObj->total_in_cents) ? $invoiceObj->total_in_cents : NULL;
        $this->invoiceData['currency'] = !empty($invoiceObj->currency) ? $invoiceObj->currency : NULL;
        $this->invoiceData['date'] = !empty($invoiceObj->date) ? $this->formatDate($invoiceObj->date) : NULL;
        $this->invoiceData['closed_at'] = !empty($invoiceObj->closed_at) ? $this->formatDate($invoiceObj->closed_at) : NULL;
        // <editor-fold defaultstate="collapsed" desc="for manual invoice notifications off all type">
        $this->invoiceData['net_terms'] = !empty($invoiceObj->net_terms) ? $invoiceObj->net_terms : NULL;
        $this->invoiceData['collection_method'] = !empty($invoiceObj->collection_method) ? $invoiceObj->collection_method : NULL;
        // </editor-fold>
        }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Save Invoice Data">\
    private function saveInvoiceData() {
        $this->sanitizeAll($this->invoiceData);
        if(!empty($this->invoiceData)){
            if(!$this->RecurlyWebhookInvoice->save($this->invoiceData)){
                $this->httpRespondCode = ''; //Leave response code blank or set other than 2XX so that recurly can again make request.
            }
        }
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="New Invoice & New Invoice (Manual) :- new_invoice_notification">
    /**
     * Function to do extra task and operation for type new_invoice_notification.
     * @description 1]. If a new invoice is generated, a new_invoice_notification is sent. 2]. If a new manual invoice is generated, a new_invoice_notification is sent. For partially paid manual invoices, a new_invoice_notification will not be sent after each partial payment.
     */
    private function newInvoiceNotification() {
        $this->setCommonInvoiceFields();
        // manual notification can be identified by collection_method key.
        $this->saveInvoiceData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Processing Invoice (Automatic - Only for ACH payments) :- processing_invoice_notification">
    /**
     * Function to do extra task and operation for type processing_invoice_notification.  Get more details about ACH in class description.
     * @description If an invoice is paid with ACH, the invoice will move into a processing state. When the invoice state changes to processing, a processing_invoice_notification is sent.
     */
    private function processingInvoiceNotification() {
        return true;// not needed currently as payment using bank account is not integrated. Only card payments are allowed currently.
        $this->setCommonInvoiceFields();
        // manual notification can be identified by collection_method key.
        $this->saveInvoiceData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Closed Invoice & Closed Invoice (Manual):- closed_invoice_notification">
    /**
     * Function to do extra task and operation for type closed_invoice_notification. 'State' is collected.
     * @description 1]. If an invoice is closed, a closed_invoice_notification is sent. A closed invoice can result from either a failed to collect invoice or fully paid invoice. 2]. If a manual invoice is closed, a closed_invoice_notification is sent. A closed invoice can result from either a failed to collect invoice or fully paid invoice. A manual
     */
    private function closedInvoiceNotification() {
        $this->setCommonInvoiceFields();
        // manual notification can be identified by collection_method key.
        $this->saveInvoiceData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Past Due Invoice & Past Due Invoice (Manual) :- past_due_invoice_notification">
    /**
     * Function to do extra task and operation for type past_due_invoice_notification. 'State' is past_due.
     * @description 1]. If an invoice is past due, a past_due_invoice_notification is sent. An invoice that is past due can result from a failure to collect by the due date. 2]. If a manual invoice is past due, a past_due_invoice_notification is sent. An invoice that is past due can result from a failure to collect by the due date.
     */
    private function pastDueInvoiceNotification() {
        $this->setCommonInvoiceFields();
        // manual notification can be identified by collection_method key.
        $this->saveInvoiceData();
    }
    // </editor-fold>
    
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Account Notifications :- https://dev.recurly.com/page/webhooks#account-notifications">

    // <editor-fold defaultstate="collapsed" desc="Save Account Data">\
    private function saveAccountData() {
        $this->sanitizeAll($this->accountData);
        if(!empty($this->accountData)){
            if(!$this->RecurlyWebhookAccount->save($this->accountData)){
                $this->httpRespondCode = ''; //Leave response code blank or set other than 2XX so that recurly can again make request.
            }
        }
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="New Account :- new_account_notification">
    /**
     * Function to do extra task and operation for type new_account_notification.
     */
    private function newAccountNotification() {
        $this->setCommonAccountFields($this->accountData);
        $this->saveAccountData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Closed Account :- canceled_account_notification">
    /**
     * Function to do extra task and operation for type canceled_account_notification.
     */
    private function canceledAccountNotification() {
        $this->setCommonAccountFields($this->accountData);
        $this->saveAccountData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Updated Billing Information :- billing_info_updated_notification">
    /**
     * Function to do extra task and operation for type billing_info_updated_notification.
     */
    private function billingInfoUpdatedNotification() {
        $this->setCommonAccountFields($this->accountData);
        $this->saveAccountData();
    }
    // </editor-fold>
    
    // <editor-fold defaultstate="collapsed" desc="Reactivated Account :- reactivated_account_notification">
    /**
     * Function to do extra task and operation for type reactivated_account_notification.
     * @description Sent when an account subscription is reactivated after having been canceled.
     */
    private function reactivatedAccountNotification() {
        $this->setCommonAccountFields($this->accountData);
        $this->saveAccountData();
        
        $this->renewedSubscriptionNotification();
    }
    // </editor-fold>
    
    // </editor-fold>
    
}