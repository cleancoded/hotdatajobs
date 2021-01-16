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
class Wpjb_Form_Admin_Config_Stripe extends Wpjb_Form_Abstract_Payment 
{
    public function init()
    {
        parent::init();
        
        $this->addGroup("stripe", __("Stripe", "wpjobboard"));
        
        $e = $this->create("secret_key");
        $e->setValue($this->conf("secret_key"));
        $e->setLabel(__("Secret Key", "wpjobboard"));
        $this->addElement($e, "stripe");
        
        $e = $this->create("publishable_key");
        $e->setValue($this->conf("publishable_key"));
        $e->setLabel(__("Publishable Key", "wpjobboard"));
        $this->addElement($e, "stripe");
        
        $e = $this->create("stripe_description");
        $e->setValue($this->conf("stripe_description"));
        $e->setLabel(__("Description", "wpjobboard"));
        $e->setAttr("placeholder", __("Payment ID: %s", "wpjobboard"));
        $e->setHint(__("The %s will be replaced with order ID assigned by WPJobBoard", "wpjobboard"));
        $this->addElement($e, "stripe");
        
        $e = $this->create("statement_descriptor");
        $e->setValue($this->conf("statement_descriptor"));
        $e->setLabel(__("Payment Description", "wpjobboard"));
        $e->setHint(__("Extra information about a charge. This will appear on your customer’s credit card statement.", "wpjobboard"));
        $this->addElement($e, "stripe");
        
        
    }
}

?>
