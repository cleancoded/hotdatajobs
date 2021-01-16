<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stripe
 *
 * @author Grzegorz
 */
class Wpjb_Payment_Stripe extends Wpjb_Payment_Abstract
{
    public function __construct(Wpjb_Model_Payment $data = null)
    {
        $this->_default = array(
            "disabled" => "0"
        );
        
        $this->_data = $data;
    }
    
    public function getEngine()
    {
        return "Stripe";
    }
    
    public function getForm()
    {
        return "Wpjb_Form_Admin_Config_Stripe";
    }
    
    public function getFormFrontend()
    {
        return "Wpjb_Form_Payment_Stripe";
    }
    
    public function getTitle()
    {
        return "Stripe (Credit Card)";
    }
    
    public function processTransaction()
    {
        $path = Wpjb_List_Path::getPath("vendor");
        
        if(!class_exists("\Stripe\Stripe")) {
            include_once $path."/Stripe/Stripe.php";
        }
        
        $cArr = Wpjb_List_Currency::getByCode($this->_data->payment_currency);
        $amount = ($this->_data->payment_sum-$this->_data->payment_paid)*pow( 10, $cArr["decimal"] );
        $currency = strtolower($this->_data->payment_currency);
        
        $token = $this->_post["token_id"];

        $description = trim($this->conf("stripe_description"));
        if(empty($description)) {
            $description = __("Payment ID: %s", "wpjobboard");
        }
        
        $pricing = new Wpjb_Model_Pricing($this->_data->pricing_id);
        $isRecurring = $pricing->meta->is_recurring->value();
        
        $customer = null;
        $card = null;
        
        if(get_current_user_id() > 0) {
            
            $customer = get_user_meta(get_current_user_id(), "_wpjb_stripe_customer_id", true);
            
            $saveCC = false;
            if(isset($this->_post['options']) && in_array("save-cc", $this->_post["options"])) {
                $saveCC = true;
            }
            
            $userHasCard = false;
            if($customer) {
                \Stripe\Stripe::setApiKey($this->conf("secret_key"));
                try {
                    $stripeCustomer = \Stripe\Customer::retrieve($customer);
                    $userHasCard = !empty($stripeCustomer->sources->data);
                } catch( Exception $e ) {
                    // Saved customer ID do not exist in Stripe
                    delete_user_meta(get_current_user_id(), "_wpjb_stripe_customer_id");
                }
            }
            
            //if(empty($customer) && ($saveCC || $isRecurring)) {
            if($saveCC || ($isRecurring && !$userHasCard)) {
                $form = array(
                    "stripe_token" => $this->_post["token_id"],
                    "email" => $this->_data->email,
                    "fullname" => $this->_data->fullname
                );
                $cus = $this->_createCustomer($form);
                $customer = $cus['customer_id'];
                $card = $cus["id"];
                $token = $card;
                
                update_user_meta(get_current_user_id(), "_wpjb_stripe_customer_id", $customer);
            }
        }
        
        \Stripe\Stripe::setApiKey($this->conf("secret_key")); 
        
        if($pricing->meta->is_recurring->value()) {
            // Recurring Payment
            $charge = \Stripe\Subscription::create(array(
                "customer" => $customer,
                "items" => array(
                  array(
                    "plan" => $pricing->meta->stripe_id->value(),
                  ),
                )
            ));
            $paid = $charge->plan->amount;
        } else {
            // Single Payment
            
            $params = array(
                "amount" => $amount, 
                "currency" => $currency,
                "description" => sprintf($description, $this->getObject()->id()),
                "receipt_email" => $this->_data->email,
                "statement_descriptor" => $this->conf("statement_descriptor")
            );
            
            
            if( $customer && $card ) {
                $params["customer"] = $customer;
                $params["source"] = $card;
            } elseif( $customer ) {
                $params["customer"] = $customer;
            } elseif( $token ) {
                $params["source"] = $token;
            }
            
            /*if($customer) {
                $params["customer"] = $customer;
            }
            if($token) {
                $params["source"] = $token;
            }*/

            
            $charge = \Stripe\Charge::create(apply_filters("wpjb_stripe_charge_params", $params, $this->getObject()->id()));
            $paid = $charge->amount;
        }

        return array(
            "external_id"   => $charge->id,
            'is_recurring'  => $pricing->meta->is_recurring->value(),
            "paid"          => $paid/pow( 10, $cArr["decimal"] ),
        );
    }
    
