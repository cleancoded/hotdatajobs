<?php
/**
 * Description of Admin
 *
 * @author greg
 * @package 
 */

class Wpjb_Application_Admin extends Daq_Application_Abstract
{

    public function dispatch($path = null, $route = null)
    {
        $path = rtrim($path, "/")."/";
        $route = $this->getRouter()->match($path);
        foreach($route['param'] as $k => $v) {
            Daq_Request::getInstance()->addParam("GET", $k, $v);
        }

        $index = $route['module']."/".$route['action'];

        $ctrl = rtrim($this->_controller, "*").ucfirst($route['module']);
        $action = $route['action']."Action";

        if(!class_exists($ctrl)) {
            throw new Exception("Module [$ctrl] does not exist");
        }
        $controller = new $ctrl;

        $info = wp_get_current_user();
        $isAdmin = true;
        if(!isset($info->wp_capabilities) || !$info->wp_capabilities['administrator']) {
            $isAdmin = false;
        }

        $this->_view->slot("is_admin", $isAdmin);

        if(!is_callable(array($controller, $action))) {
            throw new Exception("Method [$action] does not exist for controller [$ctrl]");
        }
        
        
        try {
            $controller->setView($this->_view);
            $controller->init();
            $result = $controller->$action();
        } catch(Daq_Controller_Redirect_Exception $e) {
            $result = false;
        }
        
        if(is_string($result)) {
            $index = $route['module']."/".$result;
        }

        if($result !== false) {
            $controller->view->render($index.".php");
        }
        

    }
}

?>