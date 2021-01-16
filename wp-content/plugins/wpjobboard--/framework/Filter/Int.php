<?php
/**
 * Description of Int
 *
 * @author greg
 * @package 
 */

class Daq_Filter_Int implements Daq_Filter_Interface
{
    public function filter($value)
    {
        return (int)$value;
    }
}

?>