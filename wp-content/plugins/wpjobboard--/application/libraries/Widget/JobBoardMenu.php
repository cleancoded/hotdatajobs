<?php
/**
 * Description of JobBoardMenu
 *
 * @author greg
 * @package 
 */

class Wpjb_Widget_JobBoardMenu extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "job-board-menu.php";
        $this->_viewFront = "job-board-menu.php";
        
        parent::__construct(
            "wpjb-job-board-menu", 
            __("Job Board Menu", "wpjobboard"),
            array("description"=>__("Job board and employer menu.", "wpjobboard"))
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
        $info = wp_get_current_user();
        $isAdmin = true;
        if(!isset($info->wp_capabilities['administrator']) || !$info->wp_capabilities['administrator']) {
            $isAdmin = false;
        }

        if(!$isAdmin && $this->_context->conf("posting_allow")==3) {
            $this->view->can_post = false;
        } else {
            $this->view->can_post = true;
        }
        
    }

}

?>