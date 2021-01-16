<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Careerbuilder extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("CareerBuilder API", "wpjobboard");
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "default", __( "Careerbuilder API", "wpjobboard" ) );

        $e = $this->create("api_cb_key");
        $e->setValue($instance->getConfig("api_cb_key"));
        $e->setLabel(__("Career Builder API Key", "wpjobboard"));
        $e->setHint(__("Claim your key at http://api.careerbuilder.com/RequestDevKey.aspx, It's required to use Import feature", "wpjobboard"));
        $this->addElement($e, "default");

        apply_filters("wpja_form_init_config_careerbuilder", $this);

    }
}

?>