<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Xing extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Xing API", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("api", __("URL", "wpjobboard"));
        
        $e = $this->create("xing_feed_url");
        $e->setValue( home_url() . "/wpjobboard/xml/xing/");
        $e->setLabel(__("URL", "wpjobboard"));
        $e->setAttr( "readonly", "readonly" );
        $e->setHint( __('Provide this URL in your Xing configuration.', "wpjobboard") );
        $this->addElement($e, "api");
        
        $this->addGroup("fields", __("Fields Mapping") );

        
        $form = new Wpjb_Form_AddJob();
        $fields = array();
        foreach($form->getFields() as $f ) {
            $fields[] = array( "id" => $f->name, "key" => $f->name, "description" => $f->label);
        }
        
        
        $e = $this->create( "xing_job_title", "select");
        $e->setValue( $instance->getConfig( "xing_job_title" ) );
        $e->setLabel(__("Job Title", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $e->setRequired( true );
        $this->addElement($e, "fields");
        
        $e = $this->create( "xing_description", "select");
        $e->setValue( $instance->getConfig( "xing_description" ) );
        $e->setLabel(__("Description", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $e->setRequired( true );
        $this->addElement($e, "fields");
        
        /*$e = $this->create( "xing_url", "select");
        $e->setValue( $instance->getConfig( "xing_url" ) );
        $e->setLabel(__("Description", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->setOptions( $fields );
        $e->setRequired( true );
        $this->addElement($e, "fields");*/
        
        $e = $this->create( "xing_company_name", "select");
        $e->setValue( $instance->getConfig( "xing_company_name" ) );
        $e->setLabel(__("Company Name", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $e->setRequired( true );
        $this->addElement($e, "fields");
        
        $e = $this->create( "xing_contact_email", "select");
        $e->setValue( $instance->getConfig( "xing_contact_email" ) );
        $e->setLabel(__("Zip Code", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $this->addElement($e, "fields");
        
        $e = $this->create( "xing_location_address", "select");
        $e->setValue( $instance->getConfig( "xing_location_address" ) );
        $e->setLabel(__("Address", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $this->addElement($e, "fields");
        
        $e = $this->create( "xing_location_city", "select");
        $e->setValue( $instance->getConfig( "xing_location_city" ) );
        $e->setLabel(__("City", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $e->setRequired( true );
        $this->addElement($e, "fields");
        
        $e = $this->create( "xing_location_zip_code", "select");
        $e->setValue( $instance->getConfig( "xing_location_zip_code" ) );
        $e->setLabel(__("Zip Code", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $this->addElement($e, "fields");
        
        $e = $this->create( "xing_location_country", "select");
        $e->setValue( $instance->getConfig( "xing_location_country" ) );
        $e->setLabel(__("Country", "wpjobboard"));
        $e->setHint( __('', "wpjobboard") );
        $e->addOptions( $fields );
        $e->setRequired( true );
        $this->addElement($e, "fields");

        apply_filters("wpja_form_init_config_ziprecruiter", $this);

    }
}

?>