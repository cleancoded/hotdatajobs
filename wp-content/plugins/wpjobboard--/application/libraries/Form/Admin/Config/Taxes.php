<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Taxes extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Taxes", "wpjobboard");
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "default", __( "Basic Taxes Configuration", "wpjobboard" ) );

        $e = $this->create("taxes_enabled", "checkbox");
        $e->setLabel(__("Enable Taxes", "wpjobboard"));
        $e->addOption("1", "1", __("Check this to enable taxes", "wpjobboard"));
        $e->setValue($instance->getConfig("taxes_enabled"));
        $this->addElement($e, "default");
        
        $e = $this->create("taxes_default_rate");
        $e->setLabel(__("Default Tax Rate", "wpjobboard"));
        $e->setHint(__("Enter a percentage such as 10.50", "wpjobboard"));
        $e->setValue($instance->getConfig("taxes_default_rate"));
        $this->addElement($e, "default");
        
        $e = $this->create("taxes_price_type", "radio");
        $e->setLabel(__("Prices Entered with Tax", "wpjobboard"));
        $e->addOption("gross", "gross", __("Yes, i will enter prices inclusive of tax", "wpjobboard"));
        $e->addOption("net", "net", __("No, i will enter prices exclusive of tax", "wpjobboard"));
        $e->setValue($instance->getConfig("taxes_price_type"));
        $this->addElement($e, "default");


        apply_filters("wpja_form_init_config_taxes", $this);

    }
    
}

?>