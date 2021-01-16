<?php
/**
 * Description of Categories
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_Categories extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "categories.php";
        $this->_viewFront = "categories.php";
        
        $this->_defaults["count"] = 0;
        
        parent::__construct(
            "wpjb-job-categories", 
            __("Job Categories", "wpjobboard"),
            array("description"=>__("Displays list of available job categories", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
	$instance = $old_instance;
	$instance['title'] = htmlspecialchars($new_instance['title']);
	$instance['hide'] = (int)($new_instance['hide']);
	$instance['count'] = (int)($new_instance['count']);
	$instance['hide_empty'] = (int)($new_instance['hide_empty']);
        return $instance;
    }

    public function _filter()
    {

        $this->view->categories = Wpjb_Utility_Registry::getCategories();
        
    }
}

?>