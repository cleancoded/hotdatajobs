<?php
/**
 * Description of Listing
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Pricing_SingleResume extends Wpjb_Form_Admin_Pricing
{

    public function init()
    {
        $e = $this->create("price_for", "hidden");
        $e->setValue(Wpjb_Model_Pricing::PRICE_SINGLE_RESUME);
        $this->addElement($e);
        
        parent::init();

    }
    
}

?>