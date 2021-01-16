<?php
/**
 * Description of Email
 *
 * @author greg
 * @package 
 */

class Daq_Validate_StripePlanExists
    extends Daq_Validate_Abstract implements Daq_Validate_Interface
{
    public function isValid($value)
    {
        $r = Daq_Request::getInstance();
        $pricing_id = $r->get("id");
        $is_recurring = $r->post("is_recurring", null);
        
        if( $is_recurring == null ) {
            return true;
        }
        

        if( !class_exists( "Stripe" ) ) {
            include_once Wpjb_List_Path::getPath( "vendor" )."/Stripe/Stripe.php";
        }
        
        $stripe = new Wpjb_Payment_Stripe();
        \Stripe\Stripe::setApiKey( $stripe->conf( "secret_key" ) );
        
        $slug = sanitize_title( $value );
        $plan_id = preg_replace("([^A-z0-9\-]+)", "", $slug );
        
        // Edit
        if( isset( $pricing_id) && $pricing_id > 0 ) {
            $this_id = Wpjb_Model_MetaValue::getSingle( "pricing", "stripe_id", $pricing_id, true ); 
            if( $this_id == $plan_id ) {
                return true;
            }
        }
        
        try {
            $test = \Stripe\Plan::retrieve($plan_id);
            $this->setError(__("Plan with this name already exist in Stripe. Please use another name for your pricing.", "wpjobboard"));
            return false;
        } catch (Exception $e) {
            // Error - plan not exist
        }

        return true;
    }
}

?>