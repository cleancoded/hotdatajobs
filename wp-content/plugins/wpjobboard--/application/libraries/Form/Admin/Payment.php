<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Payment
 *
 * @author Grzegorz
 */
class Wpjb_Form_Admin_Payment extends Daq_Form_ObjectAbstract 
{
    protected $_model = "Wpjb_Model_Payment";
    
    protected function _currArr()
    {
        $list = array();
        foreach(Wpjb_List_Currency::getList() as $arr) {
            $v = $arr['code'];
            $code = $arr["code"];
            if($arr['symbol'] != null) {
                $v = $arr['symbol'].' '.$v;
            }
            $list[] = array("key"=>$code, "value"=>$code, "description"=>$v);
        }
        return $list;
    }
    
    protected function _pricingArr() {
        
        $pricing_obj = new Wpjb_Model_Pricing( $this->getObject()->pricing_id );
        
        $listing = array();
        $query = new Daq_Db_Query();
        $result = $query->select("t.*")
            ->from("Wpjb_Model_Pricing t")
            ->order("title")
            ->where("is_active = 1")
            ->where("price_for IN(?)", $pricing_obj->price_for )
            ->execute();
        
        foreach($result as $pricing) {
            $listing[] = array(
                "key" => $pricing->id,
                "value" => $pricing->id,
                "description" => $pricing->title
            );
        }
        
        return $listing;
    }
    
    public function init() 
    {
        $factory = Wpjb_Project::getInstance()->payment;
        
        $e = $this->create("engine", "select");
        foreach($factory->getEngines() as $engine => $class) {
            $object = new $class;
            $e->addOption($engine, $engine, $object->getTitle());
        }
        $e->setEmptyOption(true);
        $e->setValue($this->getObject()->engine);
        $this->addElement($e, "_internal");
        
        $e = $this->create("status", "select");
        foreach(wpjb_get_payment_status() as $status) {
            $e->addOption($status["id"], $status["id"], $status["label"]);
        }
        $e->setValue($this->getObject()->status);
        $this->addElement($e, "_internal");
        
        $e = $this->create("pricing_id", "select");
        $e->addOptions( $this->_pricingArr() );
        $e->setValue( $this->getObject()->pricing_id );
        $this->addElement( $e, "_internal" );
        
        $e = $this->create("payment_currency", "select");
        $e->addOptions($this->_currArr());
        $e->setValue($this->getObject()->payment_currency);
        $this->addElement($e, "_internal");
        
        $e = $this->create("payment_sum");
        $e->setValue($this->getObject()->payment_sum);
        $this->addElement($e, "_internal");
        
        $e = $this->create("payment_discount");
        $e->setValue($this->getObject()->payment_discount);
        $this->addElement($e, "_internal");
        
        $e = $this->create("payment_paid");
        $e->setValue($this->getObject()->payment_paid);
        $this->addElement($e, "_internal");
        
        $this->addGroup("default", __("Payment Data", "wpjobboard"));
        
        $e = $this->create("fullname");
        $e->setLabel(__("Full Name", "wpjobboard"));
        $e->setValue($this->getObject()->fullname);
        $this->addElement($e, "default");
        
        $e = $this->create("email");
        $e->setLabel(__("Email", "wpjobboard"));
        $e->setValue($this->getObject()->email);
        $this->addElement($e, "default");
        
        $e = $this->create("user_id");
        $e->setLabel(__("User ID", "wpjobboard"));
        $e->setValue($this->getObject()->user_id);
        $this->addElement($e, "default");
        
        $e = $this->create("user_ip");
        $e->setLabel(__("IP Address", "wpjobboard"));
        $e->setValue($this->getObject()->user_ip);
        $this->addElement($e, "default");
        
    }
    
    public function save($append = array()) 
    {
        parent::save($append);
    }
}

?>
