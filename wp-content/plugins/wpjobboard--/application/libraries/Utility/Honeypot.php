<?php

class Wpjb_Utility_Honeypot {
    
    public function __construct() {
        $this->connect();
    }
    
    public function connect() {
        add_action("wp_head", array($this, "head"));
        
        add_filter("wpjb_form_global_error", array($this, "error"), 10, 2);
        
        add_filter("wpjb_form_init_job", array($this, "honeypot"));
        add_filter("wpjb_form_init_apply", array($this, "honeypot"));
        add_filter("wpjb_form_init_company", array($this, "honeypot"));
        add_filter("wpjr_form_init_resume", array($this, "honeypot"));
        add_filter("wpjr_form_init_register", array($this, "honeypot"));
        add_filter("wpjr_form_init_contact", array($this, "honeypot"));
    }
    
    /**
     * CSS to hide the honeypot field.
     * 
     * This function is executed by wp_head action.
     * 
     * @return void
     */
    public function head() {
        $css = '.wpjb-fieldset-%s { display: none !important; }';
        $style = new Daq_Helper_Html("style", array(
            "type" => "text/css"
        ), sprintf($css, wpjb_conf("honeypot_name", "required_field")));
        echo $style->render();
    }
    
    /**
     * Adds honeypot field to various forms.
     * 
     * This functions is executed by wpjb_form_init_* filters.
     * 
     * @param Daq_Form_Abstract $form
     * @return Daq_Form_Abstract
     */
    public function honeypot($form) {
        
        $field = wpjb_conf("honeypot_name", "required_field");
        $title = wpjb_conf("honeypot_title", "Required Title");
        
        $form->addGroup($field, "");

        $e = $form->create($field, "text");
        $e->setValue("");
        $e->setLabel($title);
        $e->addValidator(new Daq_Validate_Honeypot());
        $form->addElement($e, $field);

        return $form;
    }
    
    /**
     * Sets custom error message if honeypot field is filled incorrectly.
     * 
     * @param string $error             Error message text
     * @param Daq_Form_Abstract $form   Form being proccessed
     * @return string                   Error message text
     */
    public function error($error, $form) {
        $key = wpjb_conf("honeypot_name", "required_field");
        
        if(array_key_exists($key, $form->getErrors())) {
            $error = __("Trying to submit SPAM?", "wpjobboard");
        }
        
        return $error;
    }
}
