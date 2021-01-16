<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package 
 */

abstract class Daq_Application_FrontAbstract extends Daq_Application_Abstract
{
    protected $_link = null;

    protected $_url = null;
    
    protected $_controller = null;
    
    protected $_route = null;

    abstract function getProject();
    
    public function getPage()
    {
        if(!is_null($this->_link)) {
            return $this->_link;
        }
        
        $project = $this->getProject();
        $linkName = $this->getOption("link_name");

        if(!is_null($project->conf($linkName))) {
            $this->_link = get_post($project->conf($linkName));
        }

        return $this->_link;
    }

    public function getUrl()
    {
        global $wp_rewrite;

        $obj = $this->getPage();

        if(!$obj) {
            return rtrim(get_permalink(), "/");
        }
        
        if($wp_rewrite->using_permalinks()) {
            $link = _get_page_link($obj->ID,false,false);
            $link = apply_filters('page_link', $link, $obj->ID, false);
            return rtrim($link, "/");
        } else {
            $qv = $this->getOption("query_var");
            $link = '?page_id='.$obj->ID.'&'.$qv.'=';
            return rtrim(get_home_url(), "/")."/".$link;
        }
    }

    protected function _dispatch($route)
    {
        $this->_route = $route;
        if(isset($route['object'])) {
            $class = $route['object']->objClass;
            $query = new Daq_Db_Query();
            $query->select("*")->from("$class t");
            $object = new $class;
            $reg = $object->getFieldNames();

            foreach($route['param'] as $key => $value) {
                if(in_array($key, $reg)) {
                    $query->where("$key = ?", $value);
                }
            }

            $object = $query->execute();
            if(empty($object)) {
                throw new Exception("Object does not exist");
            } else {
                $route['object'] = $object[0];
            }
        }

        foreach($route['param'] as $k => $v) {
            Daq_Request::getInstance()->addParam("GET", $k, $v);
        }

        $index = $route['action'];

        $ctrl = rtrim($this->_controller, "*").ucfirst($route['module']);
        $action = $route['action']."Action";

        if(!class_exists($ctrl)) {
            throw new Exception("Module [$ctrl] does not exist");
        }
        $controller = new $ctrl;

        if(!method_exists($controller, $action)) {
            throw new Exception("Method [$action] does not exist for controller [$ctrl]");
        }

        $controller->setView($this->_view);
        if($route['object'] && $route['object'] instanceof Daq_Db_OrmAbstract) {
            $controller->setObject($route['object']);
        }
        $this->controller = $controller;
        $this->controller->init();
        $result = $this->controller->$action();

        $this->getProject()->placeHolder = $this->_view;
        
        return $result;
    }
    
    protected function _postDispatch($result, $callback)
    {
        if($result === null) {
            $result = $this->_route['action'];
        }

        if($result === false) {
            if(!function_exists($callback)) {
                throw new Exception("Callback [$callback] does not exist.");
            }
            $callback();
        } elseif(is_file($result)) {
            $this->controller->view->render($result, true);
        } else {
            $this->controller->view->render($result.".php");
        }

        $this->_dispatched = true;
    }
}

?>