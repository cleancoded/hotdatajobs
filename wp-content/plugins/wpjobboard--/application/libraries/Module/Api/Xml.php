<?php
/**
 * Description of Plain
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Api_Xml extends Daq_Controller_Abstract
{
    private function _open($tag, array $param = null)
    {
        $list = "";
        if(is_array($param)) {
            $list = array();
            foreach($param as $k => $v) {
                $list[] = $k."=\"".esc_html($v)."\"";
            }
            $list = " ".join(" ", $list);
        }
        echo "<".$tag.$list.">";
    }

    private function _close($tag)
    {
        echo "</".$tag.">";
    }


    private function _xmlEntities($text, $charset = 'UTF-8')
    {
        return esc_html($text);
    }
    
    private function _tagIf($tag, $content, array $param = null)
    {
        if(strlen($content)>0) {
            $this->_tag($tag, $content, $param);
        }
    }

    private function _tag($tag, $content, array $param = null)
    {
        $this->_open($tag, $param);
        echo $this->_xmlEntities($content);
        $this->_close($tag);
    }

    private function _tagCIf($tag, $content, array $param = null)
    {
        if(!empty($content)) {
            $this->_tagC($tag, $content, $param);
        }
    }

    private function _tagC($tag, $content, array $param = null)
    {
        $this->_open($tag, $param);
        echo "<![CDATA[".$content."]]>";
        $this->_close($tag);
    }
    
    private function _params($defaults = array()) {
        
        $request = Daq_Request::getInstance();
        $query = $request->get("query", "all");
        $category = $request->get("category", "all");
        $type = $request->get("type", "all");
        
        if(empty($query) || $query == "all") {
            $query = "";
        } 
        
        if(empty($category) || $category == "all") {
            $category = null;
        } else {
            $category = $this->_resolve($category, "category");
        }
        
        if(empty($type) || $type == "all") {
            $type = null;
        } else {
            $type = $this->_resolve($type, "type");
        }
        
        $defaults = array_merge(array(
            "page" => 1,
            "count" => 50,
            "exclude_imported" => true
        ), $defaults);
        
        $params = array_merge($defaults, array(
            "filter" => "active",
            "query" => $query,
            "category" => $category,
            "type" => $type,
            "posted" => $request->get("posted"),
            "page" => $request->get("page", $defaults["page"]),
            "count" => $request->get("count", $defaults["count"]),
            "country" => $request->get("country"),
            "location" => $request->get("location"),
            "is_featured" => $request->get("is_featured"),
            "employer_id" => $request->get("employer_id"),
            "field" => $request->get("field", array()),
            "sort" => $request->get("sort"),
            "order" => $request->get("order"),
            "ids_only" => true,
            "exclude_imported" => $request->get("exclude_imported", true)
        ));
        
        return apply_filters("wpjb_api_xml_params", $params);
    }

    public function indeedAction()
    {
        header("Content-type: application/xml");
        echo '<?xml version="1.0" encoding="UTF-8" ?>'.PHP_EOL;
        $url = site_url();
        $this->_open("source");
        $this->_tag("publisher", Wpjb_Project::getInstance()->conf("seo_job_board_title"));
        $this->_tag("publisherurl", $url);
        $this->_tag("lastBuildDate", date(DATE_RSS));

        $jobs = wpjb_find_jobs($this->_params(array("count"=>null)));

        foreach($jobs->job as $id) {
            $job = new Wpjb_Model_Job($id);
            $ct = Wpjb_List_Country::getByCode($job->job_country);

            $this->_open("job");
            $this->_tagC("title", $job->job_title);
            $this->_tagC("date", $job->job_created_at);
            $this->_tagC("referencenumber", $job->id);
            $this->_tagC("url", wpjb_link_to("job", $job));
            $this->_tagC("company", $job->company_name);
            $this->_tagC("city",  $job->job_city);
            $this->_tagC("state",  $job->job_state);
            $this->_tagC("country",  $ct['iso2']);
            $this->_tagC("description", strip_tags($job->job_description));
            $this->_tagC("type", $job->getTag()->type[0]->title);
            $this->_tagC("category", $job->getTag()->category[0]->title);
            do_action("wpjb_xml_api_indeed", $job);
            $this->_close("job");
        }
        $this->_close("source");
    }

    public function trovitAction()
    {
        header("Content-type: application/xml");
        echo '<?xml version="1.0" encoding="UTF-8" ?>'.PHP_EOL;
        $this->_open("trovit");
        
        $jobs = wpjb_find_jobs($this->_params(array("count"=>null)));

        foreach($jobs->job as $id) {
            $job = new Wpjb_Model_Job($id);
            $ct = Wpjb_List_Country::getByCode($job->job_country);

            $type = null;
            $category = null;
            
            if(isset($job->getTag()->type[0])) {
                $type = $job->getTag()->type[0]->title;
            }
            
            if(isset($job->getTag()->category[0])) {
                $category = $job->getTag()->category[0]->title;
            }
            
            $this->_open("ad");
            
            $this->_tagC("id", $job->id);
            $this->_tagC("title", $job->job_title);
            $this->_tagC("content", strip_tags($job->job_description));
            $this->_tagC("url", wpjb_link_to("job", $job));
            $this->_tagCIf("company", $job->company_name);
            $this->_tagCIf("category", $category);
            $this->_tagCIf("jobtype", $type);
            $this->_tagCIf("working_hours", $type);
            $this->_tagCIf("city",  $job->job_city);
            $this->_tagCIf("region",  $job->job_state);
            $this->_tagCIf("postcode",  $job->job_zip_code);
            $this->_tagC("date", date("Y/m/d", $job->time->job_created_at));
            $this->_tagCIf("expiration_date", date("Y/m/d", $job->time->job_expires_at));
            do_action("wpjb_xml_api_trovit", $job);
            
            $this->_close("ad");
        }
        $this->_close("trovit");
    }
    
    public function simplyhiredAction()
    {
        header("Content-type: application/xml");
        echo '<?xml version="1.0" encoding="UTF-8" ?>';
        $url = site_url();
        $this->_open("jobs");
        
        $jobs = wpjb_find_jobs($this->_params(array("count"=>null)));
        
        foreach($jobs->job as $id) {
            $job = new Wpjb_Model_Job($id);
            $ct = Wpjb_List_Country::getByCode($job->job_country);
            $addr = array(
                $job->job_city,
                $job->job_state,
                $job->job_zip_code
            );

            $this->_open("job");
            $this->_tag("title", $job->job_title);
            $this->_tag("detail-url", wpjb_link_to("job", $job));
            $this->_tag("job-code", $job->id);
            $this->_tag("posted-date", $job->job_created_at);
            $this->_open("description");
            $this->_tagC("summary", strip_tags($job->job_description));
            $this->_close("description");
            $this->_open("location");
            $this->_tag("address", join(", ", $addr));
            $this->_tag("state", $job->job_state);
            $this->_tagIf("city", $job->job_city);
            $this->_tagIf("zip", $job->job_zip_code);
            $this->_tagIf("country", $ct['iso2']);
            $this->_close("location");
            $this->_open("company");
            $this->_tag("name", $job->company_name);
            $this->_tagIf("url", $job->company_url);
            $this->_close("company");
            do_action("wpjb_xml_api_simplyhired", $job);
            $this->_close("job");
        }

        $this->_close("jobs");
    }

    public function jujuAction()
    {
        header("Content-type: application/xml");
        echo '<?xml version="1.0" encoding="UTF-8" ?>';
        echo '<positionfeed
            xmlns="http://www.job-search-engine.com/employers/positionfeed-namespace/"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.job-search-engine.com/employers/positionfeed-namespace/ http://www.job-search-engine.com/employers/positionfeed.xsd" version="2006-04">';

        $url = site_url();

        $this->_tag("source", Wpjb_Project::getInstance()->conf("seo_job_board_title"));
        $this->_tag("sourcurl", $url);
        $this->_tag("feeddate", date(DATE_ISO8601));

        $jobs = wpjb_find_jobs($this->_params(array("count"=>null)));
        
        foreach($jobs->job as $id) {
            $job = new Wpjb_Model_Job($id);
            $code = Wpjb_List_Country::getByCode($job->job_country);
            $code = $code['iso2'];
            $this->_open("job", array("id"=>$job->id));
            $this->_tag("employer", $job->company_name);
            $this->_tag("title", $job->job_title);
            $this->_tagC("description", strip_tags($job->job_description));
            $this->_tag("postingdate", date(DATE_ISO8601, $job->job_created_at));
            $this->_tag("joburl", wpjb_link_to("job", $job));
            $this->_open("location");
            $this->_tag("nation", $code);
            $this->_tagIf("state", $job->job_state);
            $this->_tagIf("zip", $job->job_zip_code);
            $this->_tagIf("city", $job->job_city);
            $this->_close("location");
            do_action("wpjb_xml_api_juju", $job);
            $this->_close("job");
        }
        $this->_close("positionfeed");

    }
    
    public function xingAction()
    {
        header("Content-type: application/xml");
        echo '<?xml version="1.0" encoding="UTF-8" ?>'.PHP_EOL;
        $this->_open( "feed", array( "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance", "xsi:schemaLocation" => "https://dev.xing.com/jobs/xsd" ) );
        $this->_open( "postings" );
        
        $jobs = wpjb_find_jobs($this->_params(array("count"=>null)));

        foreach($jobs->job as $id) {
            $job = new Wpjb_Model_Job($id);
            $ct = Wpjb_List_Country::getByCode($job->job_country);

            $type = null;
            $category = null;
            
            if(isset($job->getTag()->type[0])) {
                $type = $job->getTag()->type[0]->title;
            }
            
            if(isset($job->getTag()->category[0])) {
                $category = $job->getTag()->category;
            }
            
            $this->_open("posting");
            
            $this->_tagC("id", $job->id);
            $this->_tagC("job_title", $job->job_title);
            $this->_tagC("description", strip_tags($job->job_description));
            $this->_tagC("url", wpjb_link_to("job", $job));
            $this->_tagC("company_name", $job->company_name);
            $this->_tagCIf("contact_email", $job->company_email);      
            $this->_open("tags");
            foreach( $category as $c ) {
                $this->_tagCIf("tag", $c->title);
            }
            $this->_close("tags");
            $this->_tagCIf("job_type", $type);
            $this->_open("job_location");
            $this->_tagC("city",  $job->job_city);
            $this->_tagCIf("zip_code",  $job->job_zip_code);
            $this->_tagC("country",  $ct['iso2']);
            $this->_close("job_location");
            do_action("wpjb_xml_api_xing", $job);
            
            $this->_close("posting");
        }
        
        $this->_close("postings");
        $this->_close("feed");
    }

    private function _esc($text)
    {
        return esc_html(ent2ncr($text));
    }

    protected function _resolve($str, $type)
    {
        $c = new Daq_Db_Query;
        $c->select("*");
        $c->from("Wpjb_Model_Tag t");
        $c->where("t.type = ?", $type);
        $c = $c->execute();

        $cl = array();
        foreach($c as $t) {
            $cl[$t->slug] = $t->id;
        }

        if(is_array($str)) {
            $strArr = $str;
        } elseif(!empty($str)) {
            $strArr = explode(",", $str);
        } else {
            $strArr = array();
        }
        
        $category = array();
        foreach($strArr as $c) {
            $c = trim($c);
            if(isset($cl[$c])) {
                $category[] = $cl[$c];
            } elseif(is_numeric($c)) {
                $category[] = $c;
            }
        }
        
        if(empty($category)) {
            return null;
        } else {
            return $category;
        }
    }
    
    public function rssAction()
    {
        header("Content-type: application/xml");

        $site_title = wpjb_conf("seo_job_board_title", get_bloginfo("name"));
        
        $rss = new DOMDocument();
        $rss->formatOutput = true;

        $wraper = $rss->createElement("rss");
        $wraper->setAttribute("version", "2.0");
        $wraper->setAttribute('xmlns:atom', "http://www.w3.org/2005/Atom");

        $channel = $rss->createElement("channel");

        $title = $rss->createElement("title", $this->_esc($site_title));
        $channel->appendChild($title);
        $link = $rss->createElement("link", $this->_esc(site_url()));
        $channel->appendChild($link);
        $description = $rss->createElement("description", $this->_esc($site_title));
        $channel->appendChild($description);

        $result = wpjb_find_jobs($this->_params())->job;
        
        if(function_exists("mb_substr")) {
            $substr_callback = "mb_substr";
        } else {
            $substr_callback = "substr";
        }
        
        foreach($result as $id) {

            $job = new Wpjb_Model_Job($id);
            
            $desc = strip_tags($job->job_description);
            $desc = iconv("UTF-8", "UTF-8//IGNORE", $substr_callback($desc, 0, 250));

            $desc = htmlspecialchars($desc, ENT_COMPAT, 'UTF-8');
            $description = $rss->createCDATASection($desc);
            $desc = $rss->createElement("description");
            $desc->appendChild($description);

            $link = wpjb_link_to("job", $job);
            $pubDate = date(DATE_RSS, strtotime($job->job_created_at));

            $item = $rss->createElement("item");
            $item->appendChild($rss->createElement("title", $this->_esc($job->job_title)));
            $item->appendChild($rss->createElement("link", $this->_esc($link)));
            $item->appendChild($desc);
            $item->appendChild($rss->createElement("pubDate", $pubDate));
            $item->appendChild($rss->createElement("guid", $this->_esc($link)));

            apply_filters("wpjb_xml_api_rss", $item, $rss, $job);
            
            $channel->appendChild($item);
        }

        $wraper->appendChild($channel);
        $rss->appendChild($wraper);

        print $rss->saveXML();

        exit;
        return false;

    }

    public function trackerAction()
    {
        $job = $this->getObject();
        if(!$job->is_active) {
            return false;
        }

        $job->stat_views++;

        $id = $job->getId();
        if(!isset($_COOKIE['wpjb'][$id])) {
            $job->stat_unique++;
        }
        
        $job->save();

        $find = array("https://www.", "https://", "http://www.", "http://");
        $domain = get_bloginfo("url");
        $domain = str_replace($find, "", $domain);

        setcookie("wpjb[$id]", time(), time()+(3600*24*30), "/", $domain);

        echo "var WpjbTracker = {};";

        exit;
        return false;
    }
    
    public function cptAction()
    {
        $request = Daq_Request::getInstance();
        
        switch($request->get("redirect")) {
            case "job": $object = new Wpjb_Model_Job($request->get("id")); break;
            case "resume": $object = new Wpjb_Model_Resume($request->get("id")); break;
            case "company": $object = new Wpjb_Model_Company($request->get("id")); break;
            default: $object = null;
        }
        
        if(is_object($object) && $object->exists() && $object->post_id<1) {
            $object->cpt();
            wp_redirect($object->url());
            exit;
        }
        
    }
    

}

?>
