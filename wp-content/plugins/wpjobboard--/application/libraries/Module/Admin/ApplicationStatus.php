<?php
/**
 * Description of Category
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_ApplicationStatus extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
            "redirectAction" => array(
                "accept" => array(),
                "object" => "applicationStatus"
            ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_ApplicationStatus",
                "info" => __("New application status has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("applicationStatus", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_ApplicationStatus",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
        ), "applicationStatus" );
    }
    
    public function addAction( $param = array() )
    {           
        $form = new Wpjb_Form_Admin_ApplicationStatus();
        $id = false;
        
        if($this->isPost()) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo( __("New application status has been created.", "wpjobboard") );
                $form->save();
                $id = $form->getElement( "id" )->getValue();
            } else {
                $this->_addError( __("There are errors in your form.", "wpjobboard") );
            }
        }

        $url = wpjb_admin_url( "applicationStatus", "edit", "%d" );
        $this->redirectIf($id, sprintf(str_replace("%25d", "%d", $url), $id));
        $this->view->form = $form;
    }

    protected function _multiDelete( $id )
    {
        $statuses = wpjb_get_application_status();
        $total = wpjb_count_applications($id);

        if( $total > 0 ) {
            $err = __("Cannot delete application status identified by ID #{id}. There are still applications with this application status.", "wpjobboard");
            $err = str_replace("{id}", $id, $err);
            $this->view->_flash->addError($err);
            return false;
        }

        try {
            unset( $statuses[$id] );
            
            $instance = Wpjb_Project::getInstance();
            $instance->setConfigParam( "wpjb_application_statuses", $statuses );
            $instance->saveConfig();
        } catch(Exception $e) {
            // log error
        }
        
        wp_redirect( wpjb_admin_url( "applicationStatus" ) );
    }
    
    public function deleteAction() 
    {
        $id = $this->_request->getParam("id");
        
        if($this->_multiDelete($id)) {
            $m = sprintf(__("Application Status #%d deleted.", "wpjobboard"), $id);
            $this->view->_flash->addInfo($m);
        }
        wp_redirect(wpjb_admin_url("applicationStatus"));
    }
    
    public function indexAction()
    {
        $result = wpjb_get_application_status();

        $this->view->current = 1;
        $this->view->total = 1;
        $this->view->data = $result;
    }
    

}

?>
