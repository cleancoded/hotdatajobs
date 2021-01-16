<?php
/**
 * Description of Int
 *
 * @author greg
 * @package 
 */

class Daq_Filter_NotNegativeInt implements Daq_Filter_Interface
{
    public function filter($value)
    {
        $value = (int)$value;
        if($value < 0) {
            $value = "0";
        }
        
        return (int)$value;
    }
}

?>