<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Membership
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_Membership extends Daq_Form_ObjectAbstract
{
    protected $_model = "Wpjb_Model_Membership";
    
    protected $_membership = null;
    
    public function getMembership()
    {
        return $this->_membership;
    }
    
    public function init() 
    {
        $this->addGroup( "default", __( "Membership Defaults", "wpjobboard" ) );
        $this->addGroup( "employer", __( "Employer Membership Usage", "wpjobboard" ) );
        $this->addGroup( "candidate", __( "Candidate Membership Benefits", "wpjobboard" ) );
        
        $package = new Wpjb_Model_Pricing( $this->getObject()->package_id );

        if($this->getObject()->user_id > 0) {
            $user_id = $this->getObject()->user_id;
            $user_text = get_user_by("id", $this->getObject()->user_id)->display_name;
        } else {
            $user_id = 0;
            $user_text = __("None", "wpjobboard");
        }
        
        $e = $this->create("user_id", "hidden");
        $e->addFilter(new Daq_Filter_Int());
        $e->setValue($user_id);
        $this->addElement($e, "_internal");
        
        $e = $this->create("user_id_text", "text");
        $e->setLabel(__("User", "wpjobboard"));
        $e->setAttr("data-target", "user_id");
        $e->setAttr("data-suggest", "wpjb_suggest_user");
        $e->setValue($user_text);
        $e->setRenderer("wpjb_form_helper_suggest");
        $this->addElement($e, "default");
        
        $pricing = new Daq_Db_Query();
        $pricing->from("Wpjb_Model_Pricing t");
        $pricing->where("price_for = ?", Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP);
        
        $mpricing = new Daq_Db_Query();
        $mpricing->from("Wpjb_Model_Pricing t");
        $mpricing->where("price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);
        
        $e = $this->create("package_id", "select");
        $e->setValue($this->getObject()->package_id);
        $e->setLabel(__("Package", "wpjobboard"));
        $e->addOptgroup( "employer", __("Employer Membership", "wpjobboard" ) );
        $e->addOptgroup( "candidate", __("Candidate Membership", "wpjobboard" ) );
        $e->setEmptyOption( true );
        $e->setRequired(true);
        foreach ($pricing->execute() as $p) {
            $e->addOption($p->id, $p->id, $p->title, "employer");
        }
        foreach ($mpricing->execute() as $p) {
            $e->addOption($p->id, $p->id, $p->title, "candidate");
        }
        $this->addElement($e, "default");
        
        $e = $this->create("started_at", "text_date");
        $e->setDateFormat(wpjb_date_format());
        $e->setValue($this->ifNew(date("Y-m-d"), $this->getObject()->started_at));
        $e->setLabel(__("Started At", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("expires_at", "text_date");
        $e->setDateFormat(wpjb_date_format());
        $e->setValue($this->ifNew(date("Y-m-d"), $this->getObject()->expires_at));
        $e->setLabel(__("Expires At", "wpjobboard"));
        $this->addElement($e, "default");
        
        $mlist = unserialize($this->getObject()->package);
        
        if( $package->price_for == Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP) {
        
            $price = array(
                array(
                    "title" => __("Job Posting", "wpjobboard"),
                    "price_for" => Wpjb_Model_Pricing::PRICE_SINGLE_JOB,
                    "hint" => __("Select which Job Postings will be included in this package and how many times Employer will be able to use them.", "wpjobboard"),

                ),
                array(
                    "title" => __("Resumes Access", "wpjobboard"),
                    "price_for" => Wpjb_Model_Pricing::PRICE_SINGLE_RESUME,
                    "hint" => "",
                ),
            );

            $order = 105;

            foreach($price as $p) {

                $pfor = $p["price_for"];

                $query = new Daq_Db_Query();
                $query->from("Wpjb_Model_Pricing t");
                $query->where("price_for = ?", $pfor);


                if(isset($mlist[$p["price_for"]])) {
                    $mdata = $mlist[$p["price_for"]];
                } else {
                    $mdata = null;
                }

                $e = $this->create("items_".$pfor, "checkbox");
                $e->setLabel($p["title"]);
                $e->setHint($p["hint"]);
                foreach($query->execute() as $item) {
                    $e->addOption($item->id, $item->id, $item->title);
                }
                $e->setRenderer("wpjb_admin_membership_render");
                $e->setOrder($order++);
                $e->setMaxChoices(100);
                $e->setValue($mdata);
                $this->addElement($e, "employer");

                $e = $this->create("items_".$pfor."_usage", "checkbox");
                $this->addElement($e);
            }
        } elseif( $package->price_for == Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP) {
            
            $instance = Wpjb_Project::getInstance();
            
            $pages = pages_with_shortcode( 'wpjb_employers_list' );
            $company_list_page_id = $pages[0]->ID;
            
            $e = $this->create("have_access", "select");
            if( isset ( $mlist['have_access'] ) ) {
                $e->setValue( $mlist['have_access'] );
            }
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
            $e->setMaxChoices(5);
            $e->setAttr("readonly", "readonly");
            $e->setOrder(105);
            $this->addElement($e, "candidate");

            $e = $this->create("is_searchable", "checkbox");
            $e->setBoolean(true);
            $e->setLabel(__("Is Searchable", "wpjobboard"));
            $e->addOption("1", "1", __("If this option is selected, Candidate with this membership will be visible in search results. ", "wpjobboard"));
            $e->setOrder(106);
            if( isset ( $mlist['is_searchable'] ) ) {
                $e->setValue( $mlist['is_searchable'] );
            }
            $this->addElement( $e, 'candidate' );
            
            $e = $this->create("can_apply", "checkbox");
            $e->setBoolean(true);
            $e->setLabel(__("Can Apply", "wpjobboard"));
            $e->addOption("1", "1", __("If this option is selected, Candidate with this membership will be able to apply for jobs. ", "wpjobboard"));
            $e->setOrder(106);
            if( isset ( $mlist['can_apply'] ) ) {
                $e->setValue( $mlist['can_apply'] );
            }
            $this->addElement( $e, 'candidate' );

            $e = $this->create("featured_level", "text");
            $e->setLabel(__("Featured Level", "wpjobboard"));
            $e->setHint(__("Candidates with higher featured level will be higher in search results. ", "wpjobboard"));
            $e->setOrder(107);
            $e->setAttr("readonly", "readonly");
            if( isset ( $mlist['featured_level'] ) ) {
                $e->setValue( $mlist['featured_level'] );
            }
            $this->addElement( $e, 'candidate' );

            $e = $this->create("alert_slots", "text");
            $e->setLabel(__("Alert Slots", "wpjobboard"));
            $e->setHint(__("Set max number of alerts for Candidate with this membership. ", "wpjobboard"));
            $e->setOrder(108);
            $e->setAttr("readonly", "readonly");
            if( isset ( $mlist['alert_slots'] ) ) {
                $e->setValue( $mlist['alert_slots'] );
            }
            $this->addElement( $e, 'candidate' );
            
        }
        
        apply_filters("wpja_form_init_membership", $this);
    }
    
    public function save($append = array())
    {
        parent::save($append);
        
        $object = $this->getObject();
        $package = new Wpjb_Model_Pricing( $object->package_id );
        
        if( $package->price_for == Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP) {
            
            $data = array(
                Wpjb_Model_Pricing::PRICE_SINGLE_JOB => array(),
                Wpjb_Model_Pricing::PRICE_SINGLE_RESUME => array(),
            );

            foreach(array_keys($data) as $key) {
                $post = $this->value("items_".$key);
                foreach((array)$post as $id => $usage) {
                    if($usage["status"] != "disabled") {
                        $data[$key][$id] = $usage;
                    }
                }
            }
        } elseif( $package->price_for == Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP) {
            $data = array(
                "alert_slots" => $this->value( "alert_slots" ),
                "have_access" => $this->value( "have_access" ),
                "is_searchable" => $this->value( "is_searchable" ),
                "can_apply" => $this->value( "can_apply" ),
                "featured_level" => $this->value( "featured_level" ),
            );
        }
        
        $object->package = serialize($data);
        $object->save();
        
        apply_filters("wpja_form_save_membership", $this);
    }
    
}



?>
