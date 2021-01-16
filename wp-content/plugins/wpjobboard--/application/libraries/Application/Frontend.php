<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package 
 */

class Wpjb_Application_Frontend extends Daq_Application_FrontAbstract
{
    public $content = null;
    
    public function getProject() 
    {
        return Wpjb_Project::getInstance();
    }
    
    public function dispatch($path = null, $route = null)
    {
        if($path === null && is_array($route)) {
            $this->getRouter()->forceRoute($route);
            $returnResult = true;
        } else {
            $route = $this->getRouter()->match(rtrim($path, "/")."/");
            $returnResult = false;
        }
        
        $route = apply_filters("wpjb_dispatched", $route);
        
        $result = $this->_dispatch($route);
        $result = apply_filters("wpjb_select_template", $result);

        if($result === null) {
            $result = $this->_route['action'];
        }
        
        if(!is_array($result)) {
            $result = array($result);
        }
        
        if($returnResult) {
            ob_start();
        }

        foreach($result as $r) {
            if($r === false) {
                wpjb_flash();
                break;
            } elseif($r == "[did-shortcode]") {
                // do nothing
            } elseif(stripos($r, ".php") && is_file($r)) {
                do_action("wpjb_front_pre_render", $this, $r);
                $this->controller->view->render($r, true);
                break;
            } elseif($this->controller->view->hasFile($r.".php")) {
                do_action("wpjb_front_pre_render", $this, $r);
                $this->controller->view->render($r.".php");
                break;
            } 
        }

        $this->_dispatched = true;

        if($returnResult) {
            $this->content = ob_get_clean();
        }
    }

}

?>