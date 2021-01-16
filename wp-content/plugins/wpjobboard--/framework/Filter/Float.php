<?php
/**
 * Description of Int
 *
 * @author greg
 * @package
 */

class Daq_Filter_Float implements Daq_Filter_Interface
{
    private $_decimalSeparator = ".";

    private $_decimalPlaces = 2;

    private $_thousandSeparator = "";

    public function __construct($dSeparator = ".", $dPlaces = 2, $tSeparator = "")
    {
        $this->_decimalSeparator = $dSeparator;
        $this->_decimalPlaces = $dPlaces;
        $this->_thousandSeparator = $tSeparator;
    }

    public function filter($value)
    {
        $ds = $this->_decimalSeparator;
        $dp = $this->_decimalPlaces;
        $ts = $this->_thousandSeparator;

        return (float)number_format($value, $dp, $ds, $ts);
    }
}

?>