<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MembershipLimit
 *
 * @author Grzegorz
 */
class Wpjb_Validate_MembershipLimit
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    private $_listing = null;
    
    public function __construct($listing) 
    {
        $this->_listing = $listing;
    }
    
    public function isValid($value)
    {
        if(empty($value)) {
            return true;
        }
        
        list($price_for, $membership_id, $id) = explode("_", $value[0]);

        if(!$membership_id) {
            return true;
        }
        $membership = new Wpjb_Model_Membership($membership_id);
        $usage = $membership->package();
        $usage = $usage[$this->_listing];

        foreach($usage as $k => $u) {

            if($k == $id && $u["status"] == "limited") {
                $credits = $u["usage"]-$u["used"];
                if($credits < 1) {
                    $this->setError(__("You do not have any credits left.", "wpjobboard"));
                    return false;
                }
            }
        }

        return true;
    }
}

?>
