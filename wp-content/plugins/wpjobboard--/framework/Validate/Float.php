<?php
/**
 * Description of Int
 *
 * @author greg
 * @package
 */

class Daq_Validate_Float
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    private $_min = null;

    private $_max = null;

    public function __construct($min = null, $max = null)
    {
        if(is_null($min) || is_float($min) || is_int($min)) {
            $this->_min = $min;
        } else {
            throw new Exception('$min is not Float nor Null');
        }

        if(is_null($max) || is_float($max) || is_int($max)) {
            $this->_max = $max;
        } else {
            throw new Exception('$max is not Float nor Null');
        }
    }

    public function isValid($value)
    {
        $return = true;
        $v = (string)((float)$value);

        if($value != $v) {
            $this->setError(__("Value is not a Float.", "wpjobboard"));
            return false;
        }

        if($this->_min !== null && $value < $this->_min) {
            $this->setError(__("Value is too small.", "wpjobboard"));
            $return = false;
        }

        if($this->_max !== null && $value > $this->_max) {
            $this->setError(__("Value is too big.", "wpjobboard"));
            $return = false;
        }

        return $return;
    }
}

?>