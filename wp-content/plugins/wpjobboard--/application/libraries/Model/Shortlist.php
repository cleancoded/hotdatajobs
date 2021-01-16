<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Shortlist
 *
 * @author Grzegorz
 */
class Wpjb_Model_Shortlist extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_shortlist";

    protected function _init()
    {
        $this->_reference["job"] = array(
            "localId" => "object_id",
            "foreign" => "Wpjb_Model_Tagged",
            "foreignId" => "id",
            "type" => "ONE_TO_ONE"
        );
    }
    
    public function getObject()
    {
        switch($this->object) {
            case "job"          : $object = new Wpjb_Model_Job($this->object_id); break;
            case "resume"       : $object = new Wpjb_Model_Resume($this->object_id); break;
            case "company"      : $object = new Wpjb_Model_Company($this->object_id); break;
            case "application"  : $object = new Wpjb_Model_Application($this->object_id); break;
        }
        
        if($object->exists()) {
            return $object;
        } else {
            return null;
        }
    }
    
    public static function displaySingleJob(Wpjb_Model_Job $job) 
    {
        $url = wpjb_api_url("action/bookmark", array("object"=>"job", "object_id"=>$job->id, "redirect_to"=>$job->url(), "do"=>"post"));
        
        $html = new Daq_Helper_Html("a", array(
            "href" => $url,
            "class" => "wpjb-button",
            "title" => __("Bookmark this job", "wpjobboard")
        ), __("Bookmark", "wpjobboard"));
        
        echo $html->render();
        
    }
}

?>
