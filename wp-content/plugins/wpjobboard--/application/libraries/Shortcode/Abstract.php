<?php

abstract class Wpjb_Shortcode_Abstract {
    
    public $view = null;
    
    /**
     * Return request object
     * 
     * @return Daq_Request
     */
    public function getRequest() {
        return Daq_Request::getInstance();
    }
    
    /**
     * Returns a template file
     * 
     * @param string $module
     * @param string $file
     */
    public function getTemplate($module, $file) {
        
        if(stripos($file, ".php") === false) {
            $file .= ".php";
        }
        
        $dirs = array(
            get_stylesheet_directory()."/wpjobboard/".$module,
            get_template_directory()."/wpjobboard/".$module,
            Wpjb_Project::getInstance()->env("template_base") . $module
        );
            
        $dirs = apply_filters( "wpjb_templates_dir", $dirs, $module, $file );
        
        foreach($dirs as $dir) {
            if(is_file($dir."/".$file)) {
                return $dir."/".$file;
            }
        }

        throw new Exception("View file not found in directory list");
    }
    
    public function render($module, $file = null) {
        
        if($file === null) {
            $this->renderFallback($module);
            return;
        }
        
        ob_start();
        foreach($this->view as $k => $v) {
            $$k = $v;
        }
        include $this->getTemplate($module, $file);
        return ob_get_clean();
    }
    
    /**
     * 
     * 
     * @deprecated since version 5.0
     * @param string $file
     */
    public function renderFallback($file) {
        switch(get_class($this)) {
            case "Wpjb_Shortcode_Candidate_Panel":
            case "Wpjb_Shortcode_Candidate_Register":
            case "Wpjb_Shortcode_Resumes_List":
            case "Wpjb_Shortcode_Resumes_Search":
                $module = "resumes";
                break;
            default:
                $module = "job-board";
                break;
        }
        
        foreach($this->view as $k => $v) {
            $$k = $v;
        }
        include $this->getTemplate($module, $file);
    }
    
    public function getFlash() {
        if(is_object(Wpjb_Project::getInstance()->placeHolder)) {
            $flash = Wpjb_Project::getInstance()->placeHolder->_flash;
        } else {
            $flash = new Wpjb_Utility_Session();
        }
        
        return $flash;
    }
    
    public function addError($error) {
        $this->getFlash()->addError($error);
    }
    
    public function addInfo($info) {
        $this->getFlash()->addInfo($info);
    }
    
    public function flash() {
        ob_start();
        wpjb_flash();
        return ob_get_clean();
    }
    
    /**
     * Kind of abstract function
     * 
     * This is a place for shortcodes to setup action and filters that will
     * be executed outside of the shortcode for example on "init" or "wp" action.
     * 
     * @return void
     */
    public function listen() {
        
    }
    
}
