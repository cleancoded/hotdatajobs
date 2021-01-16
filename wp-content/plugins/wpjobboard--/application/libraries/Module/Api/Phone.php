<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bookmarks
 *
 * @author Grzegorz
 */
class Wpjb_Module_Api_Phone extends Wpjb_Controller_Api 
{
    public function init()
    {
        return array(
            "allowed_user_actions" => array(
                "public" => array(),
                "user" => array("get", "post", "put", "delete"),
                "employer" => array("get", "post", "put", "delete"),
                "admin" => array("get", "post", "put", "delete")
            )
        );
    }
    
    public function getInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            )
        );
    }
    
    public function getAction($params = array())
    {
        $user = $this->getUser();
        
        $user = $this->getUser();
        $mobile = get_user_meta($user->ID, "wpjb_mobile_device", true);
        $response = array("status"=>200, "total"=>0, "data"=>array());
        
        foreach($mobile->device as $k => $device) {
            
            $dev = array(
                "id" => $k,
                "mobile_id" => $device["mobile_id"],
                "mobile_os" => $device["mobile_os"],
            );
            
            $response["data"][] = $this->reduce($dev, $this->conf("//hidden_fields"));
            $response["total"]++;
        }
        
        return $response;
    }
    
    public function postInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            )
        );
    }
    
    public function postAction($params = array())
    {
        foreach(array("mobile_os", "mobile_id") as $k) {
            if(!isset($params[$k]) || empty($params[$k])) {
                throw new Exception(sprintf("Parameter '%s' not set.", $k));
            }
        }
        
        /* @var $user WP_User */
        
        if(!in_array($params["mobile_os"], array("android", "ios"))) {
            $ex = sprintf("Invalid mobile operating system [%s].", $params["mobile_os"]);
            throw new Exception($ex);
        }
        
        $user = $this->getUser();
        $mobile = get_user_meta($user->ID, "wpjb_mobile_device", true);
        
        if($mobile == "") {
            $mobile = new stdClass();
            $mobile->id = 1;
            $mobile->device = array();
        }
        
        $id = $mobile->id;
        
        $mobile->device[$id] = array(
            "mobile_os" => $params["mobile_os"],
            "mobile_id" => $params["mobile_id"]
        );
        
        $mobile->id++;
        
        update_user_meta($user->ID, "wpjb_mobile_device", $mobile);
        
        $result = array(
            "id" => $id,
            "mobile_id" => $mobile->device[$id]["mobile_id"],
            "mobile_os" => $mobile->device[$id]["mobile_os"],
        );
        
        return $this->reduce($result, $this->conf("//hidden_fields"));
    }
    
    public function putInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            )
        );
    }
    
    
    public function putAction($params = array())
    {
        foreach(array("id", "mobile_os", "mobile_id") as $k) {
            if(!isset($params[$k]) || empty($params[$k])) {
                throw new Exception(sprintf("Parameter '%s' not set.", $k));
            }
        }
        
        if(!in_array($params["mobile_os"], array("android", "ios"))) {
            $ex = sprintf("Invalid mobile operating system [%s].", $params["mobile_os"]);
            throw new Exception($ex);
        }
        
        $user = $this->getUser();
        $mobile = get_user_meta($user->ID, "wpjb_mobile_device", true);
        
        $id = $params["id"];
        
        if(!isset($mobile->device[$id])) {
            $ex = sprintf("Device with index [%d] does not exist.", $id);
            throw new Exception($ex);
        }
        
        $mobile->device[$id] = array(
            "mobile_os" => $params["mobile_os"],
            "mobile_id" => $params["mobile_id"]
        );
        
        update_user_meta($user->ID, "wpjb_mobile_device", $mobile);
        
        $result = array(
            "id" => $id,
            "mobile_id" => $mobile->device[$id]["mobile_id"],
            "mobile_os" => $mobile->device[$id]["mobile_os"],
        );
        
        return $this->reduce($result, $this->conf("//hidden_fields"));
    }
    
    public function deleteInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            )
        );
    }
    
    public function deleteAction($params = array())
    {
        $user = $this->getUser();
        $mobile = get_user_meta($user->ID, "wpjb_mobile_device", true);
        
        $id = $params["id"][0];
        
        if(!isset($mobile->device[$id])) {
            $ex = sprintf("Device with index [%s] does not exist.", $id);
            throw new Exception($ex);
        }
        
        unset($mobile->device[$id]);
        
        update_user_meta($user->ID, "wpjb_mobile_device", $mobile);
        
        return array("status"=>200, "deleted"=>$id);
    }
}

?>
