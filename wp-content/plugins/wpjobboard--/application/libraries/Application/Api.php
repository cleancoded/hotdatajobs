<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package 
 */

class Wpjb_Application_Api extends Daq_Application_FrontAbstract
{
    public function getProject() 
    {
        return Wpjb_Project::getInstance();
    }
    
    public function dispatch($path = null, $route = null)
    {
        //var_dump($path);
        $route = $this->getRouter()->match(rtrim($path, "/")."/");
        $route = apply_filters("wpjb_dispatched", $route);
        //var_dump($route);
        
        try {
            $this->_dispatch($route);
        } catch(Exception $e) {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(array("status"=>"400", "message"=>$e->getMessage()));
        }

        exit;
    }

}

?>