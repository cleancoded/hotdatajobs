<?php
/**
 * Description of Custom Menu
 *
 * @author greg
 * @package 
 */

class Wpjb_Widget_CustomMenu extends Daq_Widget_Abstract
{
    public function __construct() 
    {
        $this->_context = Wpjb_Project::getInstance();
        $this->_viewAdmin = "custom-menu.php";
        $this->_viewFront = "custom-menu.php";
        
        $this->_defaults["structure"] = array();
        
        parent::__construct(
            "wpjb-custom-menu", 
            __("WPJobBoard Menu", "wpjobboard"),
            array("description"=>__("Customizable Menu.", "wpjobboard"))
        );
    }
    
    public function update($new_instance, $old_instance) 
    {
        $instance = $old_instance;
        $instance['title'] = htmlspecialchars($new_instance['title']);
        $instance['structure'] = $new_instance['structure'];
        
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