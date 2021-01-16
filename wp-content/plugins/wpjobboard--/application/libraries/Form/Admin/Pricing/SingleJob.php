<?php
/**
 * Description of Listing
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Pricing_SingleJob extends Wpjb_Form_Admin_Pricing
{

    public function init()
    {
        $e = $this->create("price_for", "hidden");
        $e->setValue(Wpjb_Model_Pricing::PRICE_SINGLE_JOB);
        $this->addElement($e);
        
        $e = $this->addGroup("perks", __("Features", "wpjobboard"));
        
        $e = $this->create("visible");
        /* @var $e Daq_Form_Element */
        //$e->setRequired(true);
        $e->setValue($this->_object->meta->visible);
        $e->setLabel(__("Visible", "wpjobboard"));
        $e->addFilter(new Daq_Filter_NotNegativeInt());
        $e->setHint(__("Number of days job will be visible (0 for Never Expire).", "wpjobboard"));
        $e->setBuiltin(false);
        $e->setOrder(101);
        $this->addElement($e, "perks");
        
        $e = $this->create("is_featured", "checkbox");
        $m = $this->_object->meta->is_featured;
        /* @var $e Daq_Form_Element */
        $e->setValue($m->getValues(true));
        $e->setLabel(__("Is Featured", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int());
        $e->addOption(1, 1, __("Yes", "wpjobboard"));
        $e->setBuiltin(false);
        $e->setOrder(102);
        $this->addElement($e, "perks");
         
        parent::init();

    }
    
    public function save($append = array()) 
    {

        parent::save($append);
        
        $request = Daq_Request::getInstance();
        $features = $request->post("features");
        $meta = $this->getObject()->meta->features->getFirst();
        
        
        
        $meta->value = serialize($features);
        $meta->save();
        
    }
    
}
