<?php
/**
 * Description of Listing
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Pricing extends Wpjb_Controller_Admin
{
    /**
     * Returns current pricing based on $_GET['listing'] value
     * 
     * @param boolean $required If true exception will be thrown if pricing does not exist.
     * @return mixed Array or null
     * @throws Exception If pricing does not exist
     */
    public function getPricing($required = false)
    {
        $list = new Wpjb_List_Pricing;
        $pricing = $list->getBy("name", $this->_request->get("listing"));
        
        if($required && !is_array($pricing)) {
            throw new Exception("Unknown pricing type.");
        }
        
        return $pricing;
    }
    
    public function init()
    {
        $listing = "";
        $form = "";
        
        $pricing = $this->getPricing();
        
        if(is_array($pricing)) {
            $listing = $pricing["name"];
            $form = $pricing["form"];
        }
        
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "addAction" => array(
                "form" => $form,
                "info" => __("New pricing option has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("pricing", "edit", "%d", array("listing"=>$listing))
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_Pricing",
                "info" => __("Pricing option has been saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
           "redirectAction" => array(
               "accept" => array("listing"),
               "object" => "pricing",
               "action" => "list"
           ),
            "_delete" => array(
                "model" => "Wpjb_Model_Pricing",
                "info" => __("Listing deleted.", "wpjobboard"),
                "error" => __("Listing could not be deleted.", "wpjobboard")
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted listings: {success}", "wpjobboard")
                ),
                "activate" => array(
                    "success" => __("Number of activated listings: {success}", "wpjobboard")
                ),
                "deactivate" => array(
                    "success" => __("Number of deactivated listings: {success}", "wpjobboard")
                )
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Pricing"
            )
        ), "pricing" );
        
        $this->view->listing = $listing;
    }

    public function indexAction()
    {
        $query = new Daq_Db_Query();
        $query->select("COUNT(*) AS cnt, price_for AS price_for");
        $query->from("Wpjb_Model_Pricing t");
        $query->group("price_for");
        $result = $query->fetchAll();
        
        $list = new Wpjb_List_Pricing;
        $count = array();
        
        foreach($list->getAll() as $l) {
            $count[$l["id"]] = 0;
        }
        
        foreach($result as $r) {
            $count[$r->price_for] = $r->cnt;
        }
        
        $this->view->list = $list;
        $this->view->count = $count;
    }
    
    public function listAction()
    {
        $this->_delete();
        $this->_multi();
        
        $pricing = $this->getPricing(true);
        
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        $perPage = $this->_getPerPage();

        $query = new Daq_Db_Query();
        $this->view->data = $query->select("t.*")
            ->from("Wpjb_Model_Pricing t")
            ->where("price_for = ?", $pricing["id"])
            ->limitPage($page, $perPage)
            ->execute();

        $query = new Daq_Db_Query();
        $total = $query->select("COUNT(*) AS total")
            ->from("Wpjb_Model_Pricing t")
            ->where("price_for = ?", $pricing["id"])
            ->limit(1)
            ->fetchColumn();

        $this->view->pricing = $pricing;
        $this->view->listing = $this->_request->getParam("listing");
        $this->view->current = $page;
        $this->view->total = ceil($total/$perPage);
    }

    public function addAction($param = array()) 
    {
        $pricing = $this->getPricing(true);
        $this->view->listing = $pricing["name"];
        $this->view->title = sprintf(__("Add Pricing (%s)", "wpjobboard"), strtolower($pricing["title"]));
        
        parent::addAction($param);
    }
    
    public function editAction()
    {
        $id = $this->_request->getParam("id");
        $pricing = $this->getPricing(true);
        
        $fclass = $pricing["form"];
        
        $form = new $fclass($id);
        $addm = sprintf(__("Add Pricing (%s)", "wpjobboard"), strtolower($pricing["title"]));
        $editm= sprintf(__("Edit Pricing (%s)", "wpjobboard"), strtolower($pricing["title"]));

        if($this->isPost()) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo(__("Form saved.", "wpjobboard"));
                $form->save();
            } else {
                $this->_addError(__("There are errors in your form.", "wpjobboard"));
            }
        }

        if($form->getObject()->exists()) {
            $this->view->title = $editm." (ID: ".$form->getObject()->id.")";
        } else {
            $this->view->title = $addm;
        }
        
        $this->view->listing = $this->_request->getParam("listing");
        $this->view->form = $form;
       
        
    }
    
    public function deleteAction() 
    {
        $id = $this->_request->getParam("id");
        $listing = $this->_request->getParam("listing");
        
        if($this->_multiDelete($id)) {
            $m = sprintf(__("Pricing option #%d deleted.", "wpjobboard"), $id);
            $this->view->_flash->addInfo($m);
        }
        wp_redirect(wpjb_admin_url("pricing", "list", null, array("listing"=>$listing)));
    }
    
    protected function _multiActivate($id)
    {
        $object = new Wpjb_Model_Pricing($id);
        $object->is_active = 1;
        $object->save();
        return true;
    }

    protected function _multiDeactivate($id)
    {
        $object = new Wpjb_Model_Pricing($id);
        $object->is_active = 0;
        $object->save();
        return true;
    }

}
