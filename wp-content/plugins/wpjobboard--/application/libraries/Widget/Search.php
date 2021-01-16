<?php
/**
 * Description of Recent Jobs
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_Search extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "search.php";
        $this->_viewFront = "search.php";
        
        parent::__construct(
            "wpjb-search", 
            __("Search Jobs", "wpjobboard"),
            array("description"=>__("Search jobs widget.", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
	$instance = $old_instance;
	$instance['title'] = htmlspecialchars($new_instance['title']);
	$instance['hide'] = (int)($new_instance['hide']);
        return $instance;
    }
    
    protected function _filter()
    {
        global $wp_rewrite;
        $this->view->use_permalinks = $wp_rewrite->using_permalinks();
        
        if(wpjb_conf("urls_mode") != 2) {
            $this->view->page_id = wpjb_conf("link_jobs");
        } else {
            $this->view->page_id = wpjb_conf("urls_link_job");
        }
    }

}

?>