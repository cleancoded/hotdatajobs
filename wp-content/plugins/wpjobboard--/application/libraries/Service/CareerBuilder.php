<?php
/**
 * Description of CarrerBuilder
 *
 * @author greg
 * @package 
 */

class Wpjb_Service_CareerBuilder
{
    protected $_url = "http://api.careerbuilder.com/v1/";

    protected $_key = null;

    public function __construct()
    {
        $this->_key = wpjb_conf("api_cb_key");
    }
    
    public function prepare($item, $import)
    {
        $query = array(
            "DeveloperKey" => $this->_key,
            "DID" => (string)$item->DID
        );

        $url = $this->_url."job?".http_build_query($query);
        $content = wp_remote_get($url);
        $job = simplexml_load_string($content["body"]);
        
        $country = Wpjb_List_Country::getByAlpha2((string)$job->Job->LocationCountry);
        $sTime = strtotime(date("Y-m-d H:i:s"));
        $eTime = strtotime($job->Job->EndDate);
        
        $result = new stdClass();
        $result->company_name = (string)$job->Job->Company;
        $result->company_url = (string)$job->Job->ContactInfoEmailURL;
        $result->company_email = "";
        $result->job_title = (string)$job->Job->JobTitle;
        $result->job_description = html_entity_decode((string)$job->Job->JobDescription, ENT_NOQUOTES, "UTF-8");;
        $result->job_country = $country['code'];
        $result->job_state = (string)$job->Job->LocationState;
        $result->job_zip_code = (string)$job->Job->LocationPostalCode;
        $result->job_city = (string)$job->Job->LocationCity;
        $result->job_created_at = date("Y-m-d", $sTime);
        $result->job_expires_at = date("Y-m-d", $eTime);
        $result->is_active = 1;
        $result->is_approved = 1;
        
        $t1 = new stdClass();
        $t1->type = "type";
        $t1->title = (string)$job->Job->EmploymentType;
        $t1->slug = preg_replace("([^A-z0-9\-]+)", "", sanitize_title($t1->title));
        
        $t2 = new stdClass();
        $t2->type = "category";
        $t2->id = $import->category_id;
        
        $result->tags = new stdClass();
        $result->tags->tag = array($t1, $t2);
        
        $m1 = new stdClass();
        $m1->name = "job_description_format";
        $m1->value = "html";
        
        $m2 = new stdClass();
        $m2->name = "job_source";
        $m2->value = $import->engine."-".(string)$job->Job->DID;
        
        $result->metas = new stdClass();
        $result->metas->meta = array($m1, $m2);
        
        return $result;
    }
    
    public function find($param = array()) 
    {
        $result = new stdClass();
        $result->item = array();
        
        $posted = $param["posted"];
        $country = $param["country"];
        $location = $param["location"];
        $keyword = $param["keyword"];
        $max = $param["add_max"];

        $query = array(
            "DeveloperKey" => $this->_key,
            "Keywords" => $keyword,
            "CountryCode" => $country,
            "PostedWithin" => $posted,
            "PageNumber" => 1,
            "PerPage" => $max
        );

        if(!empty($location)) {
            $query["Location"] = $location;
        }
        
        $url = $this->_url."jobsearch?".http_build_query($query);
        $content = wp_remote_get($url);
        $xml = simplexml_load_string($content["body"]);

        if($xml->Errors->Error) {
            throw new Exception((string)$xml->Errors->Error);
        }
        if($xml->Errors[0]->Error) {
            throw new Exception((string)$xml->Errors[0]->Error);
        }
        if($xml->TotalCount == 0) {
            throw new Exception("No jobs found");
        }

        foreach($xml->Results->JobSearchResult as $r) {
            $r = (object)$r;
            $r->external_id = (string)$r->DID;
            $result->item[] = $r;
        }
        
        return $result;
    
    }
    

}

?>