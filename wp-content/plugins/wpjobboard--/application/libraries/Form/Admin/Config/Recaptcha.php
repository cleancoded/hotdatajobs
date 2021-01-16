<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Recaptcha
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_Config_Recaptcha extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("reCAPTCHA", "wpjobboard");
        
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "default", __( "reCAPTCHA API", "wpjobboard" ) );
        
        $e = $this->create("front_recaptcha_type", "select");
        $e->setValue($instance->getConfig("front_recaptcha_type", "no-captcha"));
        $e->setLabel(__("Captcha Type", "wpjobboard"));
        $e->addOption("re-captcha", "re-captcha", __("reCAPTCHA", "wpjobboard"));
        $e->addOption("no-captcha", "no-captcha", __("noCAPTCHA (recommended)", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("front_recaptcha_public");
        $e->setValue($instance->getConfig("front_recaptcha_public"));
        $e->setLabel(__("reCAPTCHA Public Key", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("front_recaptcha_private");
        $e->setValue($instance->getConfig("front_recaptcha_private"));
        $e->setLabel(__("reCAPTCHA Private Key", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("front_recaptcha_theme", "select");
        $e->setValue($instance->getConfig("front_recaptcha_theme", "light"));
        $e->setLabel(__("Captcha Theme", "wpjobboard"));
        $e->addOption("light", "light", __("Light", "wpjobboard"));
        $e->addOption("dark", "dark", __("Dark", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("front_recaptcha_media", "select");
        $e->setValue($instance->getConfig("front_recaptcha_media", "image"));
        $e->setLabel(__("Captcha Media", "wpjobboard"));
        $e->addOption("image", "image", __("Image", "wpjobboard"));
        $e->addOption("audio", "audio", __("Audio", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("front_recaptcha_enabled", "checkbox");
        $e->setValue($instance->getConfig("front_recaptcha_enabled"));
        $e->setLabel(__("Enable reCAPTCHA for", "wpjobboard"));
        $e->addOption("wpjb_form_init_job", "wpjb_form_init_job", __("Add Job Form", "wpjobboard"));
        $e->addOption("wpjb_form_init_apply", "wpjb_form_init_apply", __("Application Form", "wpjobboard"));
        $e->addOption("wpjb_form_init_company", "wpjb_form_init_company", __("Employer Registration Form", "wpjobboard"));
        $e->addOption("wpjb_form_init_login", "wpjb_form_init_login", __("Employer Login Form", "wpjobboard"));
        $e->addOption("wpjr_form_init_register", "wpjr_form_init_register", __("Candidate Registration Form", "wpjobboard"));
        $e->addOption("wpjr_form_init_login", "wpjr_form_init_login", __("Candidate Login Form", "wpjobboard"));
        $this->addElement($e, "default");

    }
}

?>