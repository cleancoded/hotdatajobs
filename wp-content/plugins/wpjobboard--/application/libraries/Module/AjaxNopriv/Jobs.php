<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Json
 *
 * @author greg
 */
class Wpjb_Module_AjaxNopriv_Jobs
{
    protected $_format = "json";
    
    protected static function _push($object)
    {
        //header("Content-type: application/json; charset=utf-8");

        echo json_encode($object);
        die(PHP_EOL);
    }
    
    protected static function _modify(Wpjb_Model_Job $job)
    {
        $public = array("id", "company_name", "company_website", "job_type",
            "job_category", "job_country", "job_state", "job_zip_code",
            "job_location", "job_limit_to_country", "job_title", "job_slug",
            "job_created_at", "job_expires_at", "job_description",
            "is_active", "is_filled", "is_featured", "stat_view", "stat_unique",
            "stat_apply"
        );

        $publish = new stdClass;
        foreach($public as $k) {
            $publish->$k = $job->$k; 
        }
        
        $arr = $job->toArray();
        foreach($arr as $k => $a) {
            if(substr($k, 0, 6) == "field_") {
                $publish->$k = $a;
            }
        }
             
        $publish->url = wpjb_link_to("job", $job);
        $publish->image = $job->getLogoUrl();
        $publish->location = $job->locationToString();
        $publish->category = null;
        $publish->type = null;
        $publish->is_new = $job->isNew();
        $publish->is_free = $job->isFree();
        
        if(isset($job->getTag()->category[0])) {
            $publish->category = $job->getTag()->category[0]->toArray();
        }
        
        if(isset($job->getTag()->type[0])) {
            $publish->type = $job->getTag()->type[0]->toArray();
        }
         
        return $publish;
    }
    
    public static function searchAction()
    {
        $request = Daq_Request::getInstance();
        
        $param = array(
            "query" => $request->post("query"),
            "category" => $request->post("category"),
            "type" => $request->post("type"),
            "page" => $request->post("page", 1),
            "count" => $request->post("count", wpjb_conf("front_jobs_per_page", 20)),
            "country" => $request->post("country"),
            "state" => $request->post("state"),
            "city" => $request->post("city"),
            "posted" => $request->post("posted"),
            "location" => $request->post("location"),
            "radius" => $request->post("radius"),
            "is_featured" => $request->post("is_featured"),
            "employer_id" => $request->post("employer_id"),
            "meta" => $request->post("meta", array()),
            "sort" => $request->post("sort"),
            "order" => $request->post("order")
        );
              
        if($request->post("sort_order")) {
            $param["sort_order"] = $request->post("sort_order");
        }
        
        $param = apply_filters("wpjb_filter_jobs_params", $param, "list");
        
        $result = apply_filters("wpjb_filter_jobs", Wpjb_Model_JobSearch::search($param), $param, "list");
        $list = $result->job;
        $result->job = array();
        $result->html = "";
        $shortcode = new Wpjb_Shortcode_Dynamic();
        foreach($list as $job) {
            
            if( ! ( $job instanceof  Wpjb_Service_ZipRecruiterJob ) ) {
                $result->job[] = self::_modify($job);
            } 
            
            $shortcode->view->job = $job;
            $result->html .= $shortcode->render("job-board", "index-item");
        }
        
        self::_push($result);
    }
    
    public function detailsAction()
    {
        $request = Daq_Request::getInstance();
        $job = new Wpjb_Model_Job($request->post("id"));
        
        if(!$job->is_active || !$job->is_approved || time()>strtotime($job->job_expires_at)) {
            exit(0);
        }
        
        $publish = self::_modify($job);
        
        self::_push($publish);
    }
    
    public function categoriesAction()
    {
        throw new Exception("For future use.");
    }
    
    public function typesAction()
    {
        throw new Exception("For future use.");
    }
    
    public function applyAction()
    {
        throw new Exception("For future use.");
    }
}

?>
