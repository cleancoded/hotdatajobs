<?php

/**
 * Description of ${name}
 *
 * @author ${user}
 * @package 
 */
class Wpjb_List_Currency
{
    public static function getAll()
    {
        $all = array(
            1  => array('code'=>'AUD', 'name'=>__('Australian Dollars', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            2  => array('code'=>'CAD', 'name'=>__('Canadian Dollars', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            3  => array('code'=>'CHF', 'name'=>__('Swiss Franc', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            4  => array('code'=>'CZK', 'name'=>__('Czech Koruna', "wpjobboard"), 'symbol'=>'Kč', 'decimal'=>2),
            5  => array('code'=>'DKK', 'name'=>__('Danish Krone', "wpjobboard"), 'symbol'=>'kr', 'decimal'=>2),
            6  => array('code'=>'EUR', 'name'=>__('Euros', "wpjobboard"), 'symbol'=>'€', 'decimal'=>2),
            7  => array('code'=>'GBP', 'name'=>__('Pounds Sterling', "wpjobboard"), 'symbol'=>'£', 'decimal'=>2),
            8  => array('code'=>'HKD', 'name'=>__('Hong Kong Dollar', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            9  => array('code'=>'HUF', 'name'=>__('Hungarian Forint', "wpjobboard"), 'symbol'=>'Hf', 'decimal'=>2),
            10 => array('code'=>'ILS', 'name'=>__('Israeli Shekel', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            11 => array('code'=>'JPY', 'name'=>__('Japanese Yen', "wpjobboard"), 'symbol'=>'¥', 'decimal'=>0),
            12 => array('code'=>'MXN', 'name'=>__('Mexican Peso', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            13 => array('code'=>'NOK', 'name'=>__('Norwegian Krone', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            14 => array('code'=>'NZD', 'name'=>__('New Zealand Dollar', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            15 => array('code'=>'PLN', 'name'=>__('Polish Zloty', "wpjobboard"), 'symbol'=>'zł', 'decimal'=>2),
            16 => array('code'=>'SEK', 'name'=>__('Swedish Krona', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            17 => array('code'=>'SGD', 'name'=>__('Singapore Dollar', "wpjobboard"), 'symbol'=>null, 'decimal'=>2),
            18 => array('code'=>'USD', 'name'=>__('United States Dollars', "wpjobboard"), 'symbol'=>'$', 'decimal'=>2),
        );
        
        $all = apply_filters("wpjb_list_currency", $all);
        
        return $all;
    }
    
    /**
     * Returns list of currencies
     *
     * @return ArrayIterator 
     */
    public static function getList()
    {
        return new ArrayIterator(self::getAll());
    }

    /**
     * Returns array representing given currency
     *
     * @param string $id
     * @return array
     */
    public static function getCurrency($id)
    {
        $currency = self::getAll();
        if(isset($currency[$id])) {
            return $currency[$id];
        }
        return array();
    }
    
    public static function getByCode($code)
    {
        foreach(self::getAll() as $currency) {
            if($currency["code"] == $code) {
                return $currency;
            }
        }
        
        throw new Exception("Currency [$code] does not exist!");
    }

    public static function getCurrencySymbol($code, $space = " ")
    {
        $currency = self::getByCode($code);
        
        if(!is_null($currency['symbol'])) {
            return $currency['symbol'];
        } else {
            return $currency['code'].$space;
        }
    }
}
?>
