<?php
/**
 * Description of Recent Jobs
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_RecentJobs extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "recent-jobs.php";
        $this->_viewFront = "recent-jobs.php";
        
        $this->_defaults["count"] = 5;
        
        parent::__construct(
            "wpjb-recent-jobs", 
            __("Recent Jobs", "wpjobboard"),
            array("description"=>__("Displays list of recently posted jobs", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
	$instance = $old_instance;
	$instance['title'] = htmlspecialchars($new_instance['title']);
	$instance['count'] = (int)($new_instance['count']);
	$instance['hide'] = (int)($new_instance['hide']);
        $instance['category'] = $new_instance['category'];
        $instance['type'] = $new_instance['type'];
        $instance['query'] = $new_instance['query'];
        $instance['location'] = $new_instance['location'];
        $instance['is_featured'] = $new_instance['is_featured'];
        
        return $instance;
    }

    public function _filter()
    {
        $params = array();
        $exclude = array("title", "hide");
        
        foreach($this->view->param as $k => $v) {
            if(!in_array($k, $exclude) && !empty($v)) {
                $params[$k] = $v;
            }
        }
        
        $defaults = array(
            "filter" => "active",
            "sort_order" => "t1.job_created_at DESC, t1.id DESC",
            "page" => 1
        );
        
        $params = apply_filters("wpjb_widget_recent_jobs_params", $params);
        
        $this->view->jobs = wpjb_find_jobs( $params + $defaults );
        $this->view->jobsList = $this->view->jobs->job;
    }

}

?>