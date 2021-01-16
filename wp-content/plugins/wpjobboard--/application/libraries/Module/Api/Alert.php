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
class Wpjb_Module_Api_Alert extends Wpjb_Controller_Api 
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
                "public" => array("default"=>array("meta")),
                "user" => array("default"=>array("meta")),
                "employer" => array("default"=>array("meta")),
                "admin" => array("default"=>array("meta"))
            )
        );
    }
    
    public function getAction($params = array())
    {
        $user = $this->getUser();
        $query = new Daq_Db_Query;
        $query->from("Wpjb_Model_Alert t");
        $query->where("user_id = ?", $user->ID);
        
        $list = $query->execute();
        
        $response = array("status"=>200, "total"=>0, "data"=>array());
        
        foreach($list as $row) {
            
            $arr = $row->toArray();
            $arr["params"] = unserialize($arr["params"]);
            
            $response["data"][] = $this->reduce($arr, $this->conf("//hidden_fields"));
            $response["total"]++;
            
            unset($arr);
            unset($row);
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
                "public" => array("default"=>array("meta")),
                "user" => array("default"=>array("meta")),
                "employer" => array("default"=>array("meta")),
                "admin" => array("default"=>array("meta"))
            )
        );
    }
    
    public function postAction($params = array())
    {
        $user = $this->getUser();
        
        $alert = new Wpjb_Model_Alert;
        $alert->user_id = $user->ID;
        $alert->keyword = $params["params"]["keyword"];
        $alert->email = $params["email"];
        $alert->created_at = date("Y-m-d H:i:s");
        $alert->last_run = "0000-00-00 00:00:00";
        $alert->frequency = $params["frequency"];
        $alert->params = serialize($params["params"]);
        $alert->save();
        
        return $this->reduce($alert->toArray(), $this->conf("//hidden_fields"));
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
                "public" => array("default"=>array("meta")),
                "user" => array("default"=>array("meta")),
                "employer" => array("default"=>array("meta")),
                "admin" => array("default"=>array("meta"))
            )
        );
    }
    
    
    public function putAction($params = array())
    {
        $alert = new Wpjb_Model_Alert($params["id"]);
        
        if($alert->exists()) {
            throw new Exception(sprintf("Object with ID %d does not exist.", $params["id"]));
        }
        
        $alert->keyword = $params["params"]["keyword"];
        $alert->email = $params["email"];
        $alert->frequency = $params["frequency"];
        $alert->params = serialize($params["params"]);
        $alert->save();
        
        return $this->reduce($alert->toArray(), $this->conf("//hidden_fields"));
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
                "public" => array("default"=>array("meta")),
                "user" => array("default"=>array("meta")),
                "employer" => array("default"=>array("meta")),
                "admin" => array("default"=>array("meta"))
            )
        );
    }
    
    public function deleteAction($params = array())
    {
        $id = $params["id"][0];
        
        $alert = new Wpjb_Model_Alert($id);

        if(!$alert->exists()) {
            throw new Exception("Alert with ID $id does not exist.");
        }
        
        if($this->getAccess() != "admin" && $this->getUser()->ID != $alert->user_id) {
            throw new Exception("Cannot delete, your do not own bookmark with ID $id.");
        }

        $alert->delete();
        
        return array("status"=>200, "deleted"=>$id);
    }
}

?>
