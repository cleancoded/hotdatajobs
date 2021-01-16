<?php
/**
 * Description of Tags
 *
 * @author greg
 * @package 
 */

class Daq_Filter_Tags implements Daq_Filter_Interface
{
    protected $_allowedTags = "";

    public function __construct($allowed = null)
    {
        if($allowed !== null) {
            $this->_allowedTags = $allowed;
        }
    }

    public function filter($value)
    {
        return strip_tags($value, $this->_allowedTags);
    }
}

?>