<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pricing
 *
 * @author greg
 */

class Wpjb_Model_Pricing extends Daq_Db_OrmAbstract
{
    const PRICE_SINGLE_JOB = 101;
    const PRICE_SINGLE_RESUME = 201;
    const PRICE_EMPLOYER_MEMBERSHIP = 250;
    const PRICE_CANDIDATE_MEMBERSHIP = 150;
    
    protected $_name = "wpjb_pricing";

    /**
     * Meta table name
     * 
     * @var string
     */
    protected $_metaTable = "Wpjb_Model_Meta";
    
    /**
     * Meta table object key
     *
     * @var string 
     */
    protected $_metaName = "pricing";
    
    protected $_coupon = null;
    
    protected function _init()
    {
        $this->_reference["meta"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_MetaValue",
            "foreignId" => "object_id",
            "type" => "ONE_TO_ONCE"
        );
    }
    
    public function applyCoupon($code)
    {
        
        $v = new Wpjb_Validate_Coupon($this->currency);
        
        if(!$v->isValid($code)) {
            $msg = $v->getErrors();
            throw new Wpjb_Model_PricingException($msg[0]);
        }
        
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Discount t");
        $query->where("code = ?", $code);
        $query->limit(1);
        
        $coupon = $query->execute();
        
        if($this->price_for != $coupon[0]->discount_for) {
            throw new Wpjb_Model_PricingException(__("Entered discount code cannot be applied to this item.", "wpjobboard"));
        }
        
        $this->_coupon = $coupon[0];
    }
    
    public function getCoupon()
    {
        return $this->_coupon;
    }
    
    public function getPrice() 
    {
        return $this->price;
    }
    
    public function getDiscount()
    {
        if($this->_coupon === null) {
            return 0;
        }
        
        $coupon = $this->_coupon;
        
        if($coupon->type == 1) {
            // %
            $d = round($this->price*($coupon->discount/100), 2);
        } else {
            // $
            $d = $coupon->discount;
        }
        
        return $d;

    }
    
    public function getTotal()
    {
        $result = $this->getPrice()-$this->getDiscount();
        
        if($result > 0) {
            return $result;
        } else {
            return 0;
        }
    }
    
    public function save() {
        
        $id = parent::save();

        $gateways = Wpjb_Project::getInstance()->payment->getEnabled();
        
        if( !empty( $gateways ) ) {
            foreach($gateways as $gateway) {
                if($gateway == "Wpjb_Payment_Stripe") {
                    $stripe = new Wpjb_Payment_Stripe();    
                    if(Daq_Request::getInstance()->post('is_recurring') == 1) {
                        $stripe->createPlan( $this, Daq_Request::getInstance()->post('visible') );                        
                    } elseif( Daq_Request::getInstance()->post('is_recurring') == 0 && $this->meta->stripe_id->value() !== null ) {
                        $stripe->removePlan($this);
                    }
                }
            }
        }
        
        return $id;
    }
    
    public function remove() {
        
        $gateways = Wpjb_Project::getInstance()->payment->getEnabled();

        if( !empty( $gateways ) ) {
            foreach($gateways as $gateway) {
                if($gateway == "Wpjb_Payment_Stripe") {
                    $stripe = new Wpjb_Payment_Stripe();
                    $stripe->removePlan($this);
                }
            }
        }
        
        return parent::remove();
    }
    
}

?>
