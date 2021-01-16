<?php
/**
 * Description of Option
 *
 * @author greg
 * @package 
 */

class Daq_Widget_Option
{
    private $_option = null;

    public function __construct(stdClass $raw)
    {
        $this->_option = $raw;
    }

    public function get($key, $default = null)
    {
        if(isset($this->_option->$key)) {
            return $this->_option->$key;
        } else {
            return $default;
        }
    }

    public function __get($key)
    {
        return $this->get($key);
    }
}

?>