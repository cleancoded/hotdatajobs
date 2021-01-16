<?php
/**
 * Description of Payment
 *
 * @author greg
 * @package
 */

class Wpjb_Module_Admin_Memberships extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
            "redirectAction" => array(
                "accept" => array("user_id", "package_id", "sort_order"),
                "object" => "memberships"
            ),
            "deleteAction" => array(
                "info" => __("Membership #%d deleted.", "wpjobboard"),
                "page" => "memberships"
            ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_Membership",
                "info" => __("New membership has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("memberships", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_Membership",
                "info" => __("Membership data updated.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Membership"
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted memberships: {success}", "wpjobboard")
                ),
                "markpaid" => array(
                    "success" => __("Number of memberships marked as paid: {success}", "wpjobboard")
                ),
            )
        ), "membership" );
    }

    public function indexAction()
    {
        $params = array();
        $browsing = array();
        
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        $perPage = $this->_getPerPage();
        
        if($this->_request->get("package_id")) {
            $package_id = $this->_request->get("package_id");
            $pricing = new Wpjb_Model_Pricing($package_id);
            $params["package_id"] = $package_id;
            $browsing[] = sprintf(__("with package <strong>%s</strong>", "wpjobboard"), $pricing->title);
        }
        
        if($this->_request->get("user_id")) {
            $user_id = $this->_request->get("user_id");
            $user = new WP_User($user_id);
            $params["user_id"] = $user_id;
            $browsing[] = sprintf(__("purchased by <strong>%s</strong>", "wpjobboard"), $user->display_name);
        }
        
        $params["page"] = $page;
        $params["count"] = $perPage;
        
        $result = Wpjb_Model_Membership::search($params);

        $this->view->browsing = $browsing;
        $this->view->data = $result->membership;

        $this->view->param = $params;
        $this->view->current = $page;
        $this->view->total = ceil($result->total/$perPage);
    }
    


}

?>