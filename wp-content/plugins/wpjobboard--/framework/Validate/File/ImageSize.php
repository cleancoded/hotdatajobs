<?php
/**
 * Description of ImageSize
 *
 * @author greg
 * @package 
 */

class Daq_Validate_File_ImageSize
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_width = null;

    protected $_height = null;

    public function __construct($width = null, $height = null)
    {
        $this->_width = $width;
        $this->_height = $height;
    }
    public function isValid($value)
    {
        if($value['size'] == 0) {
            return true;
        }
        
        $image = getimagesize($value['tmp_name']);
        if(!is_array($image)) {
            $this->setError(__("Unknown image file.", "wpjobboard"));
            return false;
        }
        list($width, $height, $type, $attr) = $image;

        $result = true;
        if(!is_null($this->_width) && $this->_width<$width) {
            $this->setError(__("Image width is too big.", "wpjobboard"));
            $result = false;
        }

        if(!is_null($this->_height) && $this->_height<$height) {
            $this->setError(__("Image height is too big.", "wpjobboard"));
            $result = false;
        }

        return true;
    }
}

?>