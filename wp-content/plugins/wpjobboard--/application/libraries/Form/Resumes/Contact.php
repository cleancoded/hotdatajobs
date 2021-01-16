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
class Wpjb_Form_Resumes_Contact extends Daq_Form_Abstract
{
    public function init() 
    {
        $this->addGroup("contact", __("Contact", "wpjobboard"));
        
        $e = $this->create("contact", "hidden");
        $e->setValue(1);
        $this->addElement($e);
        
        $e = $this->create("fullname");
        $e->setLabel(__("Your Name", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "contact");
        
        $e = $this->create("email");
        $e->setLabel(__("Your Email", "wpjobboard"));
        $e->setRequired(true);
        $e->addValidator(new Daq_Validate_Email);
        $this->addElement($e, "contact");
        
        $e = $this->create("message", "textarea");
        $e->setLabel(__("Message", "wpjobboard"));
        $e->setRequired(true);
        $this->addElement($e, "contact");
        
        apply_filters("wpjr_form_init_contact", $this);
    }
}

?>
