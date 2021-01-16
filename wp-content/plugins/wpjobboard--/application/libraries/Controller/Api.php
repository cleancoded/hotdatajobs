<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package 
 */

class Wpjb_Controller_Api
{
    /**
     * User access (one of public, user, employer, admin)
     *
     * @var string
     */
    protected $_access = null;
    
    /**
     * Access method (one of GET, POST, PUT, DELETE)
     *
     * @var string
     */
    protected $_method = null;
    
    /**
     * Authenticated user that is using API right now.
     *
     * @var WP_User
     */
    protected $_user = null;
    
    public function setAccess($access)
    {
        $this->_access = $access;
    }
    
    public function getAccess()
    {
        return $this->_access;
    }
    
    public function setMethod($method)
    {
        $this->_method = $method;
    }
    
    public function getMethod()
    {
        return $this->_method;
    }
    
    public function setUser($user) 
    {
        $this->_user = $user;
    }
    
    public function getUser()
    {
        return $this->_user;
    }
 
    /**
     * Returns configuration option by path
     * @example get/public/allowed_user_actions returns $this->getInit()["public"]["allowed_user_actions"]
     * @example //allowed_user_actions returns value as above but method and role are derived from current object
     * 
     * @param string $path
     * @return array
     * @throws Exception
     */
    public function conf($path) 
    {
        // method/role/conf
        list($method, $role, $key) = explode("/", $path);
        
        if(empty($method)) {
            $method = $this->_method."Init";
        } else {
            $method.= "Init";
        }
        
        if(empty($role)) {
            $role = $this->_access;
        }
        
        if($method == "allInit") {
            $conf = $this->init();
        } elseif(method_exists($this, $method)) {
            $conf = $this->$method();
        } else {
            throw new Exception("Unknown method [$method]");
        }
        
        if(!isset($conf[$key])) {
            throw new Exception("Option [$key] does not exist");
        }
        
        if(!empty($role)) {
            return $conf[$key][$role];
        } else {
            return $conf[$key];
        }
    }
    
    public function userCan($action) {
        $init = $this->init();
        
        if(!isset($init["allowed_user_actions"][$this->_access])) {
            throw new Exception("Invalid role [".$this->_access."]");
        }
        
        $access = $init["allowed_user_actions"][$this->_access];
        
        if(!in_array($action, $access)) {
            throw new Exception("User cannot execute action [$action]");
        }
    }
    
    public function reduce($object, $hide)
    {
        if(isset($hide["default"]) && is_array($hide["default"])) {
            foreach($hide["default"] as $h) {
                if(isset($object[$h])) {
                    unset($object[$h]);
                }
            }
        }

        if(isset($hide["meta"]) && is_array($hide["meta"])) {
            foreach($hide["meta"] as $h) {
                if(isset($object["meta"][$h])) {
                    unset($object["meta"][$h]);
                }
            }
        }
        
        return $object;
    }
    
}

?>