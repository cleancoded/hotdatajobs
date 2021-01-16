<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Android extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Android App", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup( "default", __( "Android API", "wpjobboard" ) );
        
        $e = $this->create("android_encrypt_key");
        $e->setRequired(true);
        $e->setLabel(__("Android Encryption Key", "wpjobboard"));
        $e->setValue($instance->getConfig("android_encrypt_key"));
        $e->setRenderer(array($this, "randomize"));
        $this->addElement($e, "default");

        
        $e = $this->create("android_site_url");
        $e->setLabel(__("Android Site URL", "wpjobboard"));
        $e->setValue(home_url());
        $e->setAttr("readonly", "readonly");
        $this->addElement($e, "default");
        


        apply_filters("wpja_form_init_config_android", $this);

    }
    
    public function isValid(array $values)
    {
        $request = Daq_Request::getInstance();
        
        if($request->post("generate")) {
            $values["android_encrypt_key"] = md5( time() . "-" . home_url() . NONCE_KEY );
        }
        
        return parent::isValid($values);
         
    }
    
    public function randomize($field, $form) {
        if($field->getValue() == "") {
            $html = new Daq_Helper_Html("input", array(
                "type" => "submit",
                "name" => "generate",
                "class" => "button-secondary",
                "value" =>  __("Generate", "wpjobboard")
            ));
            $html->forceLongClosing(false);
            return $html;
        } else {
            return $field->render();
        }
    }
}

?>