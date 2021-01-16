<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Google
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_Config_GoogleForJobs extends Daq_Form_Abstract
{
    public $name = null;
    
    protected $_types = array();

    protected function _getTypeValue($typeName) {
        foreach($this->_types as $id => $name) {
            if($name == $typeName) {
                return $id;
            }
        }
        return "";
    }
    
    public function init()
    {
        $gconf = wpjb_conf("google_for_jobs");
        if(isset($gconf["types"]) && is_array($gconf["types"])) {
            $this->_types = $gconf["types"];
        };
        
        $this->name = __("Google For Jobs", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("map-types", __("Employment Type Map", "wpjobboard"));
        
        $e = $this->create("google_fj_etype_full_time", "select");
        $e->setValue($this->_getTypeValue("FULL_TIME"));
        $e->setLabel(__("Full Time", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "FULL_TIME");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $e = $this->create("google_fj_etype_part_time", "select");
        $e->setValue($this->_getTypeValue("PART_TIME"));
        $e->setLabel(__("Part Time", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "PART_TIME");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $e = $this->create("google_fj_etype_contractor", "select");
        $e->setValue($this->_getTypeValue("CONTRACTOR"));
        $e->setLabel(__("Contractor", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "CONTRACTOR");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $e = $this->create("google_fj_etype_temporary", "select");
        $e->setValue($this->_getTypeValue("TEMPORARY"));
        $e->setLabel(__("Temporary", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "TEMPORARY");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $e = $this->create("google_fj_etype_intern", "select");
        $e->setValue($this->_getTypeValue("INTERN"));
        $e->setLabel(__("Intern", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "INTERN");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $e = $this->create("google_fj_etype_volunteer", "select");
        $e->setValue($this->_getTypeValue("VOLUNTEER"));
        $e->setLabel(__("Volunteer", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "VOLUNTEER");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $e = $this->create("google_fj_etype_per_diem", "select");
        $e->setValue($this->_getTypeValue("PER_DIEM"));
        $e->setLabel(__("Per Diem", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "PER_DIEM");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $e = $this->create("google_fj_etype_other", "select");
        $e->setValue($this->_getTypeValue("OTHER"));
        $e->setLabel(__("Other", "wpjobboard"));
        $e->setEmptyOption(true);
        $e->addClass("wpjb-gfj-job-type-map");
        $e->setAttr("data-google-type", "OTHER");
        $e->addOptions(wpjb_form_get_jobtypes());
        $this->addElement($e, "map-types");
        
        $this->addGroup("map", __("Fields Map", "wpjobboard"));
        
        $e = $this->create("google_fj_id", "select");
        $e->setLabel(__("Job ID", "wpjobboard"));
        $e->setRenderer(array($this, "fieldsMap"));
        $this->addElement($e, "map");
        
        $e = $this->create("_google_label", "label");
        $e->setLabel(__("Ready?", "wpjobboard"));
        $e->setDescription('<a href="#" class="button wpjb-gfj-types-close">Done</a>');
        $this->addElement($e, "map-types");
        
        apply_filters("wpja_form_init_config_google_for_jobs", $this);

    }
    
    public function fieldsMap(Daq_Form_Element $field, $form) {
 
        
        return wpjb_gfj_fields_map($field, $form);
        
    }
    

}

function wpjb_gfj_fields_map(Daq_Form_Element $field, $form) {
    ob_start();
    
    ?>
    
    <select id="google_field_name" name="google_field_name">
        <option value="" data-type=""><?php _e("Select property you would like to customize ...", "wpjobboard") ?></option>
        <option value="additionalType" data-type="Url"><?php _e("Additional Type", "wpjobboard") ?></option>
        <option value="alternateName" data-type="Text"><?php _e("Alternate Name", "wpjobboard") ?></option>
        <option value="baseSalary" data-type="MonetaryAmount"><?php _e("Base Salary", "wpjobboard") ?></option>
        <option value="datePosted" data-type="Date"><?php _e("Date Posted", "wpjobboard") ?></option>
        <option value="description" data-type="Text"><?php _e("Description", "wpjobboard") ?></option>
        <option value="disambiguatingDescription" data-type="Text"><?php _e("Disambiguating Description", "wpjobboard") ?></option>
        <option value="educationRequirements" data-type="Text"><?php _e("Education Requirements", "wpjobboard") ?></option>
        <option value="employmentType" data-type="Text"><?php _e("Employment Type", "wpjobboard") ?></option>
        <option value="experienceRequirements" data-type="Text"><?php _e("Experience Requirements", "wpjobboard") ?></option>
        <option value="identifier" data-type="Identifier"><?php _e("Identifier", "wpjobboard") ?></option>
        <option value="image" data-type="Url"><?php _e("Image", "wpjobboard") ?></option>
        <option value="hiringOrganization" data-type="Organization"><?php _e("Hiring Organization", "wpjobboard") ?></option>
        <option value="incentiveCompensation" data-type="Text"><?php _e("Incentive Compensation", "wpjobboard") ?></option>
        <option value="industry" data-type="Text"><?php _e("Industry", "wpjobboard") ?></option>
        <option value="jobBenefits" data-type="Text"><?php _e("Job Benefits", "wpjobboard") ?></option>
        <option value="jobLocation" data-type="Place"><?php _e("Job Location", "wpjobboard") ?></option>
        <option value="jobLocationType" data-type="Text"><?php _e("Job Location Type", "wpjobboard") ?></option>
        <option value="mainEntityOfPage" data-type="Text"><?php _e("Main Entity Of Page", "wpjobboard") ?></option>
        <option value="name" data-type="Text"><?php _e("Name", "wpjobboard") ?></option>
        <option value="occupationalCategory" data-type="Text"><?php _e("Occupational Category", "wpjobboard") ?></option>
        <option value="qualifications" data-type="Text"><?php _e("Qualifications", "wpjobboard") ?></option>
        <option value="responsibilities" data-type="Text"><?php _e("Responsibilities", "wpjobboard") ?></option>
        <option value="salaryCurrency" data-type="Text"><?php _e("Salary Currency", "wpjobboard") ?></option>
        <option value="sameAs" data-type="Url"><?php _e("Same As", "wpjobboard") ?></option>
        <option value="skills" data-type="Text"><?php _e("Skills", "wpjobboard") ?></option>
        <option value="specialCommitments" data-type="Text"><?php _e("Special Commitments", "wpjobboard") ?></option>
        <option value="title" data-type="Text"><?php _e("Title", "wpjobboard") ?></option>
        <option value="url" data-type="Text"><?php _e("Url", "wpjobboard") ?></option>
        <option value="validThrough" data-type="Date"><?php _e("Valid Through", "wpjobboard") ?></option>
        <option value="workHours" data-type="Text"><?php _e("Work Hours", "wpjobboard") ?></option>
    </select>

    <div class="wpjb-gfj-map-area">

    </div>
                                    
    <?php
    
    
    return ob_get_clean();
}



?>
