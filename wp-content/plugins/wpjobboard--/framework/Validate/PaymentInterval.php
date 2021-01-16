<?php
/**
 * Description of Email
 *
 * @author greg
 * @package 
 */

class Daq_Validate_PaymentInterval
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    public function isValid($value)
    {
        if( Daq_Request::getInstance()->post("is_recurring") != 1 ) {
            return true;
        }
        
        $period = Daq_Request::getInstance()->post("is_recurring_interval");
        $visibility = Daq_Request::getInstance()->post("visible");
        
        $v = (string)((int)intval($value));
        
        if( $v != $value ) {
            $this->setError(__("Interval count has to be a integer grather than 0.", "wpjobboard"));
            return false;
        }
        
        if( $value < 1) {
            $this->setError(__("Interval can not be lower than 1.", "wpjobboard"));
            return false;
        }
        
        if( $period == 'week' && $value > 52 ) {
            $this->setError(__("Period for weekly interval can not be grather than 52 weeks.", "wpjobboard"));
            return false;
        }
        
        if( $period == 'month' && $value > 12) {
            $this->setError(__("Period for monthly interval can not be grather than 12 months.", "wpjobboard"));
            return false;
        }
        
        if( $period == 'year' && $value != 1) {
            $this->setError(__("Only possible value for yearly interval is 1 year.", "wpjobboard"));
            return false;
        }
        
        $recurring_in_days = $value; 
        switch( $period ) {
            case "week":
                $recurring_in_days *= 7;
                break;
            case "month":
                $recurring_in_days *= 30;
                break;
            case "year":
                $recurring_in_days *= 365;
                break;
        }
        
        if( $recurring_in_days != $visibility ) {
            $this->setError( sprintf( __("Membership visibility (%d days) is different from payment cycle (%d %s = %d days).", "wpjobboard"), $visibility, Daq_Request::getInstance()->post('is_recurring_interval_count'), Daq_Request::getInstance()->post("is_recurring_interval"), $recurring_in_days ) );
            return false;
        }
        
        return true;
    }
}

?>