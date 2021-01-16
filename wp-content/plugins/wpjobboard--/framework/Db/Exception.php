<?php
/**
 * Description of Exception
 *
 * @author greg
 * @package 
 */

class Daq_Db_Exception extends Exception
{

    public function __construct($message = null, $code = 0, $query = null)
    {
        parent::__construct($message." occured in query: <code>".$query."</code>", $code);
    }

}

?>