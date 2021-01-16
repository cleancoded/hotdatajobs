<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmployerMembership
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_Pricing_CandidateMembership extends Wpjb_Form_Admin_Pricing
{

    public function init()
    {
        $this->addGroup( 'features', __( "Membership Features", "wpjobboard" ), 2 );
        $this->addGroup( "recurring", __( "Subscription Settings", "wpjobboard" ), 3 );
        
        $e = $this->create("price_for", "hidden");
        $e->setValue(Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);
        $this->addElement( $e, 'details' );
        
        $e = $this->create("is_trial", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Trial", "wpjobboard"));
        $e->addOption("1", "1", __("Automatically apply this membership to every newly registered Employer.", "wpjobboard"));
        $e->setOrder(101);
        $e->setValue($this->getObject()->meta->is_trial->value());
        $this->addElement( $e, 'features' );
        
        $e = $this->create("is_featured", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Featured", "wpjobboard"));
        $e->addOption("1", "1", __("Featured membership has additional class on membership pricing page, that allows you to distinguish most popular membership.", "wpjobboard"));
        $e->setOrder(102);
        $e->setValue($this->getObject()->meta->is_featured->value());
        $this->addElement( $e, 'features' );

        
        $e = $this->create("visible");
        /* @var $e Daq_Form_Element */
        //$e->setRequired(true);
        $e->setValue($this->_object->meta->visible);
        $e->setLabel(__("Recurrence", "wpjobboard"));
        $e->addFilter(new Daq_Filter_NotNegativeInt());
        $e->setHint(__("How many days the membership will be valid (0 for Never Expire).", "wpjobboard"));
        $e->setBuiltin(false);
        $e->setOrder(104);
        //$e->setRequired(true);
        $this->addElement( $e, 'features' );
        
        //$data = unserialize($this->getObject()->meta->package->value());
        
        /*$e = $this->create("have_access", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Have Access", "wpjobboard"));
        $e->addOption("1", "1", __("If this option is selected, Candidate with this membership will have access to WPJobBoard features. ", "wpjobboard"));
        $e->setOrder(105);
        $e->setValue($this->getObject()->meta->have_access->value());
        $this->addElement( $e, 'features' );*/
        
        $instance = Wpjb_Project::getInstance();
        
        $pages = pages_with_shortcode( 'wpjb_employers_list' );
        $company_list_page_id = $pages[0]->ID;
        
        $e = $this->create("have_access", "select");
        $e->setValue( explode( ",", $this->getObject()->meta->have_access->value() ) );
        $e->setLabel(__("Have Access", "wpjobboard"));
        $e->setHint(__("Candidate will have access to selected pages (only if restricted by config)", "wpjobboard"));
        $e->addOption( $instance->getConfig("urls_link_job"), $instance->getConfig("urls_link_job"), __("Jobs List", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_job_search"), $instance->getConfig("urls_link_job_search"), __("Jobs Search", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_job_add"), $instance->getConfig("urls_link_job_add"), __("Jobs Add", "wpjobboard" ) );
        $e->addOption( 'job', 'job', __("Job Details", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_resume"), $instance->getConfig("urls_link_resume"), __("Resumes List", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_resume_search"), $instance->getConfig("urls_link_resume_search"), __("Resumes Search", "wpjobboard" ) );
        $e->addOption( 'resume', 'resume', __("Resume Details", "wpjobboard" ) );
        if( $company_list_page_id > 0 ) {
            $e->addOption( $company_list_page_id, $company_list_page_id, __("Company List", "wpjobboard" ) );
        }
        $e->addOption( 'company', 'company', __("Company Details", "wpjobboard" ) );
        $e->setMaxChoices(15);
        $e->setOrder(105);
        $this->addElement($e, "features");
        
        $e = $this->create("is_searchable", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Is Searchable", "wpjobboard"));
        $e->addOption("1", "1", __("If this option is selected, Candidate with this membership will be visible in search results. ", "wpjobboard"));
        $e->setOrder(106);
        $e->setValue($this->getObject()->meta->is_searchable->values());
        $this->addElement( $e, 'features' );
        
        $e = $this->create("can_apply", "checkbox");
        $e->setBoolean(true);
        $e->setLabel(__("Can Apply", "wpjobboard"));
        $e->addOption("1", "1", __("If this option is selected, Candidate with this membership will be able to apply for jobs. ", "wpjobboard"));
        $e->setOrder(106);
        $e->setValue($this->getObject()->meta->can_apply->values());
        $this->addElement( $e, 'features' );
        
        $e = $this->create("featured_level", "text");
        $e->setLabel(__("Featured Level", "wpjobboard"));
        $e->setHint(__("Candidates with higher featured level will be higher in search results. ", "wpjobboard"));
        $e->setOrder(107);
        $e->setValue($this->getObject()->meta->featured_level);
        $this->addElement( $e, 'features' );
        
        $e = $this->create("alert_slots", "text");
        $e->setLabel(__("Alert Slots", "wpjobboard"));
        $e->setHint(__("Set max number of alerts for Candidate with this membership. ", "wpjobboard"));
        $e->setOrder(108);
        $e->setValue($this->getObject()->meta->alert_slots);
        $this->addElement( $e, 'features' );
        
        $e = $this->create( "is_recurring_warning", "label" );
        $e->setLabel( __( "Recurring Warning", "wpjobboard" ) );
        $e->setHint( __( "Recurring plans in WPJobBoard work only with Stripe!", "wpjobboard" ) );
        $this->addElement( $e, "recurring" );
        
        $e = $this->create( "is_recurring_info", "label" );
        $e->setLabel( __( "Recurring Information", "wpjobboard" ) );
        $e->setHint( __( "Recurring plans can't be updated because of Stripe policy. The only way to update a plan is to remove it and create again. When you remove plan, all users who subscribed to it will lose their subscribtion. To remove subscribtion, uncheck 'Recurring' chekcbox and save pricing.", "wpjobboard" ) );
        $this->addElement( $e, "recurring" );
        
        $e = $this->create( "is_recurring", "checkbox" );
        $e->setBoolean( true );
        $e->setLabel( __( "Recurring", "wpjobboard" ) );
        $e->addOption( "1", "1", __("Recurring membership will charge user every selected period untill he resign.", "wpjobboard" ) );
        $e->setOrder(103);
        $e->setValue( $this->getObject()->meta->is_recurring->values() );
        $this->addElement( $e, "recurring" );
        
        
        parent::init();
        
        $this->getElement("is_active")->setHint(__("Only active listings can be used by candidates.", "wpjobboard" ) ); 
        
        if( isset( $this->getObject()->meta->is_recurring ) && $this->getObject()->meta->is_recurring->value() == 1) {
            $this->getElement('price')->setAttr('readonly', 'readonly');
            $this->getElement('title')->setAttr('readonly', 'readonly');
            $this->getElement('currency')->setAttr('readonly', 'readonly');
            $this->getElement('visible')->setAttr('readonly', 'readonly');
        }
        
    }
    
    public function save($append = array())
    {
        parent::save($append);
        
        $object = $this->getObject();

        $meta_fields = array(
            "is_trial", "is_featured", "is_recurring", "have_access",
            "is_searchable", "featured_level", "alert_slots", "can_apply"
        );
                
        foreach($meta_fields as $meta_field) {
            $meta = $object->meta->{$meta_field}->getFirst();
            $meta->object_id = $object->getId();
            if( is_array( $this->value($meta_field) ) ) {
                $meta->value = implode(",", $this->value( $meta_field ) );
            } else {
                $meta->value = $this->value($meta_field);
            }
            
            $meta->save();
        }
        
        $data = array(
            "alert_slots" => $this->value( "alert_slots" ),
            "have_access" => $this->value( "have_access" ),
            "is_searchable" => $this->value( "is_searchable" ),
            "can_apply" => $this->value( "can_apply" ),
            "featured_level" => $this->value( "featured_level" ),
        );
        
        $meta = $object->meta->package->getFirst();
        $meta->object_id = $object->getId();
        $meta->value = serialize($data);
        $meta->save();

    }
    
}

?>
