<?php
/**
 * PayWhirl API PHP Library
 *
 * Use this library to interface with PayWhirl's API
 * https://api.paywhirl.com
 *
 *  Example Usage:
 *  =========================
 *  $payWhirl = new \PayWhirl\PayWhirl($api_key,$api_secret);
 *  $customer = $payWhirl->getCustomer($customer_id);
 *
 */

namespace PayWhirl;

class PayWhirl{


    // @var string The PayWhirl API key and secret to be used for requests.
    protected $_api_key;
    protected $_api_secret;

    // @var string The base URL for the PayWhirl API.
    protected $_api_base = 'https://api.paywhirl.com';

    /**
     * Prepare to make request
     * @param string $api_key    Your Paywhirl API Key
     * @param string $api_secret Your PayWhirl API Secret
     */
    function __construct($api_key,$api_secret,$api_base=false){
        //set API key and secret
        $this->_api_key = $api_key;
        $this->_api_secret = $api_secret;

        if($api_base){
            $this->_api_base = $api_base;
        }

    }

    /**
     * Get a list of customers
     * @return Customer Array of Objects
     */
    public function getCustomers($data){
        return $this->get('/customers',$data);
    }

    /**
     * Get a customer
     * @param  int $id Customer ID
     * @return Customer Object
     */
    public function getCustomer($id){
        return $this->get('/customer/'.$id);
    }

    /**
     * Get all addresses associated with a customer
     * @param  int $id Customer ID
     * @return Customer Object
     */
    public function getAddresses($id){
        return $this->get('/customer/addresses'.$id);
    }

    /**
     * Get a single address associated with a customer
     * @param  int $id Address ID
     * @return Customer Object
     */
    public function getAddress($id){
        return $this->get('/customer/address'.$id);
    }

    /**
     * Get a full customer profile by customer ID or email (customer, addresses, and profile questions)
     * @param  int $id Address ID
     * @return Customer Object
     */
    public function getProfile($id){
        return $this->get('/customer/profile'.$id);
    }

    /**
     * Create a customer
     * @param  array $data Customer data
     * @return Customer Object
     */
    public function createCustomer($data){
        return  $this->post('/create/customer',$data);

    }

    /**
     * Authenticate a customer
     * @param string $email email address
     * @param string $password password bcrypt hash or plain text
     * @return array ['status' => 'success' or 'failure']
     */
    public function authCustomer($email, $password){
        return $this->post('/auth/customer', compact('email', 'password'));
    }

    /**
     * Create a customer
     * @param  array $data Customer data
     * @return Customer Object
     */
    public function updateCustomer($data){
        return $this->post('/update/customer',$data);
    }

    /**
     * Delete a customer
     * @param  int $id Customer ID
     * @param  boolean $forget delete and obfuscate customer data
     * @return boolean
     */
    public function deleteCustomer($id, $forget = null){
        $data['id'] = $id;
        if (!is_null($forget)) {
            $data['forget'] = $forget;
        }
        return $this->post('/delete/customer', $data);
    }

    /**
     * Update a customer's answer to a profile questions
     * @param  array $data Answer data
     * @return Answer Object
     */
    public function updateAnswer($data){
        return $this->post('/update/answer',$data);
    }


    /**
     * Get a list of profile questions
     * @return Questions Array of Objects
     */
    public function getQuestions($data = 100){
        if (is_int($data) == true) {
            $tempData = array("limit" => $data);
            return $this->get('/questions', $tempData);
        }

        return $this->get('/questions',$data);
    }

    /**
     * Get a answers to a customer's questions
     * @return Answer Array of Objects
     */
    public function getAnswers($data){
        return $this->get('/answers',$data);
    }

    /**
     * Get a list of plans
     * @return Plan Array of Objects
     */
    public function getPlans($data){
        return $this->get('/plans',$data);
    }

    /**
     * Get a plan
     * @param  int $id Plan ID
     * @return Plan Object
     */
    public function getPlan($id){
        return $this->get('/plan/'.$id);
    }

    /**
     * Create a plan
     * @param  array $data Plan data
     * @return Plan Object
     */
    public function createPlan($data){
        return $this->post('/create/plan',$data);
    }

    /**
     * Update a plan
     * @param  array $data Plan data
     * @return Plan Object
     */
    public function updatePlan($data){
        return $this->post('/update/plan',$data);
    }


