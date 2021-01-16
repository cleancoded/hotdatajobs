<?php
/**
 * Description of Recent Jobs
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_FeaturedJobs extends Daq_Widget_Abstract
{
    
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "featured-jobs.php";
        $this->_viewFront = "featured-jobs.php";
        
        $this->_defaults["count"] = 5;
        
        parent::__construct(
            "wpjb-featured-jobs", 
            __("Featured Jobs", "wpjobboard"),
            array("description"=>__("Displays list of recent featured jobs.", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
	$instance = $old_instance;
	$instance['title'] = htmlspecialchars($new_instance['title']);
	$instance['hide'] = (int)($new_instance['hide']);
	$instance['count'] = (int)($new_instance['count']);
        return $instance;
    }

    public function _filter()
    {
        $this->view->jobList = wpjb_find_jobs(array(
            "filter" => "active",
            "is_featured" => 1,
            "page" => 1,
            "count" => $this->_get("count", $this->_defaults["count"])
        ))->job;
    }
    
}

?>