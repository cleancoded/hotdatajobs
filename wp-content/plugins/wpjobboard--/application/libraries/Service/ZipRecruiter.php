<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ZipRecruiter
 *
 * @author Grzegorz
 */

class Wpjb_Service_ZipRecruiter
{
    protected static $_backfilled = 0;
    
    public static function jobTypes()
    {
        return array(
            "fulltime" => __("Full-Time", "wpjobboard"), 
            "parttime" => __("Part-Time", "wpjobboard"), 
            "contract" => __("Contract", "wpjobboard"), 
            "internship" => __("Internship", "wpjobboard"), 
            "temporary" => __("Temporary", "wpjobboard")
        );
    }
    
    public static function countries() 
    {
        return array(
            "us" => "United States",
            "ar" => "Argentina",
            "au" => "Australia",
            "at" => "Austria",
            "bh" => "Bahrain",
            "be" => "Belgium",
            "br" => "Brazil",
            "ca" => "Canada",
            "cl" => "Chile",
            "cn" => "China",
            "co" => "Colombia",
            "cz" => "Czech Republic",
            "dk" => "Denmark",
            "fi" => "Finland",
            "fr" => "France",
            "de" => "Germany",
            "gr" => "Greece",
            "hk" => "Hong Kong",
            "hu" => "Hungary",
            "in" => "India",
            "id" => "Indonesia",
            "ie" => "Ireland",
            "il" => "Israel",
            "it" => "Italy",
            "jp" => "Japan",
            "kr" => "Korea",
            "kw" => "Kuwait",
            "lu" => "Luxembourg",
            "my" => "Malaysia",
            "mx" => "Mexico",
            "nl" => "Netherlands",
            "nz" => "New Zealand",
            "ng" => "Nigeria",
            "no" => "Norway",
            "om" => "Oman",
            "pk" => "Pakistan",
            "pe" => "Peru",
            "ph" => "Philippines",
            "pl" => "Poland",
            "pt" => "Portugal",
            "qa" => "Qatar",
            "ro" => "Romania",
            "ru" => "Russia",
            "sa" => "Saudi Arabia",
            "sg" => "Singapore",
            "za" => "South Africa",
            "es" => "Spain",
            "se" => "Sweden",
            "ch" => "Switzerland",
            "tw" => "Taiwan",
            "tr" => "Turkey",
            "ae" => "United Arab Emirates",
            "gb" => "United Kingdom",
            "ve" => "Venezuela",
        );
    }
    
    public static function connect()
    {
        add_filter("wpjb_filter_jobs", array("Wpjb_Service_ZipRecruiter", "backfill"), 10, 3);
        add_filter("wpjb_scheme", array("Wpjb_Service_ZipRecruiter", "scheme"), 10, 2);
        
        $options = wpjb_conf("ziprecruiter_backfill", array());
        
        if(in_array("attribution", $options)) {
            add_filter("the_content", array("Wpjb_Service_ZipRecruiter", "attribution"), 100000);
        }
        
        //if(in_array("click-tracking", $options)) {
        //    add_action("wp_enqueue_scripts", array("Wpjb_Service_ZipRecruiter", "tracking"), 100000);
        //}
        
    }
    
    public static function attribution($content)
    {
        
        if(self::$_backfilled == 0) {
            return $content;
        }
        
        $protocol = is_ssl() ? "https" : "http";
        $attribution = '
            <a href="https://www.ziprecruiter.co.uk/jobs" id="jobs_widget_link">
                <span>Job Search by</span> <span id="zr_logo_container">
                    <img id="zr_logo" src="https://d6dyoorq84mou.cloudfront.net/uploads/tenant/logo/5309/ZipRecruiter_logo_dark_web__1_.png" alt="ZipRecruiter" width="120" />
                </span>
            </a>
        ';

        return $content . $attribution;
    }
    

