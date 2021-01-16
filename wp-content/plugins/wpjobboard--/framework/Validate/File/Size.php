<?php
/**
 * Description of Ext
 *
 * @author greg
 * @package
 */

class Daq_Validate_File_Size
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_size = array();

    public function getSize()
    {
        return $this->_size;
    }
    
    public function __construct($size)
    {
        $this->_size = $size;
    }

    public function isValid($value)
    {
        if($this->_size < $value['size']) {
            $this->setError(__("File is too big.", "wpjobboard"));
            return false;
        }

        return true;
    }
}

?>