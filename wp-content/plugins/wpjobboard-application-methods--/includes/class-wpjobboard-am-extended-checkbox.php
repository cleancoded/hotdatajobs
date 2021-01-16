<?php

class Wpjb_Am_Extended_Checkbox extends Daq_Form_Element_Checkbox {
    
    
    protected $config = null;
    
    public function setConfig($conf) {
        $this->config = $conf;
    }
    
    public function getConfig() {
        return $this->config;
    }
    
    public function validate() {
        
        $this->_hasErrors = false;
        $count = 0;
        $arr = array();
        
        $value = (array)$this->getValue();
        foreach($value as $v) {
            
            if(is_array($v)) {
                $v = null;
            } elseif(is_string($v)) {
                $v = trim($v);
            }
            
            if(!empty($v)) {
                $count++;
                $arr[] = $v;
            }
        }

        if(empty($arr) && !$this->isRequired()) {
            return true;
        } else {
            $this->addValidator(new Daq_Validate_Required());
        }
        
        $choices = $this->getMaxChoices();
        if($choices > 0) {
            $this->addValidator(new Daq_Validate_Choices(null, $choices));
        }
        
        $allowed = array();
        foreach($this->getOptions() as $opt) {
            $allowed[] = trim($opt["value"]);
        }
        $this->addValidator(new Daq_Validate_InArray($allowed));
        
        $request = Daq_Request::getInstance();
        $id = "wpjb_session_".str_replace("-", "_", wpjb_transient_id());
        $transient = wpjb_session()->get($id);

        if(!isset($transient["job"])) {
            $job = array(
                "wpjb-am-email" => array(),
                "wpjb-am-url" => ""
            );
        } else {
            $job = $transient["job"];
        }
        
        $emails = $request->post("wpjb-am-email", null);
        if( ( !isset( $emails ) || empty( $emails ) ) && !get_query_var('wpjb-id') ) {
            $emails = $job["wpjb-am-email"];
        }
        
        $url = $request->post("wpjb-am-url", null);
        if( ( !isset( $url ) || empty( $url ) ) && !get_query_var('wpjb-id') ) {
            $url = $job["wpjb-am-url"];
        }

        $errors = array();
        if(is_array($this->getValue()) && count($this->getValue()) > 0) {
            foreach($this->getValue() as $method) {

                if($method == "email") {
                    foreach(explode(",", $emails) as $email) {
                        if(!is_email($email)) {
                            $this->_hasErrors = true;
                            $errors[] = __("One of provided e-mail addresses is invalid", "wpjobboard-am");
                            break;
                        }
                    }
                }    

                if($method == "url") {
                    if(!filter_var($url, FILTER_VALIDATE_URL)) {
                        $this->_hasErrors = true;
                        $errors[] = __("Provided URL address is invalid", "wpjobboard-am");
                        break;
                    }
                }
            }
        }
        
        foreach($this->getValidators() as $validate) {
            if(!$validate->isValid($arr)) {
                $this->_hasErrors = true;
                $this->_errors = $validate->getErrors();
                break;
            }
        }
        
        if($this->_hasErrors) {
            $this->_errors = array_merge($errors, $this->_errors);
        }

        return !$this->_hasErrors;
    }
}