    public function bind(array $post, array $get)
    {
        $this->setObject(new Wpjb_Model_Payment($post["id"]));
        
        parent::bind($post, $get);
    }
    
    public function render()
    {
        $id = $this->_data->id;
        $info = $this->getObject()->successMessages();
        
        
        $request = Daq_Request::getInstance();
        $form = $request->post("form");
        
        $token = null;
        if(isset($form["stripe_token"])) {
            $token = $form["stripe_token"];
        }

        if($form["saved_credit_card"] != "-1") {
            $data = array(
                "type" => "customer",
                "id" => $form["saved_credit_card"]
            );
        } elseif(isset($form["options"]) && in_array("save-cc", $form["options"])) {
            $data = $this->_createCustomer($form);
        } else {
            $data = array(
                "type" => "token",
                "id" => $token
            );
        }
        
        $html = '';
        $html.= '<input type="hidden" id="wpjb-stripe-id" value="'.$data["id"].'" />';
        $html.= '<input type="hidden" id="wpjb-stripe-type" value="'.$data["type"].'" />';
        $html.= '<input type="hidden" id="wpjb-stripe-payment-id" value="'.$id.'" />';
        
        $html.= '<div class="wpjb-stripe-result">';
        
        $html.= '<div class="wpjb-stripe-pending wpjb-flash-info">';
        $html.= '<div class="wpjb-flash-icon"><span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin"></span></div>';
        $html.= '<div class="wpjb-flash-body">';
        $html.= '<p><strong>'.__("Placing Order", "wpjobboard").'</strong></p>';
        $html.= '<p>'.__("Waiting for payment confirmation ...", "wpjobboard").'</p>';
        $html.= '</div>';
        $html.= '</div>';
        
        $html.= '<div class="wpjb-flash-info wpjb-none">';
        $html.= '<div class="wpjb-flash-icon"><span class="wpjb-glyphs wpjb-icon-ok"></span></div>';
        $html.= '<div class="wpjb-flash-body"></div>';
        $html.= '</div>';
        
        $html.= '<div class="wpjb-flash-error wpjb-none">';
        $html.= '<div class="wpjb-flash-icon"><span class="wpjb-glyphs wpjb-icon-cancel-circled"></span></div>';
        $html.= '<div class="wpjb-flash-body"></div>';
        $html.= '</div>';
        
        $html.= '</div>';
        
        return $html;
        
    }
    
    public function getIcon() 
    {
        return "wpjb-icon-cc-stripe";
    }
    
    public function getIconFrontend() 
    {
        return "wpjb-icon-credit-card";
    }
    
    protected function _createCustomer($form)
    {
        if(!class_exists("Stripe")) {
            include_once Wpjb_List_Path::getPath("vendor")."/Stripe/Stripe.php";
        }
        
        \Stripe\Stripe::setApiKey($this->conf("secret_key"));
        
        $id = get_user_meta(get_current_user_id(), "_wpjb_stripe_customer_id", true);

        if(!$id) {
            $customer = \Stripe\Customer::create(array(
                "source" => $form["stripe_token"],
                "email" => $form["email"],
                "description" => $form["fullname"]
            ));
            $id = $customer->id;
            update_user_meta(get_current_user_id(), "_wpjb_stripe_customer_id", $id);
            $card = $customer->sources->data[0];
            
        } else {
            $customer = \Stripe\Customer::retrieve($id);
            $card = $customer->sources->create(array("source" => $form["stripe_token"]));
        }

        return array(
            "type" => "customer",
            "customer_id" => $customer->id,
            "id" => $card->id
        );
    }
    
