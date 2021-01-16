<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Payment
 *
 * @author Grzegorz
 */
class Wpjb_Form_Abstract_Payment extends Daq_Form_Abstract
{
    protected $_config = array();
    
    public function __construct($options = array(), $config = array())
    {
        $this->_config = $config;
        parent::__construct();
    }
    
    public function conf($option, $default = null)
    {
        if(isset($this->_config[$option])) {
            return $this->_config[$option];
        }
        
        return $default;
    }
    
    public function init()
    {
        $this->addGroup("main", __("Configuration", "wpjobboard"));
        
        $e = $this->create("disabled", "radio");
        $e->setLabel(__("Availability", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int);
        $e->addOption("0", "0", __("Enable this payment method.", "wpjobboard"));
        $e->addOption(1, 1, __("Disable this payment method.", "wpjobboard"));
        $e->setValue($this->conf("disabled"));
        $this->addElement($e, "main");
        
        $e = $this->create("title");
        $e->setLabel(__("Title", "wpjobboard"));
        $e->setValue($this->conf("title"));
        $this->addElement($e, "main");
        
        $e = $this->create("order");
        $e->setLabel(__("Order", "wpjobboard"));
        $e->setHint(__("Payments are sorted ascending by order number.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int(0));
        $e->setValue($this->conf("order", "0"));
        $this->addElement($e, "main");
    }
    
}

?>