    /**
     * Updates the jobs list or search with ZipRecruiter results
     * 
     * This function is being called by wpjb_filter_jobs filter, which is
     * applied in self::connect() method.
     * 
     * @see wpjb_filter_jobs filter
     * @see self::connect()
     * 
     * @param stdClass  $result     wpjb_jobs_search() result
     * @param array     $atts       Search parameters passed to wpjb_jobs_search()
     * @param string    $context    Context in which this method was called either "list" or "search"
     * @return stdClass             Updated wpjb_jobs_search() result
     */
    public static function backfill($result, $atts = array(), $context = null)
    {   
        if( isset( $atts['disable_backfill'] ) && $atts['disable_backfill'] == 1) {
            return $result;
        }
        
        if( isset( $atts['backfill'] ) && strtolow( $atts['backfill'] ) != "ziprecruiter") {
            return $result;
        }

        $mode = wpjb_conf("urls_mode", array(1));
        $instance = Wpjb_Project::getInstance();
        $backfill = wpjb_conf("ziprecruiter_backfill", array());

        if(!in_array("enabled-$context", $backfill)) {
            return $result;
        }
  
        if(wpjb_conf("ziprecruiter_backfill_when") < $result->count) {
            return $result;
        }
        
        $limit = $result->perPage - $result->count;
        
        if($limit < 1) {
            //$limit = 0;
            return $result;
        }
        
        $request = Daq_Request::getInstance();
        
        $country = $request->getParam("country", wpjb_conf("ziprecruiter_default_co"));
        $q = $request->getParam("query", wpjb_conf("ziprecruiter_default_q"));
        $l = $request->getParam("location", wpjb_conf("ziprecruiter_default_l"));

        if($request->getParam("country") === "") {
            $country = wpjb_conf("ziprecruiter_default_co");
        }
        if($request->getParam("query") === "") {
            $q = wpjb_conf("ziprecruiter_default_q");
        }
        if($request->getParam("location") === "") {
            $l = wpjb_conf("ziprecruiter_default_l");
        }
        if($request->getParam("radius") === "") {
            $rm = wpjb_conf("ziprecruiter_default_r");
        }
        if($request->getParam("days_ago") === "") {
            $da = wpjb_conf("ziprecruiter_default_da");
        }
        if($request->getParam("salary") === "") {
            $s = wpjb_conf("ziprecruiter_default_s");
        }
        
        $query = apply_filters("wpjb_ziprecruiter_query", array(
            "api_key"           => wpjb_conf("ziprecruiter_api_key"),
            "search"            => $q,
            "location"          => $l,
            "radius_miles"      => $rm,
            "days_ago"          => $da,
            "refine_by_salary"  => $s,
            "page"              => 1,
            "jobs_per_page"     => $limit,
        ));
        
        
        $url = 'https://api.ziprecruiter.com/jobs/v1?' . http_build_query($query);
        
        $response = wp_remote_get($url);

        if($response instanceof WP_Error) {
            return $result;
        }
        
        $data = json_decode($response["body"]);
        //var_dump($data);
        
        if($data->num_paginable_jobs < 1) {
            return $result;
        }
        
        foreach($data->jobs as $item) {
            
            $country = Wpjb_List_Country::getByAlpha2((string)$item->country);
            $date = date("Y-m-d H:i:s", wpjb_time((string)$item->posted_time));
            
            $tag = new stdClass();
            $tag->type = array();
            $tag->category = array($item->industry_name);
            
            $job = new Wpjb_Service_ZipRecruiterJob(null);
            $job->setZipRecruiterData($item);
            $job->setZipRecruiterTags($tag);
            $job->company_name = (string)$item->hiring_company->name;
            $job->company_url = (string)$item->hiring_company->url;
            $job->company_email = "";
            $job->job_title = (string)$item->name;
            $job->job_description = (string)$item->snippet;
            $job->job_country = $country["code"];
            $job->job_state = (string)$item->state;
            $job->job_zip_code = "";
            $job->job_city = (string)$item->city;
            $job->job_created_at = date("Y-m-d", wpjb_time($date));
            $job->job_expires_at = date("Y-m-d", wpjb_time("$date +30 day"));
            $job->is_active = 1;
            $job->is_approved = 1;

            $result->job[] = apply_filters("wpjb_ziprecruiter_job", $job, $query);
            $result->count++;
            
            self::$_backfilled++;
        }
        
        $result->perPage;
        $result->count;
        if( isset( $data->totalResults ) ) {
            $result->total += $data->totalResults;
        }
        $result->pages = ceil($result->total/$result->perPage);
        
        return $result;
    }
    
