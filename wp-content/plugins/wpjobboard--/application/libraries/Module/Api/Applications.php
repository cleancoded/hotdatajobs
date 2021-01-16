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
class Wpjb_Module_Api_Applications extends Wpjb_Controller_Api 
{

    public function init()
    {
        return array(
            "allowed_user_actions" => array(
                "public" => array(),
                "user" => array(),
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
        $query->from("Wpjb_Model_Application t");
        
        if($this->getAccess() == "employer") {
            $employer = new Daq_Db_Query;
            $employer->from("Wpjb_Model_Company t");
            $employer->where("user_id = ?", $this->_user->ID);
            $employer->limit(1);
            $eList = $employer->execute();
            
            $company = $eList[0];
            
            $query->join("t.job t2");
            $query->where("t2.employer_id = ?", $company->id);
        }
        
        if(isset($params["job_id"]) && !empty($params["job_id"])) {
            $query->where("t.job_id = ?", $params["job_id"]);
        }
        
        if(isset($params["id"]) && !empty($params["id"])) {
            $query->where("t.id IN(?)", (array)$params["id"]);
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
                "user" => array(),
                "employer" => array(),
                "admin" => array()
            ),
            "hidden_fields" => array(
                "public" => array(),
                "user" => array(),
                "employer" => array("job_id", "user_id", "applied_at"),
                "admin" => array()
            )
        );
    }
    
    public function postAction($params = array())
    {

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
                "employer" => array("job_id", "user_id", "applied_at"),
                "admin" => array()
            )
        );
    }
    
    
    public function putAction($params = array())
    {
        $application = new Wpjb_Model_Application($params["id"][0]);
        
        if(!$application->exists()) {
            throw new Exception(sprintf("Object with ID %d does not exist.", $params["id"][0]));
        }
        
        $job = new Wpjb_Model_Job($application->job_id);
        $company = new Wpjb_Model_Company($job->employer_id);
        
        if($this->getAccess()=="employer" && $company->user_id!=$this->getUser()->ID) {
            throw new Exception(sprintf("You do not own Application with ID %d.", $params["id"][0]));
        }
        
        foreach($params as $key => $value) {
            if(in_array($key, $application->getFieldNames()) && $key!="id") {
                $application->$key = $value;
            }
        }

        $application->save();
        
        return $this->reduce($application->toArray(), $this->conf("//hidden_fields"));
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
        $application = new Wpjb_Model_Application($params["id"][0]);
        
        if(!$application->exists()) {
            throw new Exception(sprintf("Object with ID %d does not exist.", $params["id"][0]));
        }
        
        $job = new Wpjb_Model_Job($application->job_id);
        $company = new Wpjb_Model_Company($job->employer_id);
        
        if($this->getAccess()=="employer" && $company->user_id!=$this->_user->ID) {
            throw new Exception(sprintf("You do not own Application with ID %d.", $params["id"][0]));
        }

        $application->delete();
        
        return array("status"=>200, "deleted"=>$id);
    }
}

?>
