<?php
/**
 * Description of Interface
 *
 * @author greg
 * @package 
 */

interface Daq_Validate_Interface
{
    public function isValid($value);

    public function getErrors();

    public function setValue($value);
}

?>