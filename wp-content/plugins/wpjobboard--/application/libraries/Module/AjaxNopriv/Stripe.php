<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stripe
 *
 * @author Mark
 */
class Wpjb_Module_AjaxNopriv_Stripe
{
    
        
    public function subscriptionRenewAction() 
    {
        //$stripe = new Wpjb_Payment_Stripe();

        // Retrieve the request's body and parse it as JSON
        $input = @file_get_contents("php://input");
        $event_json = json_decode($input);

        foreach($event_json->data->object->lines->data as $object) {
            $subscription_id = $object->id;
            
            // Find Membership
            $q = Daq_Db_Query::create();
            $m = $q->select()->from("Wpjb_Model_MetaValue t")->where("t.value = ?", $subscription_id)->execute();
            
            if( !isset( $mv[0] ) || empty( $mv[0] ) ) {
                continue;
            }
            
            $mv = $m[0];
            $original_membership = new Wpjb_Model_Membership($mv->object_id);
            $new_membership = clone $original_membership;

            $pricing = new Wpjb_Model_Payment($original_membership->package_id);
            $duration = $pricing->meta->visible->value();
            if(!is_numeric($duration)) {
                $duration = 30;
            }
            
            // Copy with new date
            $new_membership->id = null;
            $new_membership->started_at = $new_membership->expires_at;
            $new_membership->expires_at = wpjb_time("today +".$duration." day");
            $new_membership->save();
                     
            $user = get_userdata( $new_membership->user_id );
            
            // Save Payment (For History Reason)
            $payment = new Wpjb_Model_Payment();
            $payment->pricing_id = $new_membership->package_id;
            $payment->object_type = 3;
            $payment->object_id = $new_membership->id;
            $payment->user_ip = $_SERVER['REMOTE_ADDR'];
            $payment->user_id = $new_membership->user_id;
            $payment->fullname = $user->first_name . " " . $user->last_name;
            $payment->email = $user->user_email;
            $payment->external_id = $subscription_id; 
            $payment->status = 1;
            $payment->message = __("Subcription automatic payment", "wpjobboard");
            $payment->created_at = current_time("mysql", 1);
            $payment->paid_at = current_time("mysql", 1);
            $payment->engine = "Stripe";
            $payment->payment_sum = $object->amount / 100;
            $payment->payment_paid = $object->amount / 100;
            $payment->payment_discount = 0;
            $payment->payment_currency = $object->currency;
            $payment->params = "";
            $payment->save();
        }

        wp_die();
    }
    

}

