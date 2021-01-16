<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Indeed
 *
 * @author Grzegorz
 */

class Wpjb_Service_Indeed 
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
        add_filter("wpjb_filter_jobs", array("Wpjb_Service_Indeed", "backfill"), 10, 3);
        add_filter("wpjb_scheme", array("Wpjb_Service_Indeed", "scheme"), 10, 2);
        
        $options = wpjb_conf("indeed_backfill", array());
        
        if(in_array("attribution", $options)) {
            add_filter("the_content", array("Wpjb_Service_Indeed", "attribution"), 100000);
        }
        
        if(in_array("click-tracking", $options)) {
            add_action("wp_enqueue_scripts", array("Wpjb_Service_Indeed", "tracking"), 100000);
        }
        
    }
    
    public static function attribution($content)
    {
        
        if(self::$_backfilled == 0) {
            return $content;
        }
        
        $protocol = is_ssl() ? "https" : "http";
        $attribution = '
            <span id=indeed_at>
                <a href="http://www.indeed.com/">Jobs</a> by 
                <a href="http://www.indeed.com/" title="Job Search"><img src="'.$protocol.'://www.indeed.com/p/jobsearch.gif" style="border: 0; vertical-align: middle;" alt="Indeed job search"></a>
            </span>
        ';

        return $content . $attribution;
    }
    
    public static function conversion() 
    {
        if( !is_singular() || get_query_var("applied") === '') {
            return;
        }
        
        $jobId = absint(get_query_var("applied"));
        
        $id = wpjb_conf("indeed_conversion_tracking_id");
        $label = apply_filters("indeed_conversion_tracking_label", wpjb_conf("indeed_conversion_tracking_label"), $jobId);
        ?>

        <!-- Begin INDEED conversion code -->
        <script type="text/javascript">
        /* <![CDATA[ */
        var indeed_conversion_id = '<?php echo esc_html($id) ?>';
        var indeed_conversion_label = '<?php echo esc_html($label) ?>';
        /* ]]> */
        </script>
        <script type="text/javascript" src="//conv.indeed.com/pagead/conversion.js"></script>
        <noscript>
            <img height="1" width="1" border="0" src="//conv.indeed.com/pagead/conv/<?php echo esc_html($id) ?>/?script=0">
        </noscript>
        <!-- End INDEED conversion code -->

        <?php
    }
    
    public static function tracking($content)
    {
        $protocol = is_ssl() ? "https" : "http";
        wp_enqueue_script( 'wpjb-indeed-tracking', $protocol."://gdc.indeed.com/ads/apiresults.js", array(), null);
    }


    /**
     * Updates the jobs list or search with Indeed results
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
        
        if( isset( $atts['backfill'] ) && strtolow( $atts['backfill'] ) != "indeed") {
            return $result;
        }
        
        $mode = wpjb_conf("urls_mode", array(1));
        $instance = Wpjb_Project::getInstance();
        $backfill = wpjb_conf("indeed_backfill", array());

        if(!in_array("enabled-$context", $backfill)) {
            return $result;
        }
        
        if(wpjb_conf("indeed_backfill_when") < $result->count) {
            return $result;
        }
        

        $limit = $result->perPage - $result->count;
        $start = $result->perPage * $result->page - $result->total - $limit;
        
        if($limit < 1) {
            $limit = 0;
        }
        
        if($start < 0) {
            $start = 0;
        }
        
        $request = Daq_Request::getInstance();
        
        $country = $request->getParam("country", wpjb_conf("indeed_default_co"));
        $q = $request->getParam("query", wpjb_conf("indeed_default_q"));
        $l = $request->getParam("location", wpjb_conf("indeed_default_l"));

        if($request->getParam("country") === "") {
            $country = wpjb_conf("indeed_default_co");
        }
        if($request->getParam("query") === "") {
            $q = wpjb_conf("indeed_default_q");
        }
        if($request->getParam("location") === "") {
            $l = wpjb_conf("indeed_default_l");
        }
        
        
        $query = apply_filters("wpjb_indeed_query", array(
            "publisher" => wpjb_conf("indeed_publisher"),
            "co" => $country,
            "q" => $q,
            "l" => $l,
            "st" => wpjb_conf("indeed_default_st"),
            "start" => $start,
            "limit" => $limit,
            "sort" => wpjb_conf("indeed_default_sort"),
            "v" => "2",
            "userip" => $_SERVER["REMOTE_ADDR"],
            "useragent" => $_SERVER["HTTP_USER_AGENT"],
            "format" => "json"
        ));
        
        
        $url = "http://api.indeed.com/ads/apisearch?" . http_build_query($query);
        
        $response = wp_remote_get($url);
        
        if($response instanceof WP_Error) {
            return $result;
        }
        
        $data = json_decode($response["body"]);
        //var_dump($data);
        
        if($data->totalResults < 1) {
            return $result;
        }
        
        foreach($data->results as $item) {
            
            $country = Wpjb_List_Country::getByAlpha2((string)$item->country);
            $date = date("Y-m-d H:i:s", wpjb_time((string)$item->date));
            
            $tag = new stdClass();
            $tag->type = array();
            $tag->category = array();
            
            $job = new Wpjb_Service_IndeedJob(null);
            $job->setIndeedData($item);
            $job->setIndeedTags($tag);
            $job->company_name = (string)$item->company;
            $job->company_url = (string)$item->url;
            $job->company_email = "";
            $job->job_title = (string)$item->jobtitle;
            $job->job_description = (string)$item->snippet;
            $job->job_country = $country["code"];
            $job->job_state = (string)$item->state;
            $job->job_zip_code = "";
            $job->job_city = (string)$item->city;
            $job->job_created_at = date("Y-m-d", wpjb_time($date));
            $job->job_expires_at = date("Y-m-d", wpjb_time("$date +30 day"));
            $job->is_active = 1;
            $job->is_approved = 1;

            $result->job[] = apply_filters("wpjb_indeed_job", $job, $query);
            $result->count++;
            
            self::$_backfilled++;
        }
        
        $result->perPage;
        $result->count;
        $result->total += $data->totalResults;
        $result->pages = ceil($result->total/$result->perPage);
        
        return $result;
    }
    
    public static function scheme($scheme, $object)
    {
        if($object instanceof Wpjb_Service_IndeedJob) {
        
            if(!isset($scheme["field"]) || !is_array($scheme["field"])) {
                $scheme["field"] = array();
            }
                        
            if(!isset($scheme["field"]["job_title"]) || !is_array($scheme["field"]["job_title"])) {
                $scheme["field"]["job_title"] = array();
            }
        
            $scheme["field"]["job_title"]["render_callback"] = array("Wpjb_Service_Indeed", "link");
        } 
        
        return $scheme;
    }
    
    public static function link($object) 
    {
        $data = $object->getIndeedData();
        $options = wpjb_conf("indeed_backfill", array());
        $onmousedown = "";
        
        if(in_array("click-tracking", $options)) {
            $onmousedown = ' onmousedown="' . esc_attr($data->onmousedown) . '"';
        }
        
        ?>
            <span class="wpjb-line-major">
                <a href="<?php esc_attr_e($data->url) ?>" class="wpjb-indeed-link" rel="nofollow" <?php echo $onmousedown ?>>
                    <?php esc_html_e($object->job_title) ?>
                </a>
            </span> 
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
        
        return apply_filters("wpjb_indeed_item", $result, $item, $import);
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

        $publisher = wpjb_conf("indeed_publisher");
        
        $url = "http://api.indeed.com/ads/apisearch?publisher=";
        $url.= $publisher."&co=".$country."&limit=";
        $url.= $max."&l=".urlencode($location)."&fromage=".$posted;
        $url.= "&q=".urlencode($keyword);
        $url.= "&v=2";

        $response = wp_remote_get($url);
        
        if($response instanceof WP_Error) {
            return $result;
        }
        
        $xml = new SimpleXMLElement($response["body"]);
        $keys = array();

        foreach($xml->results->result as $r) {
            $keys[] = (string)$r->jobkey;
        }
        
        $keys = join(",", $keys);
        
        $url = "http://api.indeed.com/ads/apigetjobs?publisher=$publisher&jobkeys=".$keys."&v=2";
        $response = wp_remote_get($url);
        
        if($response instanceof WP_Error) {
            return $result;
        }
        
        $xml = new SimpleXMLElement($response["body"]);

        foreach($xml->results->result as $r) {
            $r = (object)$r;
            $r->external_id = (string)$r->jobkey;
            $result->item[] = $r;
        }

        return $result;
    
    }
}

class Wpjb_Service_IndeedJob extends Wpjb_Model_Job {
    
    protected $_indeed = null;
    
    protected $_indeedLogo = null;

    public function setIndeedData($item) {
        $this->_indeed = $item;
    }
    
    public function getIndeedData() {
        return $this->_indeed;
    }
    
    public function setIndeedTags($tags) {
        $this->_tag = $tags;
    }
    
    public function setIndeedLogoUrl($logo) {
        $this->_indeedLogo = $logo;
    }
    
    public function getLogoUrl($resize = null) {
        // maybe?? https://pbs.twimg.com/profile_images/465901126684913664/sTJZxF5G_400x400.jpeg
        return $this->_indeedLogo;
    }
    
    public function save() {
        throw new Exception("Indeed Job cannot be saved.");
    }
    
    public function delete() {
        throw new Exception("Indeed Job cannot be deleted.");
    }
}

?>
