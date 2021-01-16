<?php
/**
 * Description of ResumesMenu
 *
 * @author greg
 * @package
 */

class Wpjb_Widget_ResumesMenu extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "resumes-menu.php";
        $this->_viewFront = "resumes-menu.php";
        
        parent::__construct(
            "wpjb-resumes-menu", 
            __("Resumes Menu", "wpjobboard"),
            array("description"=>__("Resumes menu links", "wpjobboard"))
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

        if(current_user_can("manage_resumes")) {
            $this->view->is_employee = true;
        } else {
            $this->view->is_employee = false;
        }

        if($info->ID>0) {
            $this->view->is_loggedin = true;
        } else {
            $this->view->is_loggedin = false;
        }
    }

}

?>