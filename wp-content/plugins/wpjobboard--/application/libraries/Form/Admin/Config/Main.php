<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Main extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Common Settings", "wpjobboard");
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "default", __( "Basic Settings", "wpjobboard" ) );

        $e = $this->create("show_maps", "checkbox");
        $e->setValue($instance->getConfig("show_maps"));
        $e->setLabel(__("Maps", "wpjobboard"));
        $e->addOption(1, 1, __("Show Google Maps", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e, "default");
        
        $e = $this->create("xml_import_enabled", "checkbox");
        $e->setValue($instance->getConfig("xml_import_enabled"));
        $e->setLabel(__("XML Import", "wpjobboard"));
        $e->addOption(1, 1, __("Enable XML Import", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e, "default");
        
        apply_filters("wpja_form_init_config_main", $this);

    }
}

?>