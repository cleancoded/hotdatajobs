<?php
/**
 * Description of Categories
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_Locations extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "locations.php";
        $this->_viewFront = "locations.php";
        
        $this->_defaults["count"] = 0;
        $this->_defaults["list"] = 3;
        
        parent::__construct(
            "wpjb-job-locations", 
            __("Job Locations", "wpjobboard"),
            array("description"=>__("Displays list of lob locations.", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
        foreach(array("hide", "title", "list", "count", "target_id") as $k) {
            if(!isset($new_instance[$k])) {
                $new_instance[$k] = "";
            }
        }
        
	$instance = $old_instance;
	$instance['title'] = htmlspecialchars($new_instance['title']);
	$instance['hide'] = (int)($new_instance['hide']);
	$instance['list'] = (int)($new_instance['list']);
        $instance['count'] = (int)($new_instance['count']);
        $instance['target_id'] = (int)($new_instance['target_id']);
        return $instance;
    }

    public function _filter()
    {
        $cols = array(
            "t1.job_country AS `job_country`",
            "t1.job_state AS `job_state`",
            "t1.job_zip_code AS `job_zip_code`",
            "t1.job_city AS `job_city`",
            "COUNT(*) AS `count`"
        );
        
        $select = new Daq_Db_Query();
        $select = $select->select(join(", ", $cols));
        $select->from("Wpjb_Model_Job t1");
        $select->where("t1.is_active = 1");
        $select->where("t1.job_expires_at >= ?", date("Y-m-d"));
        $select->where("t1.job_created_at <= ?", date("Y-m-d"));
        
        $hide_filled = wpjb_conf("front_hide_filled", false);
        if($hide_filled) {
            $select->where("is_filled = 0");
        }
        
        switch($this->_get("list", 4)) {
            case 1:
                $select->group("t1.job_country");
                break;
            case 2:
                $select->group("t1.job_state");
                break;
            case 3:
                $select->group("t1.job_city");
                break;
            case 4:
                $select->group("t1.job_city, t1.job_state");
                break;
            case 5:
                $select->group("t1.job_country, t1.job_city");
                break;
        }

        $result = $select->fetchAll();
        $params = array();
        
        foreach($result as $r) {
            $p = array("query"=>null, "title"=>"", "count"=>$r->count);
            switch($this->_get("list", 4)) {
                case 1:
                    $p["query"] = array("country"=>$r->job_country);
                    $p["title"] = Wpjb_List_Country::getByCode($r->job_country);
                    $p["title"] = $p["title"]["name"];
                    break;
                case 2:
                    $p["query"] = array("state"=>$r->job_state);
                    $p["title"] = "{$r->job_state}";
                    break;
                case 3:
                    $p["query"] = array("city"=>$r->job_city);
                    $p["title"] = "{$r->job_city}";
                    break;
                case 4:
                    $p["query"] = array("state"=>$r->job_state, "city"=>$r->job_city);
                    $p["title"] = "{$r->job_city}, {$r->job_state}";
                    break;
                case 5:
                    $c = Wpjb_List_Country::getByCode($r->job_country);
                    $p["query"] = array("country"=>$r->job_country, "city"=>$r->job_city);
                    $p["title"] = $c["name"].", ".$r->job_city;
                    break;
            }
            $params[] = $p;
        }
        
        $this->view->url = wpjb_link_to("search", null, array(), $this->_get("target_id", null));
        $this->view->locations = $params;
        $this->view->glue = "?";
        
        if(stripos($this->view->url, "?")) {
            $this->view->glue = "&";
        }
        
    }
}

?>