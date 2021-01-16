<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Email extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Email", "wpjobboard");
        $instance = Wpjb_Project::getInstance();
        
        $e = $this->create("color_background");
        $e->setValue($instance->getConfig("color_background"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("color_background_body");
        $e->setValue($instance->getConfig("color_background_body"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("color_text");
        $e->setValue($instance->getConfig("color_text"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("color_link");
        $e->setValue($instance->getConfig("color_link"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("color_text_header");
        $e->setValue($instance->getConfig("color_text_header"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("color_text_footer");
        $e->setValue($instance->getConfig("color_text_footer"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("color_button");
        $e->setValue($instance->getConfig("color_button"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("color_button_text");
        $e->setValue($instance->getConfig("color_button_text"));
        $e->setLabel("");
        $this->addElement($e);
        
        $e = $this->create("admin_email");
        $e->setValue($instance->getConfig("admin_email"));
        $e->setLabel(__("Admin Email", "wpjobboard"));
        $e->setHint(__("Admin notifications will be sent to this email address", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Email());
        $e->setAttr("placeholder", get_option("admin_email"));
        $this->addElement($e);
        
        $e = $this->create("email_logo");
        $e->setValue($instance->getConfig("email_logo"));
        $e->setLabel(__("Logo URL", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Url());
        $this->addElement($e);
        
        $e = $this->create("email_footer");
        $e->setValue($instance->getConfig("email_footer"));
        $e->setLabel(__("Footer Text", "wpjobboard"));
        $this->addElement($e);
        
        apply_filters("wpja_form_init_config_email", $this);

    }
}

?>