<?php
/**
 * Description of Int
 *
 * @author greg
 * @package
 */

class Daq_Validate_Callback
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    private $_callback = null;


    public function __construct($callback)
    {
        if(!is_callable($callback)) {
            throw new Exception("Givan parameter is not a valid callback.");
        }

        $this->_callback = $callback;
    }

    public function isValid($value)
    {
        $return = call_user_func($this->_callback, $value);

        if($return === true) {
            return true;
        }

        $this->setError($return);
        return false;
    }
}

?>