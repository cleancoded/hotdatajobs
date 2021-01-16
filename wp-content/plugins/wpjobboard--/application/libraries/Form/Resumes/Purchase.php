<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contact
 *
 * @author Grzegorz
 */
class Wpjb_Form_Resumes_Purchase extends Daq_Form_Abstract
{
    protected function _listings()
    {
        $price_for_c = Wpjb_Model_Pricing::PRICE_SINGLE_RESUME;
        
        $listing = array();
        $query = new Daq_Db_Query();
        $result = $query->select("t.*")
            ->from("Wpjb_Model_Pricing t")
            ->order("title")
            ->where("is_active = 1")
            ->where("price_for IN(?)", $price_for_c)
            ->execute();
        
        foreach($result as $pricing) {
            $listing[] = array(
                "id" => $pricing->id,
                "key" => $pricing->price_for."_0_".$pricing->id,
                "title" => $pricing->title
            );
        }
        
        if(!Wpjb_Model_Company::current()) {
            return $listing;
        }
        
        foreach(Wpjb_Model_Company::current()->membership() as $membership) {
            $package = new Wpjb_Model_Pricing($membership->package_id);
            $data = $membership->package();
            
            if(!isset($data[$price_for_c])) {
                continue;
            }
            
            foreach($data[$price_for_c] as $id => $use) {
                
                $pricing = new Wpjb_Model_Pricing($id);
                
                if(!$pricing->exists()) {
                    continue;
                }
                
                $membership_id = $membership->id;
                
                if($use["status"] == "limited" && $use["used"] >= $use["usage"]) {
                    $renewal = $membership->getActiveRenewal($pricing);
                    if($renewal) {
                        $membership_id = $renewal->id;
                    }
                }
                
                $listing[] = array(
                    "id" => $package->id,
                    "key" => $package->price_for."_".$membership_id."_".$pricing->id,
                    "title" => $package->title." / ".$pricing->title
                );
            }
            
        }

        return $listing;
    }
    
    public function init() 
    {
        $this->addGroup("purchase", __("Purchase", "wpjobboard"));
        
        $e = $this->create("purchase", "hidden");
        $e->setValue(1);
        $this->addElement($e, "_internal");
        
        $e = $this->create("listing_type", "radio");
        $e->setLabel(__("Listing Type", "wpjobboard"));
        $e->setRequired(true);
        $listings = $this->_listings();
        foreach($listings as $p) {
            $e->addOption($p["key"], $p["key"], $p["title"]);
        }
        $e->setRenderer("wpjb_form_helper_resume_listing");
        $e->addValidator(new Wpjb_Validate_MembershipLimit(Wpjb_Model_Pricing::PRICE_SINGLE_RESUME));
        $this->addElement($e, "purchase");
        
        apply_filters("wpjr_form_init_purchase", $this);
    }
}

?>
