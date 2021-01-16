<?php
/**
 * Description of Int
 *
 * @author greg
 * @package
 */

class Daq_Filter_WP_SanitizeUser implements Daq_Filter_Interface
{
    public function filter($value)
    {
        return sanitize_user($value);
    }
}

?>