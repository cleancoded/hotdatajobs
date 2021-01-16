<?php
/**
 * Description of Categories
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_Feeds extends Daq_Widget_Abstract
{
    
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "feeds.php";
        $this->_viewFront = "feeds.php";
        
        parent::__construct(
            "wpjb-widget-feeds", 
            __("Job Feeds", "wpjobboard"),
            array("description"=>__("Displays list of available WPJobBoard feeds", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
	$instance = $old_instance;
	$instance['title'] = htmlspecialchars($new_instance['title']);
	$instance['hide'] = (int)($new_instance['hide']);
        return $instance;
    }

    public function _filter()
    {
        $query = Daq_Db_Query::create();
        $query->from("Wpjb_Model_Tag t");
        $query->where("type = ?", Wpjb_Model_Tag::TYPE_CATEGORY);
        $this->view->categories = $query->execute();
        
    }
}

?>