<?php
/**
 * Description of JobType
 *
 * @author greg
 * @package
 */

class Wpjb_Model_Discount extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_discount";

    protected function _init()
    {

    }

    public function getTextDiscount()
    {
        if($this->type == 1) {
            return $this->discount."%";
        } else {
            $currency = Wpjb_List_Currency::getByCode($this->currency);
            $code = $currency['code'].' ';
            if($currency['symbol'] != null) {
                $code = $currency['symbol'];
            }
            return $code.$this->discount;
        }
    }
}

?>