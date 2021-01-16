<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Taxer
 *
 * @author Grzegorz
 */
class Wpjb_Utility_Taxer 
{
    /**
     * Are taxes enabled?
     *
     * @var boolean
     */
    protected $_enabled = null;
    
    /**
     * Tax percentage value
     *
     * @var float
     */
    protected $_tax = null;
    
    /**
     * Price type (one of "gross" or "net")
     *
     * @var string
     */
    protected $_type = null;
    
    /**
     * Item price
     * 
     * @var float
     */
    protected $_price = null;
    
    /**
     * Item discount value
     *
     * @var float
     */
    protected $_discount = null;
    
    /**
     * Taxes config
     *
     * @var array
     */
    protected $_config = array();
    
    public function __construct() 
    {
        if(wpjb_conf("taxes_enabled")) {
            $enabled = true;
        } else {
            $enabled = false;
        }
        
        $this->setTaxRate(wpjb_conf("taxes_default_rate"));
        $this->setType(wpjb_conf("taxes_price_type"));
        $this->setEnabled($enabled);
    }
    
    /**
     * Enables and disables taxes
     * 
     * @since 4.4.0
     * @param boolean $enabled
     * @return void
     */
    public function setEnabled($enabled)
    {
        $this->_enabled = $enabled;
    }
    
    /**
     * Returns true if taxes are enabled
     * 
     * @since 4.4.0
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }
    
    /**
     * Sets item price
     * 
     * @since 4.4.0
     * @param float $price
     * @return void
     */
    public function setPrice($price)
    {
        $this->_price = floatval($price);
    }
    
    /**
     * Returns item price
     * 
     * @since 4.4.0
     * @return float Item price
     */
    public function getPrice()
    {
        return $this->_price;
    }
    
    /**
     * Sets item discount
     * 
     * @since 4.4.0
     * @param float $discount
     * @return void
     */
    public function setDiscount($discount)
    {
        $this->_discount = floatval($discount);
    }
    
    /**
     * Returns item discount
     * 
     * @since 4.4.0
     * @return float Item discount
     */
    public function getDiscount()
    {
        return $this->_discount;
    }
    
    /**
     * Sets tax percentage value
     * 
     * @since 4.4.0
     * @param float $price
     * @return void
     */
    public function setTaxRate($tax)
    { 
        $this->_tax = floatval($tax);
    }
    
    /**
     * Returns tax value
     * 
     * @since 4.4.0
     * @return float Tax value
     */
    public function getTaxRate()
    {
        return $this->_tax;
    }
    
    /**
     * Sets price type
     * 
     * @since 4.4.0
     * @param string $price
     * @return void
     */
    public function setType($type)
    { 
        $this->_type = $type;
    }
    
    /**
     * Returns price type
     * 
     * @since 4.4.0
     * @return string Price type (one of "gross", "net")
     */
    public function getType()
    {
        return $this->_type;
    }
    
    public function __get($name) 
    {
        switch($name) {
            case "price": return $this->_price(); break;
            case "value": return $this->_value(); break;
        }
    }
    
    public function getTotal($value) 
    {
        $tax = $this->getTaxRate();
        
        if($this->getType() == "gross") {
            return round($value * 100 / ( 100 + $tax ), 2);
        } else {
            return round($value * ( 1 + $tax / 100 ), 2 );
        }
    }
    
    protected function _value()
    {
        $object = new stdClass();
        
        $object->type = $this->getType();
        $object->price = $this->getPrice();
        $object->discount = $this->getDiscount();
        $object->rate = $this->getTaxRate();
        $object->total = null;
        $object->subtotal = null;
        $object->tax = null;
        
        $value = $object->price - $object->discount;
        
        if(!$this->isEnabled()) {
            $object->total = $value;
            $object->subtotal = $value;
            $object->tax = 0;
        } elseif($object->type == "gross") {
            $object->total = round($value, 2);
            $object->subtotal = round($value * 100 / ( 100 + $object->rate ), 2);
            $object->tax = $object->total - $object->subtotal;
        } else {
            $object->total = round($value * ( 1 + $object->rate / 100 ), 2 );
            $object->tax = $object->total - $value;
            $object->subtotal = $object->total - $object->tax;
        }

        return $object;
    }
}

