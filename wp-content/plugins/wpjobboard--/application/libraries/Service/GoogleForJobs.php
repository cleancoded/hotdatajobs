<?php

class Wpjb_Service_GoogleForJobs {
    
    /**
     * Job Types Map
     *
     * @var array
     */
    protected $_types = array();
    
    /**
     * Job Description Template
     *
     * @var string
     */
    protected $_template = '{$job.job_description}';
    
    public static function getFields() {
        $data = array(
            "additionalType" => array(
                "type" => "Url",
                "label" => __("Additional Type", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "alternateName" => array(
                "type" => "Text",
                "label" => __("Alternate Name", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "baseSalary" => array(
                "type" => "MonetaryAmount",
                "label" => __("Base Salary", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "datePosted" => array(
                "type" => "Date",
                "label" => __("Date Posted", "wpjobboard"),
                "is_required" => true,
                "is_recommended" => false
            ),
            "description" => array(
                "type" => "Text",
                "label" => __("Description", "wpjobboard"),
                "is_required" => true,
                "is_recommended" => false
            ),
            "disambiguatingDescription" => array(
                "type" => "Text",
                "label" => __("Disambiguating Description", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "educationRequirements" => array(
                "type" => "Text",
                "label" => __("Education Requirements", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "employmentType" => array(
                "type" => "Text",
                "label" => __("Employment Type", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "experienceRequirements" => array(
                "type" => "Text",
                "label" => __("Experience Requirements", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "identifier" => array(
                "type" => "Identifier",
                "label" => __("Identifier", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "image" => array(
                "type" => "Url",
                "label" => __("Image", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "hiringOrganization" => array(
                "type" => "Organization",
                "label" => __("Hiring Organization", "wpjobboard"),
                "is_required" => true,
                "is_recommended" => false
            ),
            "incentiveCompensation" => array(
                "type" => "Text",
                "label" => __("Incentive Compensation", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "industry" => array(
                "type" => "Text",
                "label" => __("Industry", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "jobBenefits" => array(
                "type" => "Text",
                "label" => __("Job Benefits", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "jobLocation" => array(
                "type" => "Place",
                "label" => __("Job Location", "wpjobboard"),
                "is_required" => true,
                "is_recommended" => false
            ),
            "jobLocationType" => array(
                "type" => "Text",
                "label" => __( "Job Location Type", "wpjobboard" ),
                "is_required" => false,
                "is_recommended" => false
            ),
            "mainEntityOfPage" => array(
                "type" => "Text",
                "label" => __("Main Entity Of Page", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "name" => array(
                "type" => "Text",
                "label" => __("Name", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "occupationalCategory" => array(
                "type" => "Text",
                "label" => __("Occupational Category", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "qualifications" => array(
                "type" => "Text",
                "label" => __("Qualifications", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "responsibilities" => array(
                "type" => "Text",
                "label" => __("Responsibilities", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "salaryCurrency" => array(
                "type" => "Text",
                "label" => __("Salary Currency", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "sameAs" => array(
                "type" => "Url",
                "label" => __("Same As", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "skills" => array(
                "type" => "Text",
                "label" => __("Skills", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "specialCommitments" => array(
                "type" => "Text",
                "label" => __("Special Commitments", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "title" => array(
                "type" => "Text",
                "label" => __("Title", "wpjobboard"),
                "is_required" => true,
                "is_recommended" => false
            ),
            "url" => array(
                "type" => "Url",
                "label" => __("url", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
            "validThrough" => array(
                "type" => "Date",
                "label" => __("Valid Through", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "workHours" => array(
                "type" => "Text",
                "label" => __("Work Hours", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),

        );
        
        return $data;
    }
    
    /**
     * Sets Job Types Map
     * 
     * The $types variable should be an array in which each key is WPJB 
     * Job Type ID and value is a string one of: "FULL_TIME", "PART_TIME", 
     * "CONTRACTOR", "TEMPORARY", "INTERN", "VOLUNTEER", "PER_DIEM", "OTHER".
     * 
     * @param array $types
     */
    public function setTypes(array $types) {
        $this->_types = $types;
    }
    
    /**
     * Returns Job Types Map
     * 
     * @return array
     */
    public function getTypes() {
        return $this->_types;
    }
    
    /**
     * Sets job description template
     * 
     * @since   5.3.2
     * @param   string  $template  Job description template
     * @return  void
     */
    public function setTemplate($template) {
        $this->_template = $template;
    }
    
    /**
     * Returns job description template
     * 
     * @since  5.3.2
     * @return string   Job description template
     */
    public function getTemplate() {
        return $this->_template;
    }
    
    /**
     * Generates a JSON-LD object for $job
     * 
     * @param mixed $job    Either Wpjb_Model_Job or integer (job id).
     * @return stdClass     JSON-LD for the given job
     */
    public function getJson($job) {
        
        if(! $job instanceof Wpjb_Model_Job) {
            $job = new Wpjb_Model_job($job);
        }

        $tpl = new Daq_Tpl_Email();
        $tpl->assign("job", $job->toArray());
        
        $country = Wpjb_List_Country::getByCode($job->job_country);
        
        $json = new stdClass();
        $json->{"@context"} = "http://schema.org/";
        $json->{"@type"} = "JobPosting";
        
        $json->title = $job->job_title;
        $json->description = $tpl->draw($this->getTemplate());
        
        $json->identifier = new stdClass();
        $json->identifier->{"@type"} = "PropertyValue";
        $json->identifier->name = $job->company_name;
        $json->identifier->value = $job->id;
        
        $json->datePosted = $job->job_created_at;
        $json->validThrough = $job->job_expires_at;
        $json->employmentType = $this->getType($job);
        
        $json->hiringOrganization = new stdClass();
        $json->hiringOrganization->{"@type"} = "Organization";
        $json->hiringOrganization->name = $job->company_name;
        $json->hiringOrganization->sameAs = $job->company_url;
        
        $json->jobLocation = new stdClass();
        $json->jobLocation->{"@type"} = "Place";
        $json->jobLocation->address = new stdClass();
        $json->jobLocation->address->{"@type"} = "PostalAddress";
        $json->jobLocation->address->streetAddress = "";
        $json->jobLocation->address->addressLocality = $job->job_city;
        $json->jobLocation->address->addressRegion = $job->job_state;
        $json->jobLocation->address->postalCode = $job->job_zip_code;
        $json->jobLocation->address->addressCountry = $country["iso2"];
        
        return apply_filters("wpjb_google_for_jobs_jsonld", $json, $job);
    }
    
    /**
     * Returns full HTML for generated JSON object
     * 
     * @uses self::getJson()
     * 
     * @param mixed $job    Either Wpjb_Model_Job or integer (job id).
     * @return string
     */
    public function getHtml($job) {
        $jsonld = $this->getJson($job);
        
        $html = '<script type="application/ld+json">'.PHP_EOL;
        $html.= json_encode($jsonld, JSON_PRETTY_PRINT);
        $html.= PHP_EOL.'</script>';
        
        return $html;
    }
    
    public function validateJson($json) {
        
        $fields = self::getFields();
        
        $response = new stdClass();
        $response->filled = 0;
        $response->total = count($fields);
        $response->missing = new stdClass();
        $response->missing->required = array();
        $response->missing->recommended = array();

        $special = array(
            "hiringOrganization" => "_validateHiringOrganization", 
            "jobLocation" => "_validateJobLocation"
        );
        
        foreach($fields as $name => $field) {
            if(isset($special[$name])) {
                $callback = $special[$name];
                $response = $this->$callback($response, $json);
            } else if(!isset($json->$name) || empty($json->$name)) {
                if($field["is_required"]) {
                    $response->missing->required[] = $field["label"];
                }
                if($field["is_recommended"]) {
                    $response->missing->recommended[] = $field["label"];
                }
            } else {
                $response->filled++;
            }
        }
        
        return $response;
        
        foreach($required as $r) {
            if(!isset($json->$r) || empty($json->$r)) {
                //$response->missing->required[] = $r;
            }
        }
        
        foreach($recommended as $r) {
            if(!isset($json->$r) || empty($json->$r)) {
                //$response->missing->recommended[] = $r;
            }
        }
    }
    
    protected function _validateHiringOrganization($response, $json) {
        $fields = self::getFields(); 
        
        if(!isset($json->hiringOrganization)) {
            $response->missing->required[] = $fields["hiringOrganization"]["label"];
            return $response;
        }

        $response->total += 1;
        
        if(!isset($json->hiringOrganization->name) || empty($json->hiringOrganization->name)) {
            $response->missing->required[] = $fields["hiringOrganization"]["label"] . " - " . __("Name", "wpjobboard");
        } else {
            $response->filled++;
        }
        
        if(isset($json->hiringOrganization->sameAs) && !empty($json->hiringOrganization->sameAs)) {
            $response->filled++;
        }
        
        if(isset($json->hiringOrganization->logo) && !empty($json->hiringOrganization->logo)) {
            $response->filled++;
        }
        
        return $response;
    }
        
    protected function _validateJobLocation($response, $json) {
        $fields = self::getFields(); 
        $field = $fields["jobLocation"];
        $required = array("addressLocality", "addressRegion");
        $recommended = array("postalCode", "streetAddress");
        
        if(!isset($json->jobLocation->address)) {
            $response->missing->required[] = $fields["jobLocation"]["label"];
            return $response;
        }
        
        $response->total += 4;
        $subs = array(
            "streetAddress" => array(
                "label" => __("Street Address", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "addressLocality" => array(
                "label" => __("City", "wpjobboard"),
                "is_required" => true,
                "is_recommended" => false
            ),
            "addressRegion" => array(
                "label" => __("Region", "wpjobboard"),
                "is_required" => true,
                "is_recommended" => false
            ),
            "postalCode" => array(
                "label" => __("Postal Code", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => true
            ),
            "addressCountry" => array(
                "label" => __("Country", "wpjobboard"),
                "is_required" => false,
                "is_recommended" => false
            ),
        );
        
        foreach($subs as $name => $sub) {
            if(!isset($json->jobLocation->address->$name) || empty($json->jobLocation->address->$name)) {
                if($sub["is_required"]) {
                    $response->missing->required[] = $field["label"] . " - " . $sub["label"];
                }
                if($sub["is_recommended"]) {
                    $response->missing->recommended[] = $field["label"] . " - " . $sub["label"];
                }
            } else {
                $response->filled++;
            }
        }
        
        return $response;
    }
    
    /**
     * Returns JobPosting scheme compatible job type name
     * 
     * @param Wpjb_Model_Job $job   Job Object
     * @return string               JobPosting scheme compatible Job Type
     */
    public function getType(Wpjb_Model_Job $job) {
        
        if(!isset($job->tag->type[0])) {
            return "OTHER";
        }
        
        $type = $job->tag->type[0]->id;
        
        if(isset($this->_types[$type])) {
            return $this->_types[$type];
        }
        
        return "OTHER";
    }
    
    /**
     * Maps job posting scheme with data from POST
     * 
     * This function is applied using wpjb_google_for_jobs_jsonld filter 
     * when testing.
     * 
     * @param stdClass $json        JSON-LD object
     * @param Wpjb_Model_Job $job   Job object
     * @return stdClass             Customized JSON-LD object
     */
    public function mapFromPost($json, Wpjb_Model_Job $job) {
        return $this->_map($json, $job, Daq_Request::getInstance()->getParam("jsonld", array()));
    }
    
    /**
     * Maps job posting scheme with data from configuration
     * 
     * This function is applied using wpjb_google_for_jobs_jsonld filter..
     * 
     * @param stdClass $json        JSON-LD object
     * @param Wpjb_Model_Job $job   Job object
     * @return stdClass             Customized JSON-LD object
     */
    public function mapFromConfig($json, Wpjb_Model_Job $job) {
        
        $map = array();
        $gconf = wpjb_conf("google_for_jobs");
        if(isset($gconf["jsonld"]) && is_array($gconf["jsonld"])) {
            $map = $gconf["jsonld"];
        }
        
        return $this->_map($json, $job, $map);
    }
    
    /**
     * Sorts arrays by "order".
     * 
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sort($a, $b) {
        if($a["order"] > $b["order"]) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
     * Does the actual mapping
     * 
     * @param stdClass $json        JSON-LD object
     * @param Wpjb_Model_Job $job   Job object
     * @param array $map            Mapping array
     * @return stdClass             Customized JSON-LD object
     */
    protected function _map($json, Wpjb_Model_Job $job, $map) {
        
        if(!is_array($map)) {
            return $json;
        }
        
        uasort($map, array($this, "sort"));
        
        $special = array(
            "baseSalary" => "_mapSalary",
            "identifier" => "_mapIdentifier",
            "hiringOrganization" => "_mapOrganization",
            "jobLocation" => "_mapPlace"
        );

        foreach($map as $uid => $mapi) {
            $key = $mapi["key"];
            $current = null;
            
            if(isset($json->$key)) {
                $current = $json->$key;
            }
            
            if(isset($special[$key])) {
                $callback = $special[$key];
                $value = $this->$callback($mapi, $job, $current);
            } else {
                $value = $this->_mapScalar($mapi, $job, $current);
            }
            
            if(!empty($value)) {
                $json->$key = $value;
            }
            
        }
        
        return $json;
    }
    
    /**
     * Map Identifier Object
     * 
     * @param array             $item       Mapping data
     * @param Wpjb_Model_Job    $job        Job Object
     * @param stdClass          $default    Current JSON-LD value
     * @return stdClass                     Customized JSON-LD node
     */
    protected function _mapIdentifier($item, $job, $default) {
        $object = new stdClass();
        $object->{"@type"} = "PropertyValue";

        $keys = array("name", "value");
        foreach($keys as $key) {
            $value = $this->_mapObject($item, $job, $default, $key);
            
            if(!empty($value)) {
                $object->$key = $value;
            }
        }
        
        return $object;
    }
    
    /**
     * Map Organization Object
     * 
     * @param array             $item       Mapping data
     * @param Wpjb_Model_Job    $job        Job Object
     * @param stdClass          $default    Current JSON-LD value
     * @return stdClass                     Customized JSON-LD node
     */
    protected function _mapOrganization($item, $job, $default) {
        $object = new stdClass();
        $object->{"@type"} = "Organization";

        $keys = array("name", "sameAs", "logo");
        foreach($keys as $key) {
            $value = $this->_mapObject($item, $job, $default, $key);
            
            if(!empty($value)) {
                $object->$key = $value;
            }
        }

        return $object;
    }
    
    /**
     * Map Place Object
     * 
     * @param array             $item       Mapping data
     * @param Wpjb_Model_Job    $job        Job Object
     * @param stdClass          $default    Current JSON-LD value
     * @return stdClass                     Customized JSON-LD node
     */
    protected function _mapPlace($item, $job, $default) {
        $object = new stdClass();
        $object->{"@type"} = "Place";
        $object->address = new stdClass();
        $object->address->{"@type"} = "PostalAddress";

        $keys = array(
            "streetAddress", "addressLocality", "addressRegion",
            "postalCode", "addressCountry"
        );
        foreach($keys as $key) {
            $value = $this->_mapObject($item, $job, $default->address, $key);
            
            if(!isset($object->address->$key) && isset($default->address->$key)) {
                $object->address->$key = $default->address->$key;
            }
            
            if(is_null($value)) {
                unset($object->address->$key);
            } else if(!empty($value)) {
                $object->address->$key = $value;
            }
        }

        return $object;
    }
    
    /**
     * Map Monetary Amount Object
     * 
     * @param array             $item       Mapping data
     * @param Wpjb_Model_Job    $job        Job Object
     * @param stdClass          $default    Current JSON-LD value
     * @return stdClass                     Customized JSON-LD node
     */
    protected function _mapSalary($item, $job, $default) {
        $object = new stdClass();
        $object->{"@type"} = "MonetaryAmount";

        $keys = array("value", "currency");
        foreach($keys as $key) {
            $value = $this->_mapObject($item, $job, $default, $key);
            
            if(!empty($value)) {
                $object->$key = $value;
            }
        }
        
        return $object;
    }
    
    /**
     * Map Object
     * 
     * This function is being used by self::_map{func}() functions.
     * 
     * @param array             $item       Mapping data
     * @param Wpjb_Model_Job    $job        Job Object
     * @param stdClass          $default    Current JSON-LD value
     * @param string            $key        Node name
     * @return stdClass                     Customized JSON-LD node
     */
    protected function _mapObject($item, $job, $default, $key) {

        $args = array(
            "path" => $item["path"][$key]["value"],
            "text" => ""
        );
        if(isset($item["path"][$key]["text"])) {
            $args["text"] = $item["path"][$key]["text"];
        }

        $current = null;
        if(isset($default->$key)) {
            $current = $default->$key;
        }

        if($args["path"] == "inherit" ) {
            if(isset($default->$key)) {
                $value = $default->$key;
            } else {
                $value = null;
            }
        } else {
            $value = $this->_mapScalar($args, $job, $current);
        }

        return $value;
        
    }
    
    /**
     * Maps a scalar value
     * 
     * Used to map nodes which accept Text, Date or URL values.
     * 
     * This function is also used to map individaul properties inside complex
     * objects like Place, Identifier, Organization and MonetaryAmount
     * 
     * @param array             $item       Mapping data
     * @param Wpjb_Model_Job    $job        Job Object
     * @param stdClass          $default    Current JSON-LD value
     * @return stdClass                     Customized JSON-LD node
     */
    protected function _mapScalar($item, $job, $default) {
        if($item["path"] == "text") {
            return $item["text"];
        } elseif($item["path"] == "null") {
            return null;
        } else {
            $value = $this->_mapGetValue($item, $job);
        }
        
        if(empty($value) && $default) {
            return $default;
        } else {
            return $value;
        }
    }
    
    /**
     * Returns value for a single field.
     * 
     * @param array $map            Mapping data
     * @param Wpjb_Model_Job $job   Job Object 
     * @return string               Value after mapping the field
     */
    protected function _mapGetValue($map, $job) {
        $value = "";
        $object = null;

        list($class, $type, $name) = explode("__", $map["path"]);
        
        switch($class) {
            case "company": $object = $job->getCompany(true); break;
            case "job": $object = $job; break;
        }
        
        if( !is_object( $object ) ) {
            return "";
        }
        
        $arr = $object->toArray();
        
        switch($type) {
            case "default": 
                $value = $object->$name; 
                break;
            case "meta": 
                if($object->meta->$name) {
                   $value = join(", ", $object->meta->$name->values());  
                }
                break;
            case "tag": 
                if(isset($object->tag->{$name}[0])) {
                    $value = $object->tag->{$name}[0]->title;
                }
                break;
            case "country": 
                if(isset($arr["country"][$name])) {
                    $value = $arr["country"][$name]; 
                }
                break;
            case "file": 
                if(isset($arr["file"][$name][0])) {
                    $value = $arr["file"][$name][0]["url"]; 
                }
                break;
        } // end switch
        
        return $value;
    }
}
