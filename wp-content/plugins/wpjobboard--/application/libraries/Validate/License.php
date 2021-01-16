<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of License
 *
 * @author Grzegorz
 */
class Wpjb_Validate_License
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{

    public function isValid($value)
    {
        add_filter("wpjb_upgrade_manager_url", array($this, "forceConnectionType"));
        
        $request = Wpjb_Project::getInstance()->env("upgrade")->wpjobboard->remote("license", array("license"=>$value));

        remove_filter("wpjb_upgrade_manager_url", array($this, "forceConnectionType"));
        
        if(is_string($request)) {
            $this->setError($request);
            return false;
        }
        
        if($request->result <= 0) {
            $this->setError(sprintf(__("External Error: %s", "wpjobboard"), $request->message));
            return false;
        }
        
        return true;
    }
    
    public function forceConnectionType($url) {
        if(Daq_Request::getInstance()->post("license_use_non_ssl") == "1") {
            $type = "http://";
        } else {
            $type = "https://";
        }
        
        return str_replace(array("http://", "https://"), $type, $url);
    }
}

?>