    /**
     * Get a list of subscriptions for a customer
     * @param  int $id Customer ID
     * @return Subscription List Object
     */
    public function getSubscriptions($id){
        return $this->get('/subscriptions/'.$id);
    }

    /**
     * Get a subscription
     * @param  int $id Subscription ID
     * @return Subscription Object
     */
    public function getSubscription($id){
        return $this->get('/subscription/'.$id);
    }

     /**
     * Get a list of active subscribers
     * @param  array $data Array of options
     * @return Subscribers List
     */
    public function getSubscribers($data){
        return $this->get('/subscribers',$data);
    }


    /**
     * Subscribe a customer to a plan
     * @param  int $customerID
     * @param  int $planID
     * @param  int $trialEnd
     * @param  int $promoID
     * @param  int $quantity
     * @return Subscription Object
     */
    public function subscribeCustomer($customerID, $planID, $trialEnd = false,
                                      $promoID = false, $quantity = 1 ) {
        $data = array(
            'customer_id' => $customerID,
            'plan_id' => $planID,
            'quantity' => $quantity
        );
        if ($trialEnd) {
            $data['trial_end'] = $trialEnd;
        }
        if ($promoID) {
            $data['promo_id'] = $promoID;
        }
        return $this->post('/subscribe/customer', $data);
    }

     /**
     * Subscribe a customer to a plan
     * @param  int $id Subscription ID
     * @return Subscription Object
     */
    public function updateSubscription($subscription_id, $plan_id, $quantity = NULL){
        $data = array(
            'subscription_id' => $subscription_id,
            'plan_id' => $plan_id
        );
        if ($quantity != NULL) {
            $data['quantity'] = $quantity;
        }
        return $this->post('/update/subscription',$data);
    }

     /**
     * Unsubscribe a Customer
     * @param  int $id Subscription ID
     * @return Subscription Object
     */
    public function unsubscribeCustomer($subscription_id){
        $data = array(
            'subscription_id' => $subscription_id,
        );
        return $this->post('/unsubscribe/customer',$data);
    }


    /**
     * Get a invoice
     * @param  int $id Invoice ID
     * @return Invoice Object
     */
    public function getInvoice($id){
        return $this->get('/invoice/'.$id);
    }

    /**
     * Get a list of upcoming invoices for a customer
     * @param  int $id Customer ID
     * @return Invoices Object
     */
    public function getInvoices($id, $all = 0){
        if ($all != 0) {
            $data = array(
                'all' => $all,
            );
        }
        return $this->get('/invoices/'.$id, $data);
    }

    /**
     * Process an upcoming invoice immediately
     * @param  int $invoice_id
     * @param  array $data
     * @return Invoice Object
     */
    public function processInvoice($invoice_id, $data = []){
        return $this->post("/invoices/{$invoice_id}/process", $data);
    }

    /**
     * Mark an upcoming invoice as paid
     * @param  int $invoice_id
     * @return Invoice Object
     */
    public function markInvoiceAsPaid($invoice_id){
        return $this->post("/invoices/{$invoice_id}/mark-as-paid");
    }

     /**
     * Update a card for an invoice
     * @param  int $invoice_id
     * @param int $card_id
     * @return Invoice Object
     */
    public function updateInvoiceCard($invoice_id, $card_id){
        return $this->post("/invoices/{$invoice_id}/card", $card_id);
    }

     /**
     * Update a card for an invoice
     * @param  int $invoice_id
     * @param  array $line_items
     * @return Invoice Object
     */
    public function updateInvoiceItems($invoice_id, $line_items){
        return $this->post("/invoices/{$invoice_id}/items", $line_items);
    }

     /**
     * Create a new invoice
     * @param  array $data
     * @return Invoice Object
     */
    public function createInvoice($data){
        return $this->post("/invoices/", $data);
    }

    /**
     * Get a list of payment gateways
     * @return Gateways Collection
     */
    public function getGateways(){
        return $this->get('/gateways');
    }


    /**
     * Get a payment gateway
     * @param  int $id Gateway ID
     * @return Gateway Object
     */
    public function getGateway($id){
        return $this->get('/gateway/'.$id);
    }

     /**
     * Create an invoice with a single charge
     * @param  array $data  data
     * @return Plan Object
     */
    public function createCharge($data){
        return $this->post('/create/charge',$data);
    }