    public static function scheme($scheme, $object)
    {
        if($object instanceof Wpjb_Service_ZipRecruiterJob) {
        
            if(!isset($scheme["field"]) || !is_array($scheme["field"])) {
                $scheme["field"] = array();
            }
                        
            if(!isset($scheme["field"]["job_title"]) || !is_array($scheme["field"]["job_title"])) {
                $scheme["field"]["job_title"] = array();
            }
        
            $scheme["field"]["job_title"]["render_callback"] = array("Wpjb_Service_ZipRecruiter", "link");
        } 
        
        return $scheme;
    }
    
    public static function link($object) 
    {
        $data = $object->getZipRecruiterData();
        $options = wpjb_conf("ziprecruiter_backfill", array());
        $onmousedown = "";
        
        if(in_array("click-tracking", $options)) {
            $onmousedown = ' onmousedown="' . esc_attr($data->onmousedown) . '"';
        }
        
        ?>
            <!--span class="wpjb-line-major"-->
            <a class="wpjb-title" target="_blank" href="<?php esc_attr_e($data->url) ?>" class="wpjb-ziprecruiter-link" rel="nofollow" <?php echo $onmousedown ?>>
                <?php esc_html_e($object->job_title) ?>
            </a>
            <!--/span--> 
        <?php

    }
    
    public function prepare($item, $import)
    {
        $country = Wpjb_List_Country::getByAlpha2((string)$item->country);
        $date = date("Y-m-d H:i:s", wpjb_time((string)$item->date));

        $result = new stdClass();
        $result->company_name = (string)$item->company;
        $result->company_url = (string)$item->url;
        $result->company_email = "";
        $result->job_title = (string)$item->jobtitle;
        $result->job_description = (string)$item->snippet;
        $result->job_country = $country["code"];
        $result->job_state = (string)$item->state;
        $result->job_zip_code = "";
        $result->job_city = (string)$item->city;
        $result->job_created_at = date("Y-m-d", wpjb_time($date));
        $result->job_expires_at = date("Y-m-d", wpjb_time("$date +30 day"));
        $result->is_active = 1;
        $result->is_approved = 1;
        
        $t1 = new stdClass();
        $t1->type = "type";
        $t1->title = "Full Time";
        $t1->slug = "full-time";
        
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
        $m2->value = $import->engine."-".(string)$item->jobkey;
        
        $result->metas = new stdClass();
        $result->metas->meta = array($m1, $m2);
        
        return apply_filters("wpjb_ziprecruiter_item", $result, $item, $import);
    }
    
    public function find($param = array()) 
    {
        $result = new stdClass();
        $result->item = array();
        
        $posted = $param["posted"];
        //$country = $param["country"];
        $location = $param["location"];
        $keyword = $param["keyword"];
        $max = $param["add_max"];

        $query = apply_filters("wpjb_ziprecruiter_query", array(
            "api_key"           => wpjb_conf("ziprecruiter_api_key"),
            "search"            => $keyword,
            "location"          => $location,
            //"radius_miles"      => $rm,
            "days_ago"          => $posted,
            //"refine_by_salary"  => $s,
            "page"              => 1,
            "jobs_per_page"     => $max,
        ));
        
        
        $url = 'https://api.ziprecruiter.com/jobs/v1?' . http_build_query($query);
        
        $response = wp_remote_get($url);
        
        if($response instanceof WP_Error) {
            return $result;
        }
        
        $data = json_decode($response["body"]);
        
        foreach($data->job as $r) {
            $r = (object)$r;
            $r->external_id = (string)$r->id;
            $result->item[] = $r;
        }

        return $result;
    
    }
}

class Wpjb_Service_ZipRecruiterJob extends Wpjb_Model_Job {
    
    protected $_ziprecruiter = null;
    
    protected $_ziprecruiterLogo = null;

    public function setZipRecruiterData($item) {
        $this->_ziprecruiter = $item;
    }
    
    public function getZipRecruiterData() {
        return $this->_ziprecruiter;
    }
    
    public function setZipRecruiterTags($tags) {
        $this->_tag = $tags;
    }
    
    public function setZipRecruiterLogoUrl($logo) {
        $this->_ziprecruiterLogo = $logo;
    }
    
    public function getLogoUrl($resize = null) {
        // maybe?? https://pbs.twimg.com/profile_images/465901126684913664/sTJZxF5G_400x400.jpeg
        return $this->_ziprecruiterLogo;
    }
    
    public function save() {
        throw new Exception("ZipRecruiter Job cannot be saved.");
    }
    
    public function delete() {
        throw new Exception("ZipRecruiter Job cannot be deleted.");
    }
}

?>
