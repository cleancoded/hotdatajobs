<?php
/**
 * Description of Listing
 *
 * @author greg
 * @package
 */

class Wpjb_Module_Admin_Discount extends Wpjb_Controller_Admin
{
    public function init()
    {
       $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "redirectAction" => array(
               "accept" => array(),
               "object" => "discount"
           ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_Discount",
                "info" => __("New discount has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("discount", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_Discount",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_delete" => array(
                "model" => "Wpjb_Model_Discount",
                "info" => __("Discount deleted.", "wpjobboard"),
                "error" => __("Discount could not be deleted.", "wpjobboard")
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted discounts: {success}", "wpjobboard")
                ),
                "activate" => array(
                    "success" => __("Number of activated discounts: {success}", "wpjobboard")
                ),
                "deactivate" => array(
                    "success" => __("Number of deactivated discounts: {success}", "wpjobboard")
                )
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Discount"
            )
        ), "discount" );
    }
    
    
    public function deleteAction() 
    {
        $id = $this->_request->getParam("id");
        $discount = new Wpjb_Model_Discount($id);
        
        if($this->_multiDelete($id)) {
            $m = sprintf(__("Discount '%s' deleted.", "wpjobboard"), $discount->title);
            $this->view->_flash->addInfo($m);
        }
        wp_redirect(wpjb_admin_url("discount"));
    }
    

    public function indexAction()
    {
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        $perPage = $this->_getPerPage();

        $query = new Daq_Db_Query();
        $this->view->data = $query->select("t.*")
            ->from("Wpjb_Model_Discount t")
            ->limitPage($page, $perPage)
            ->execute();

        $query = new Daq_Db_Query();
        $total = $query->select("COUNT(*) AS total")
            ->from("Wpjb_Model_Discount t")
            ->limit(1)
            ->fetchColumn();

        $this->view->current = $page;
        $this->view->total = ceil($total/$perPage);
    }

    protected function _multiActivate($id)
    {
        $object = new Wpjb_Model_Discount($id);
        $object->is_active = 1;
        $object->save();
        return true;
    }

    protected function _multiDeactivate($id)
    {
        $object = new Wpjb_Model_Discount($id);
        $object->is_active = 0;
        $object->save();
        return true;
    }

}

?>