    /**
     * Get a charge
     * @param  int $id Charge ID
     * @return Charge Object
     */
    public function getCharge($id){
        return $this->get('/charge/'.$id);
    }

    /**
     * Refund a charge
     * @param  int $id Charge ID
     * @param  array $data  data
     * @return Plan Object
     */
    public function refundCharge($id, $data){
        return $this->post('/refund/charge/'.$id, $data);
    }

    /**
     * Get a card
     * @param  int $id Card ID
     * @return Gateway Object
     */
    public function getCard($id){
        return $this->get('/card/'.$id);
    }

    /**
     * Get a customer's cards
     * @param  int $id Customer ID
     * @return Card List Object
     */
    public function getCards($id){
      return $this->get('/cards/'.$id);
    }

    /**
     * Create a card
     * @param  array $data Card Data
     * @return Card Object
     */
    public function createCard($data){
        return $this->post('/create/card',$data);
    }

    /**
     * Delete a card
     * @param  int $id Card ID
     * @return boolean
     */
    public function deleteCard($id){
        $data['id'] = $id;
        return $this->post('/delete/card',$data);
    }


    /**
     * Get all promo codes
     * @return Promo Code Object
     */
    public function getPromos(){
        return $this->get('/promo');
    }

    /**
     * Get a promo code
     * @param  int $id Promo Code ID
     * @return Promo Code Object
     */
    public function getPromo($id){
        return $this->get('/promo/'.$id);
    }

     /**
     * Create a promo code
     * @param  array $data  data
     * @return Plan Object
     */
    public function createPromo($data){
        return $this->post('/create/promo',$data);
    }

     /**
     * Delete a promo code
     * @param  array $id  id
     * @return Plan Object
     */
    public function deletePromo($id){
        $data = array( 'id' => $id );
        return $this->post('/delete/promo',$data);
    }

    /**
     * Get an email template
     * @param  int $id Email Template ID
     * @return Email Template Object
     */
    public function getEmailTemplate($id){
        return $this->get('/email/'.$id);
    }

    /**
     * Send a system generated email to a customer
     * @param  array $data options
     * @return success or error
     */
    public function sendEmail($data){
        return $this->post('/send-email', $data);
    }

    /**
     * Get authenticated account's information
     * @return PayWhirl account object
     */
    public function getAccount(){
        return $this->get('/account');
    }

     /**
     * Get authenticated account's stats
     * @return PayWhirl account object
     */
    public function getStats(){
        return $this->get('/stats');
    }

    /**
     * Get all shipping rules
     * @return Shipping Rule Object
     */
    public function getShippingRules(){
        return $this->get('/shipping');
    }

    /**
     * Get a shipping rule
     * @param  int $id Shipping Rule ID
     * @return Shipping Rule Object
     */
    public function getShippingRule($id){
        return $this->get('/shipping/'.$id);
    }

    /**
     * Get a tax rule
     * @return Tax Rule Object
     */
    public function getTaxRules(){
        return $this->get('/tax');
    }

    /**
     * Get a tax rule
     * @param  int $id Tax Rule ID
     * @return Tax Rule Object
     */
    public function getTaxRule($id){
        return $this->get('/tax/'.$id);
    }

     /**
     * Get MultiAuth token
     * @param  array $data Options
     * @return boolean
     */
    public function getMultiAuthToken($data){
        return $this->post('/multiauth',$data);
    }

    /**
    * Send POST request
    */
    public function post($endpoint,$params=array()){

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('api_key: '.$this->_api_key ,'api_secret: '.$this->_api_secret));
        curl_setopt($ch, CURLOPT_URL,$this->_api_base.$endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

         curl_setopt($ch, CURLOPT_POSTFIELDS,
         http_build_query($params));
        // receive server response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        curl_close ($ch);
        return json_decode($server_output);
    }
     /**
    * Send GET request
    */
    public function get($endpoint,$params=array()){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_HTTPHEADER,array('api_key: '.$this->_api_key ,'api_secret: '.$this->_api_secret));
        $query = http_build_query($params);
        curl_setopt($ch,CURLOPT_URL,$this->_api_base.$endpoint.'?'.$query);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $output=curl_exec($ch);

        curl_close($ch);
        return json_decode($output);
    }
}