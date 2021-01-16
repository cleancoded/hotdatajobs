<?php
/**
 * Description of Email
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Email extends Wpjb_Controller_Admin
{
    protected $_mailList = null;

    public function init()
    {
        $this->_mailList = array();
        $this->view->mailList = $this->_mailList;
        
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions", array(
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_Email",
                "info" => __("New Email Template has been created.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("email", "edit", "%d")
            ),
            "deleteAction" => array(
                "info" => __("Email Template #%d deleted.", "wpjobboard"),
                "page" => "email"
            ),
            "_delete" => array(
                "model" => "Wpjb_Model_Email",
                "info" => __("Email Template deleted.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Email"
            )
        ), "email" );
    }

    public function indexAction()
    {
        $query = new Daq_Db_Query();
        $data = $query->select("t1.*")->from("Wpjb_Model_Email t1")->order("sent_to")->execute();
        $item = array();
        
        foreach($data as $d) {
            if(!isset($item[$d->sent_to]) || !is_array($item[$d->sent_to])) {
                $item[$d->sent_to] = array();
            }
            $item[$d->sent_to][] = $d;
        }
        
        $desc = array(
            1 => __("Emails sent to admin <small>(emails are sent From and To email address specified in Mail From field)</small>", "wpjobboard"),
            2 => __("Emails sent to employer <small>(to email address specified in Company Email field)</small>", "wpjobboard"),
            3 => __("Emails sent to candidate", "wpjobboard"),
            4 => __("Other Emails", "wpjobboard"),
            5 => __("Custom Emails", "wpjobboard")
        );
        
        $this->view->desc = $desc;
        $this->view->data = $item;
    }

    public function editAction()
    {
        wp_enqueue_script("wpjb-admin-config-email");
        wp_enqueue_style("wp-jquery-ui-dialog");
        
        $email = new Wpjb_Model_Email($this->_request->get("id"));
        $evars = new Wpjb_List_EmailVars();
        
        $this->view->vars = $evars->getVariables();
        $this->view->objects = $evars->getEmailObjects($email);
        $this->view->customs = $evars->getEmailVars($email);

        $meta_inner = array(
            "name"=> __("Name", "wpjobboard"), 
            "title"=> __("Title", "wpjobboard"),
            "value" => __("Value (as string)", "wpjobboard"),
            "values" => __("Values (array)", "wpjobboard") 
        );
        
        $this->view->meta_inner = $meta_inner;
        
        $tag_inner = array(
            "id"=> __("ID", "wpjobboard"), 
            "type"=> __("Type", "wpjobboard"),
            "slug" => __("Name", "wpjobboard"),
            "title" => __("Title", "wpjobboard") 
        );
        
        $this->view->tag_inner = $tag_inner;
        
        $file_inner = array(
            "url"=> __("URL", "wpjobboard"),
            "basename"=> __("File Name", "wpjobboard"),
            "size" => __("File Size", "wpjobboard")
        );
        
        $this->view->file_inner = $file_inner;
        
        
        $form = new Wpjb_Form_Admin_Email($this->_request->getParam("id"));
        $this->view->id = $this->_request->getParam("id");
        if($this->isPost()) {
            $isValid = $form->isValid($this->_request->getAll());
            if($isValid) {
                $this->_addInfo(__("Email Template saved.", "wpjobboard"));
                $form->save();
            } else {
                $this->_addError(__("There are errors in the form.", "wpjobboard"));
            }
        }

        $this->view->form = $form;
    }
    
    public function composerAction()
    {
        $info = __("Form saved.", "wpjobboard");
        $error = __("There are errors in your form.", "wpjobboard");

        $footer_default = get_bloginfo('name');
        if(get_bloginfo('description')) {
            $footer_default .= ' — ' . get_bloginfo('description');
        }
        
        $this->view->footer_default = $footer_default;
        
        $f = new Wpjb_Form_Admin_Config_Email(array());
        if($this->isPost() && apply_filters("_wpjb_can_save_config", $this)) {
            $isValid = $f->isValid($this->_request->getAll());
            if($isValid) {
                
                $instance = Wpjb_Project::getInstance();
                foreach($f->getValues() as $k => $v) {
                    $instance->setConfigParam($k, $v);
                    $instance->saveConfig();
                }
                
                $this->_addInfo($info);
            } else {
                $this->_addError($error);
            }
        }

        $this->view->form = $f;
    }
    
    public function editorAction() 
    {
        
    }
}

?>