    /**
     * Creates new plan in Stripe
     * 
     * @param Wpjb_Model_Pricing $pricing
     */
    public function createPlan( Wpjb_Model_Pricing $pricing, $interval ) {
        
        if(!class_exists("Stripe")) {
            include_once Wpjb_List_Path::getPath("vendor")."/Stripe/Stripe.php";
        }
        
        \Stripe\Stripe::setApiKey($this->conf("secret_key"));
                
        if( isset( $pricing->meta->stripe_id ) && is_object($pricing->meta->stripe_id) ) {
            $id = $pricing->meta->stripe_id->value();
        } else {
            $id = null;
        }
        
        if( !isset($id) || $id == null ) {
            
            $cArr = Wpjb_List_Currency::getByCode($pricing->currency);
            $amount = ($pricing->price)*pow( 10, $cArr["decimal"] );
            
            $slug = sanitize_title($pricing->title);
            $slug = preg_replace("([^A-z0-9\-]+)", "", $slug);
            
            /*$interval = $pricing->meta->is_recurring_interval->value();
            if( $interval == null ) {
                $interval = 'month';
            }
            $interval_count = $pricing->meta->is_recurring_interval_count->value();
            if( $interval_count == null ) {
                $interval_count = 1;
            }*/
            
            $plan = \Stripe\Plan::create(array(
                "amount"            => (int)$amount,
                "interval"          => 'day', // day, week, month, year
                "interval_count"    => (int)$interval, // max 1 for year, 12 for month and 52 for week
                "id"                => $slug,
                "currency"          => $pricing->currency,
                "product"           => array( "name" => $pricing->title ),
            ));
   
            $q = Daq_Db_Query::create();
            $meta_id = $q->select()->from("Wpjb_Model_Meta t")
                                   ->where("t.name = ?", "stripe_id")
                                   ->where("t.meta_object = ?", "pricing")
                                   ->fetchColumn();
            
            $q = Daq_Db_Query::create();
            $mv_id = $q->select()
                       ->from("Wpjb_Model_MetaValue t")
                       ->where("t.meta_id = ?", $meta_id)
                       ->where("t.object_id = ?", $pricing->id)
                       ->fetchColumn();

            $mv = new Wpjb_Model_MetaValue($mv_id);
            $mv->meta_id = $meta_id;
            $mv->object_id = $pricing->id;
            $mv->value = $plan['id'];
            $mv->save();
        }
    }
    
    /**
     * Removes Plan from Stripe
     * 
     * @param Wpjb_Model_Pricing $pricing
     */
    public function removePlan( Wpjb_Model_Pricing $pricing ) {
        
        if(!class_exists("Stripe")) {
            include_once Wpjb_List_Path::getPath("vendor")."/Stripe/Stripe.php";
        }
        
        \Stripe\Stripe::setApiKey($this->conf("secret_key"));
        
        $id = $pricing->meta->stripe_id->value();
        
        // Remove all subscribtions for this plan
        $subscriptions = \Stripe\Subscription::all(array('plan' => $id, 'limit' => 100, 'status' => 'active'));
        foreach($subscriptions as $sub) {
            $sub->cancel();
        }
        
        // Remove Plan
        $plan = \Stripe\Plan::retrieve( $id );
        $product_id = $plan['product'];
        $plan->delete(); 
        
        // Remove Product
        $product = \Stripe\Product::retrieve( $product_id );
        $product->delete();
        
        // Remove from meta
        $q = Daq_Db_Query::create();
        $meta_id = $q->select()->from("Wpjb_Model_Meta t")
                               ->where("t.name = ?", "stripe_id")
                               ->where("t.meta_object = ?", "pricing")
                               ->fetchColumn();

        $q = Daq_Db_Query::create();
        $mv_id = $q->select()
                   ->from("Wpjb_Model_MetaValue t")
                   ->where("t.meta_id = ?", $meta_id)
                   ->where("t.object_id = ?", $pricing->id)
                   ->fetchColumn();

        $mv = new Wpjb_Model_MetaValue($mv_id);
        $mv->delete();
    }
    
    public function getSubscription( $subscription_id ) {
        
        if( !class_exists( "Stripe" ) ) {
            include_once Wpjb_List_Path::getPath("vendor")."/Stripe/Stripe.php";
        }
        
        \Stripe\Stripe::setApiKey( $this->conf( "secret_key" ) );
        return \Stripe\Subscription::retrieve( $subscription_id );
    }
    
    public function cancelSubsctiption( $subscription_id ) {
        
        if( !class_exists( "Stripe" ) ) {
            include_once Wpjb_List_Path::getPath("vendor")."/Stripe/Stripe.php";
        }
        
        \Stripe\Stripe::setApiKey( $this->conf( "secret_key" ) );
        $subscription = \Stripe\Subscription::retrieve( $subscription_id );
        $subscription->cancel( array("at_period_end" => true ) ); 
    }
}

