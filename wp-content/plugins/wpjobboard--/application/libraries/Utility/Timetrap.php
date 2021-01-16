<?php

class Wpjb_Utility_Timetrap {
    
    public static $name = "_timetrap";
    
    public function __construct() {
        $this->connect();
    }
    
    public function connect() {

        add_filter("wpjb_form_global_error", array($this, "error"), 10, 2);
        
        add_filter("wpjb_form_init_job", array($this, "timetrap"));
        add_filter("wpjb_form_init_apply", array($this, "timetrap"));
        add_filter("wpjb_form_init_company", array($this, "timetrap"));
        add_filter("wpjr_form_init_resume", array($this, "timetrap"));
        add_filter("wpjr_form_init_register", array($this, "timetrap"));
        add_filter("wpjr_form_init_contact", array($this, "timetrap"));
    }
    
    /**
     * Adds timetrap field to various forms.
     * 
     * This functions is executed by wpjb_form_init_* filters.
     * 
     * @param Daq_Form_Abstract $form
     * @return Daq_Form_Abstract
     */
    public function timetrap($form) {
        
        $e = $form->create(self::$name, "hidden");
        $e->setValue(Daq_Validate_Timetrap::encode(Daq_Validate_Timetrap::current()));
        $e->addValidator(new Daq_Validate_Timetrap());
        $e->setRequired(true);
        $form->addElement($e, "_internal");

        return $form;
    }
    
    /**
     * Sets custom error message if timetrap field is filled incorrectly.
     * 
     * @param string $error             Error message text
     * @param Daq_Form_Abstract $form   Form being proccessed
     * @return string                   Error message text
     */
    public function error($error, $form) {

        if(array_key_exists(self::$name, $form->getErrors())) {
            $error = __("You are submitting the forms too fast.", "wpjobboard");
        }
        
        return $error;
    }
    

}
