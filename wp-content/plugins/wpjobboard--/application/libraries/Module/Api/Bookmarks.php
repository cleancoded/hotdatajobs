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
class Wpjb_Module_Api_Bookmarks extends Wpjb_Controller_Api 
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
                "user" => array("shortlisted_at"),
                "employer" => array("shortlisted_at"),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array("meta"),
                "user" => array("meta"),
                "employer" => array("meta"),
                "admin" => array("meta")
            )
        );
    }
    
    public function getAction($params = array())
    {
        $query = new Daq_Db_Query;
        $query->from("Wpjb_Model_Shortlist t");
        
        if($this->getMethod() != "admin") {
            $query->where("user_id = ?", $this->_user->ID);
        }
        
        $list = $query->execute();
        
        $response = array("status"=>200, "total"=>0, "data"=>array());
        
        foreach($list as $row) {
            
            $response["data"][] = $this->reduce($row->toArray(), $this->conf("//hidden_fields"));
            $response["total"]++;
        }
        
        return $response;
        
    }
    
    public function postInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array("shortlisted_at", "user_id"),
                "employer" => array("shortlisted_at", "user_id"),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array("meta"),
                "user" => array("meta"),
                "employer" => array("meta"),
                "admin" => array("meta")
            )
        );
    }
    
    public function postAction($params = array())
    {
        if(!isset($params["shortlisted_at"])) {
            $params["shortlisted_at"] = date("Y-m-d");
        }
        
        $query = new Daq_Db_Query;
        $query->from("Wpjb_Model_Shortlist t");
        $query->where("user_id = ?", $params["user_id"]);
        $query->where("object = ?", $params["object"]);
        $query->where("object_id = ?", $params["object_id"]);
        $query->limit(1);
        
        $list = $query->execute();
        
        if(!empty($list)) {
            throw new Exception("You already bookmarked '{$params['object']}' with ID {$params['object_id']}");
        }
        
        $shortlist = new Wpjb_Model_Shortlist();
        $shortlist->user_id = $params["user_id"];
        $shortlist->object = $params["object"];
        $shortlist->object_id = $params["object_id"];
        $shortlist->shortlisted_at = $params["shortlisted_at"];
        $shortlist->save();
        
        return $this->reduce($shortlist->toArray(), $this->conf("//hidden_fields"));
    }
    
    public function putInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array(),
                "user" => array("shortlisted_at", "user_id"),
                "employer" => array("shortlisted_at", "user_id"),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array("meta"),
                "user" => array("meta"),
                "employer" => array("meta"),
                "admin" => array("meta")
            )
        );
    }
    
    
    public function putAction($params = array())
    {
        $shortlist = new Wpjb_Model_Shortlist($params["id"]);
        
        if($shortlist->exists()) {
            throw new Exception("Object with ID ".$params["id"]." does not exist.");
        }
        
        foreach($params as $key => $value) {
            $shortlist->$key = $value;
        }

        $shortlist->save();
        
        return $this->reduce($shortlist->toArray(), $this->conf("//hidden_fields"));
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
        $id = $params["id"][0];
        
        $shotlist = new Wpjb_Model_Shortlist($id);

        if(!$shotlist->exists()) {
            throw new Exception("Bookmark with ID $id does not exist.");
        }
        
        if($this->getAccess() != "admin" && $this->getUser()->ID != $shotlist->user_id) {
            throw new Exception("Cannot delete, your do not own bookmark with ID $id.");
        }

        $shotlist->delete();
        
        return array("status"=>200, "deleted"=>$id);
    }
}

?>
