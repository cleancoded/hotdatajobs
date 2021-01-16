<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Jobs
 *
 * @author Grzegorz
 */
class Wpjb_Module_Api_Jobs extends Wpjb_Controller_Api
{

    
    public function init()
    {
        return array(
            "allowed_user_actions" => array(
                "public" => array("get"),
                "user" => array("get"),
                "employer" => array("get"),
                "admin" => array("get", "post", "put", "delete")
            )
        );
    }
    
    public function getInit()
    {
        return array(
            "disallowed_params" => array(
                "public" => array("filter", "count_only", "ids_only", "hide_filled"),
                "user" => array("filter"),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(
                    "default" => array("job_modified_at", "job_expires_at", "company_email", "applications", "read", "admin_url"), 
                    "meta" => array("job_source")
                ),
                "user" => array("filter"),
                "employer" => array(),
                "admin" => array()
            )
        );
    }
    
    public function getAction($params) 
    {
        $result = wpjb_find_jobs($params);
        
        $response = array("status"=>200, "total"=>$result->total, "data"=>array());
        
        foreach($result->job as $row) {
            
            $response["data"][] = $this->reduce($row->toArray(), $this->conf("//hidden_fields"));
        }
        
        return $response;
    }
    
    public function postAction($object)
    {
        $id = Wpjb_Model_Job::import_new($object);
        
        $object = new Wpjb_Model_Job($id);
        
        return $object->toArray();
    }
    
    public function putAction()
    {
        
    }
    
    public function deleteAction()
    {
        
    }
}

?>
