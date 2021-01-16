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
class Wpjb_Form_Payment_Stripe extends Daq_Form_Abstract 
{

    public function __construct($options = array()) 
    {
        $request = Daq_Request::getInstance();
        
        if(DOING_AJAX && $request->post("action") == "wpjb_payment_render") {
            add_filter("wpjb_payment_render_response", array($this, "script"));
        }
        
        parent::__construct($options);
    }
    
    public function script($response) 
    {
        $scripts = wp_scripts()->registered["wpjb-stripe"];
        $response["load"] = array($scripts->src."?time=".time());
        
        return $response;
    }
    
    public function init() 
    {
        $stripe = new Wpjb_Payment_Stripe;
        
        $e = $this->create("stripe_publishable_key", "hidden");
        $e->setValue($stripe->conf("publishable_key"));
        $this->addElement($e, "_internal");
        
        $this->addGroup("default");
        
        $e = $this->create("fullname");
        $e->setLabel(__("Full Name", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "default");
        
        $e = $this->create("email");
        $e->setLabel(__("Email", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "default");
        
        $this->addGroup("stripe", __("Credit Card", "wpjobboard"));
        
        $id = get_user_meta(get_current_user_id(), "_wpjb_stripe_customer_id", true);
        $cards = array();
        
        if($id) {
            if(!class_exists("Stripe")) {
                include_once Wpjb_List_Path::getPath("vendor")."/Stripe/Stripe.php";
            }
            $stripe = new Wpjb_Payment_Stripe();
            \Stripe\Stripe::setApiKey($stripe->conf("secret_key"));
            $customer = \Stripe\Customer::retrieve($id);
            foreach($customer->sources->data as $cc) {
                $cards[] = array(
                    "id" => $cc->id,
                    "desc" => sprintf("%s ****-****-****-%s (%s/%s)", $cc->brand, $cc->last4, $cc->exp_month, $cc->exp_year)
                );
            }
        }
        
        $e = $this->create("saved_credit_card", "select");
        $e->setLabel(__("Saved Credit Cards", "wpjobboard"));
        $e->addOption("-1", "-1", __("Create New Card ...", "wpjobboard"));
        foreach($cards as $card) {
            $e->addOption($card["id"], $card["id"], $card["desc"]);
        }
        $this->addElement($e, "stripe");
        
        $e = $this->create("card_number");
        $e->setLabel(__("Card Number", "wpjobboard"));
        $e->addClass("wpjb-stripe-cc");
        $e->setAttr("data-stripe", "number");
        $e->setRenderer(array($this, "inputStripe"));
        $this->addElement($e, "stripe");
        
        $e = $this->create("cvc");
        $e->setLabel(__("CVC", "wpjobboard"));
        $e->addClass("wpjb-stripe-cc");
        $e->setAttr("data-stripe", "cvc");
        $e->setRenderer(array($this, "inputStripe"));
        $this->addElement($e, "stripe");
        
        $e = $this->create("expiration");
        $e->setLabel(__("Expiration (MM/YYYY)", "wpjobboard"));
        $e->addClass("wpjb-stripe-cc");
        $e->setRenderer(array($this, "inputExpiration"));
        $this->addElement($e, "stripe");
        
        if(get_current_user_id()) {
            $e = $this->create("options", "checkbox");
            $e->addOption("save-cc", "save-cc", __("Save this card data for future use.", "wpjobboard"));
            $this->addElement($e, "stripe");
        }
        
        apply_filters("wpjb_form_init_payment_stripe", $this);
    }

    public function inputStripe($input) 
    {
        $e = new Daq_Form_Element_Text("");
        $e->addClass($input->getClasses());
        foreach($input->getAttr() as $aKey => $aVal) {
            $e->setAttr($aKey, $aVal);
        }
        
        echo $e->render();
    }
    
    public function inputExpiration()
    {
        $month = new Daq_Form_Element_Text("");
        $month->addClass("wpjb-stripe-cc");
        $month->setAttr("data-stripe", "exp-month");
        
        
        $year = new Daq_Form_Element_Text("");
        $year->addClass("wpjb-stripe-cc");
        $year->setAttr("data-stripe", "exp-year");
        
        echo '<div class="wpjb-stripe-expiration">'.$month->render() . "<strong>/</strong>" . $year->render().'</div>';
    }
}


?>
