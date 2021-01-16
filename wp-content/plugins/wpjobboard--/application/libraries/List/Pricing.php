<?php

/**
 * Lists allowed pricing types (for example: single job posting, single resume access,
 * memberships and etc.).
 * 
 * @uses "wpjb_pricing_list" filter Allows to register new pricing
 * 
 * @since 4.4.1
 * @package WPJB
 */

class Wpjb_List_Pricing
{
    /**
     * List of available pricing types
     *
     * @var array
     */
    protected $_list = null;
    
    /**
     * Creates instance of pricings list
     * 
     * @uses "wpjb_pricing_list" filter Allows to register new pricing
     * 
     * @since 4.4.1
     * @return void
     */
    public function __construct() 
    {
        $types = array(
            array(
                "id" => 101,
                "name" => "single-job",
                "form" => "Wpjb_Form_Admin_Pricing_SingleJob",
                "title" => __("Single Job Posting", "wpjobboard"),
            ),
            array(
                "id" => 201,
                "name" => "single-resume",
                "form" => "Wpjb_Form_Admin_Pricing_SingleResume",
                "title" => __("Single Resume Access", "wpjobboard")
            ),
            array(
                "id" => 250,
                "name" => "employer-membership",
                "form" => "Wpjb_Form_Admin_Pricing_EmployerMembership",
                "title" => __("Employer Membership", "wpjobboard")
            ),
            array(
                "id" => 150,
                "name" => "candidate-membership",
                "form" => "Wpjb_Form_Admin_Pricing_CandidateMembership",
                "title" => __("Candidate Membership", "wpjobboard")
            ),
            
        );

        $this->_list = apply_filters("wpjb_pricing_list", $types);
    }
    
    /**
     * Returns pricing object by key
     * 
     * @since 4.4.1
     * @param string $name One of: "id", "name", "form", "title"
     * @param mixed $value Value to search by
     * @return mixed Array or Null
     */
    public function getBy($name, $value)
    {
        foreach($this->_list as $pricing) {
            if(isset($pricing[$name]) && $pricing[$name] == $value) {
                return $pricing;
            }
        }
        
        return null;
    }
    
    /**
     * Returns all pricing types
     * 
     * @since 4.4.1
     * @return array All registered pricings
     */
    public function getAll()
    {
        return $this->_list;
    }
}