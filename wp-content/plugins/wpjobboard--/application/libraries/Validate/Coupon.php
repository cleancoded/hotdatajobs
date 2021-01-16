<?php
/**
 * Description of Coupon
 *
 * @author greg
 * @package 
 */

class Wpjb_Validate_Coupon
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{

    private $_currency = null;
    
    private $_price_for = null;

    public function __construct($currency = null, $price_for = null)
    {
        $this->_currency = $currency;
        $this->_price_for = $price_for;
    }

    public function isValid($value)
    {
        $query = new Daq_Db_Query();
        $result = $query->select("*")
            ->from("Wpjb_Model_Discount t")
            ->where("code = ?", $value)
            ->limit(1)
            ->execute();

        if(!isset($result[0])) {
            $this->setError(__("Coupon code does not exist.", "wpjobboard"));
            return false;
        }

        $discount = $result[0];
        if(!$discount->is_active) {
            $this->setError(__("Coupon code is not active.", "wpjobboard"));
            return false;
        }

        if(strtotime("now") > strtotime($discount->expires_at)) {
            $this->setError(__("Coupon code expired.", "wpjobboard"));
            return false;
        }

        if($discount->max_uses > 0 && $discount->used >= $discount->max_uses) {
            $this->setError(__("Coupon code expired.", "wpjobboard"));
            return false;
        }
        
        if($this->_currency!==null && $discount->type==2 && $this->_currency!=$discount->currency) {
            $this->setError(__("Currency does not match.", "wpjobboard"));
            return false;
        }
        
        if($this->_price_for!== null && $this->_price_for!=$discount->discount_for) {
            $this->setError(__("Entered discount code cannot be applied to this item.", "wpjobboard"));
            return false;
        }

        return true;
    }
}
?>