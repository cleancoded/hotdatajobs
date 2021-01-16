<?php
/**
 * Description of Ext
 *
 * @author greg
 * @package 
 */

class Daq_Validate_File_Ext
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_ext = array();

    public function getExt()
    {
        return join(", ", $this->_ext);
    }
    
    public function __construct($ext)
    {
        foreach(explode(",", $ext) as $e) {
            $this->_ext[] = strtolower(trim($e));
        }
    }

    public function isValid($value)
    {
        if($value['size'] == 0) {
            return true;
        }

        $part = explode(".", $value['name']);
        $ext = $part[count($part)-1];

        if(!in_array(strtolower($ext), $this->_ext)) {
            $this->setError(__("Unrecognized file extension.", "wpjobboard"));
            return false;
        }

        return true;
    }
}

?>