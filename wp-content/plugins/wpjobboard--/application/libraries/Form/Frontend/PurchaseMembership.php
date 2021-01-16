<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contact
 *
 * @author Grzegorz
 */
class Wpjb_Form_Frontend_PurchaseMembership extends Daq_Form_Abstract
{
    protected function _listings()
    {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Pricing t");
        
        
        if($this->_options["force_product_id"]) {
            $query->where("id = ?", $this->_options["force_product_id"]);
        } else {
            $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP);
        }
        
        return $query->execute();
    }
    
    public function init() 
    {
        $this->addGroup("purchase", "");
        
        $e = $this->create("purchase", "hidden");
        $e->setValue(1);
        $this->addElement($e, "_internal");
        
        $e = $this->create("listing", "radio");
        $e->setLabel(__("Membership Package", "wpjobboard"));
        $e->setRequired(true);
        $e->setRenderer("wpjb_form_helper_membership");
        $listings = $this->_listings();
        foreach($listings as $p) {
            $e->addOption(
                $p->id, 
                $p->price_for."_0_".$p->id,
                $p->title
            );
        }
        if(!empty($listings)) {
            $e->setValue($listings[0]->price_for."_0_".$listings[0]->id);
        }
        $this->addElement($e, "purchase");
        
        $e = $this->create("coupon");
        $e->setLabel(__("Discount Code", "wpjobboard"));
        $this->addElement($e, "purchase");
        
        $e = $this->create("payment_method", "radio");
        $e->setLabel(__("Payment Method", "wpjobboard"));
        $e->setRequired(true);
        $factory = Wpjb_Project::getInstance()->payment;
        $engines = $factory->getEnabled();
        foreach($engines as $engine) {
            $engine = new $engine;
            $e->addOption($engine->getEngine(), $engine->getEngine(), $engine->getCustomTitle());
        }
        if(!empty($engines)) {
            $e->setValue(key($engines));
        }
        if(count($listings)>1 || $listings[0]->price!=0) {
            $this->addElement($e, "purchase");
        }
        
        $e = $this->create("email");
        $e->setLabel(__("Your Email", "wpjobboard"));
        $e->setRequired(true);
        $e->addValidator(new Daq_Validate_Email);
        $e->setValue(wp_get_current_user()->user_email);
        $this->addElement($e, "purchase");
        
        apply_filters("wpjr_form_init_purchase", $this);
    }
    
    public function isValid(array $values)
    { 
        $parts = explode("_", $values["listing"]);
        $listing = new Wpjb_Model_Pricing($parts[2]);
        $validator = new Wpjb_Validate_Coupon($listing->currency, Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP);

        if($this->hasElement("coupon")) {
            $this->getElement("coupon")->addValidator($validator);
        }

        return parent::isValid($values);
    }
    
    
}

?>
