<?php
/**
 * Description of Category
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_ApplicationStatus extends Daq_Form_Abstract
{
    //protected $_model = "Wpjb_Model_ApplicationStatus";

    public function _exclude()
    {
        /*if($this->_object->id ) {
            return array("id" => $this->_object->getId());
        } else {
            return array();
        }*/
    }
    
    public function getId() {
        
        return null;
    }

    public function init()
    {
        $id = Daq_Request::getInstance()->get( "id", null );
        $data = wpjb_get_application_status();
        
        if( $id != null ) {           
            $object = $data[$id];
        } else {
            $id = $data[ count( $data ) - 1]["id"] + 1;
        }
        
        $e = $this->create("id", "hidden");
        $e->setValue($id);
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e);
        
        $e = $this->create("label");
        $e->setRequired(true);
        $e->setValue($object["label"]);
        $e->setLabel( __( "Application Status", "wpjobboard" ) );
        $e->setHint( __( "Name of the application status visible everywhere (e.g. on application list).", "wpjobboard"));
        $this->addElement($e);
        
        $e = $this->create("public", "checkbox");
        $e->setValue($object["public"]);
        $e->setLabel( __( "Public", "wpjobboard" ) );
        $e->addOption( 1, 1, __("Public status will be visible in frone-end employer dashboard.", "wpjobboard" ) );
        $e->setBoolean(true);
        //$e->setHint( __( "Name of the application status visible everywhere (e.g. on application list).", "wpjobboard"));
        $this->addElement($e);
        
        $e = $this->create("slug");
        $e->setRequired(true);
        $e->setValue($object["slug"]);
        $e->setLabel(__("Status Slug", "wpjobboard"));
        $e->setHint(__("The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Slug());
        //$e->addValidator(new Daq_Validate_Db_NoRecordExists("Wpjb_Model_ApplicationStatus", "slug", $this->_exclude()));
        //$this->addElement($e);
        
        $e = $this->create("description");
        $e->setValue($object["description"]);
        $e->setLabel( __( "Status Description", "wpjobboard" ) );
        $e->setHint( __( "Short description of status visible in hints.", "wpjobboard"));
        //$this->addElement($e);
        
        $e = $this->create("bulb");
        $e->setValue($object["bulb"]);
        $e->setLabel( __( "CSS Class", "wpjobboard" ) );
        $e->setHint( __( "You can add custom CSS class for status.", "wpjobboard"));
        $this->addElement($e);
        
        $e = $this->create("email_template", "select");
        $e->setValue($object["email_template"]);
        $e->addOptions( $this->email_templates() );
        $e->setMaxChoices( 1 );
        $e->setEmptyOption( true );
        $e->setLabel( __( "E-mail Template", "wpjobboard" ) );
        $e->setHint( __( "E-mail template that will be used to inform applicant about status change. If empty no e-mail will be sent.", "wpjobboard"));
        $this->addElement($e);

        $e = $this->create( "order", "text" );
        $e->setValue( $object["order"] );
        $e->setLabel( __( "Order", "wpjobboard" ) );
        $e->setHint( __( "The smaller the number the higher the item will be displayed in the list.", "wpjobboard" ) );
        //$this->addElement($e);

        $e = $this->create("bg_color");
        //$e->addClass("wpjb-color-picker");
        $e->setValue($object["color"]);
        $e->addFilter( new Daq_Filter_Trim( "#" ) );
        $e->setLabel( __( "Background Color", "wpjobboard" ) );
        $e->setHint( __( "Color for status bulb background.", "wpjobboard" ) );
        $e->setRenderer( "wpjb_form_field_colorpicker" );
        $this->addElement( $e );
        
        $e = $this->create("text_color");
        //$e->addClass("wpjb-color-picker");
        $e->setValue($object["tcolor"]);
        $e->addFilter( new Daq_Filter_Trim( "#" ) );
        $e->setLabel( __( "Text Color", "wpjobboard" ) );
        $e->setHint( __( "Color for status bulb text.", "wpjobboard" ) );
        $e->setRenderer( "wpjb_form_field_colorpicker" );
        $this->addElement( $e );
        
        apply_filters("wpja_form_init_applicationstatus", $this);
    }
    
    public function isValid(array $values) {
        
        return true;
    }
    
    public function save($append = array()) 
    {
        
        $data = wpjb_conf( "wpjb_application_statuses" );
        //$data = array();
        
        $request = Daq_Request::getInstance();
        $email_id = $request->post("email_template", null);
        if( $email_id != null ) {
            $email_template = new Wpjb_Model_Email( $request->post("email_template") );
            $email_template_name = $email_template->name;
        } else {
            $email_template_name = "";
        }
        
        $id = $request->post("id", null);
        
        $data[$id] = array(
            "id"                        => $request->post("id"),
            "key"                       => "app_key_" . $request->post("id"),
            "color"                     => $request->post("bg_color"),
            "tcolor"                    => $request->post("text_color"),
            "bulb"                      => $request->post("bulb"),
            "label"                     => $request->post("label"),
            "public"                    => $request->post("public"),
            "email_template"            => $email_id,
            "notify_applicant_email"    => $email_template_name,
            "labels" => array(
                "multi_success" => sprintf( __( "Number of applications marked as %s: {success}", "wpjobboard" ), $request->post("title") ),
            )
        );
        
        $instance = Wpjb_Project::getInstance();
        $instance->setConfigParam( "wpjb_application_statuses", $data );
        $instance->saveConfig();
        
        
        apply_filters("wpja_form_save_applicationstatus", $this);
    }
    
    private function email_templates() {
        
        $q = new Daq_Db_Query();
        $templates = $q->select()->from("Wpjb_Model_Email t")
                    ->where("t.name = ?", "notify_applicant_status_change")
                    ->orWhere("t.name LIKE ?", 'notify_applicant_status_change%')
                    ->execute();
        
        $email_templates = array();
        foreach( $templates as $temp ) {
            $email_templates[] = array( "key" => $temp->id, "value" => $temp->id, "description" => $temp->name);
        }
        
        return $email_templates;
    }
}

?>

