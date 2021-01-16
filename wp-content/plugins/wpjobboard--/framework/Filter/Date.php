<?php
/**
 * Description of Date
 *
 * @author greg
 * @package 
 */

class Daq_Filter_Date implements Daq_Filter_Interface
{
    private $_format = null;
    
    public function __construct($format) 
    {
        $this->_format = $format;
    }
    
    public function filter($value)
    {
        $ts = current_time("timestamp");
        $offset = get_option("gmt_offset");
        
        if(stripos($offset, "-") !== 0) {
            $offset = "-".$offset." hours";
        } else {
            $offset = str_replace("-", "+", $offset)." hours";
        }

        $date = date_create_from_format($this->_format, $value);
        
        if(!is_object($date)) {
            return "";
        }
        
        $date->setTime(date("H", $ts), date("i", $ts), date("s", $ts));
        $date->modify($offset);

        return $date->format("Y-m-d");
    }
}

?>