<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Spam extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Anti SPAM", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("honeypot", __("Honeypot", "wpjobboard"));
        
        $e = $this->create("honeypot_enabled", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Honeypot", "wpjobboard"));
        $e->setValue($instance->getConfig("honeypot_enabled"));
        $e->addOption("1", "1", __("Enable Honeypot", "wpjobboard"));
        $this->addElement($e, "honeypot");
        
        $e = $this->create("honeypot_title");
        $e->setRequired(false);
        $e->setLabel(__("Honeypot Title", "wpjobboard"));
        $e->setAttr("placeholder", "Required Field");
        $e->setValue($instance->getConfig("honeypot_title"));
        $this->addElement($e, "honeypot");
        
        $e = $this->create("honeypot_name");
        $e->setRequired(false);
        $e->setLabel(__("Honeypot Name", "wpjobboard"));
        $e->setAttr("placeholder", "required_field");
        $e->setValue($instance->getConfig("honeypot_name"));
        $this->addElement($e, "honeypot");

        
        $this->addGroup("timetrap", __("Timetrap", "wpjobboard"));
        
        $e = $this->create("timetrap_enabled", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Timetrap", "wpjobboard"));
        $e->setValue($instance->getConfig("timetrap_enabled"));
        $e->addOption("1", "1", __("Enable Timetrap", "wpjobboard"));
        $this->addElement($e, "timetrap");

        $e = $this->create("timetrap_delta");
        $e->setRequired(false);
        $e->setLabel(__("Timetrap Delta (in seconds)", "wpjobboard"));
        $e->setValue($instance->getConfig("timetrap_delta", 2));
        $this->addElement($e, "timetrap");
        
        $e = $this->create("timetrap_key");
        $e->setRequired(false);
        $e->setLabel(__("Timetrap Encode Key", "wpjobboard"));
        $e->setValue($instance->getConfig("timetrap_key"));
        $e->setAttr("placeholder", substr(md5(AUTH_KEY), 4, 12));
        $this->addElement($e, "timetrap");

        apply_filters("wpja_form_init_config_spam", $this);

    }
    
    
}
