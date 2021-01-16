<?php
/**
 * Description of Date
 *
 * @author greg
 * @package
 */

class Daq_Validate_WP_Email
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    protected $_data = array();
    
    public function __construct($data = array())
    {
        if(!isset($data["exclude"])) {
            $data["exclude"] = null;
        }

        $this->_data = $data;
    }

    public function isValid($value)
    {
        $user_email = $value;
        
        if ( $user_email == '' ) {
            $this->setError(__('Please type your e-mail address.', "wpjobboard") );
            return false;
        } elseif ( ! is_email( $user_email ) ) {
            $this->setError(__('The email address isn&#8217;t correct.', "wpjobboard") );
            $user_email = '';
            return false;
        } 
        
        $exists = email_exists( $user_email );

        if ($exists && $this->_data["exclude"] != $exists) {
            $this->setError(__('This email is already registered, please choose another one.', "wpjobboard") );
            return false;
        }

        return true;
    }
}
