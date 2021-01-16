<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayPal
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_Config_Paypal extends Wpjb_Form_Abstract_Payment
{ 
    public function init()
    {
        parent::init();
        
        $this->_env = array(
            1 => __("Sandbox (For testing only)", "wpjobboard"),
            2 => __("Production (Real money)", "wpjobboard")
        );

        $this->addGroup("paypal", __("PayPal", "wpjobboard"));
        
        $e = $this->create("paypal_email");
        $e->setValue($this->conf("paypal_email"));
        $e->setLabel(__("PayPal eMail", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Email());
        $this->addElement($e, "paypal");

        $e = $this->create("paypal_env", Daq_Form_Element::TYPE_SELECT);
        $e->setValue($this->conf("paypal_env"));
        $e->setLabel(__("PayPal Environment", "wpjobboard"));
        $e->addValidator(new Daq_Validate_InArray(array_keys($this->_env)));
        foreach($this->_env as $k => $v) {
            $e->addOption($k, $k,  $v);
        }
        $this->addElement($e, "paypal");
        
        
    }
}

?>
