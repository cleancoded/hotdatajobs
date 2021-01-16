<?php
/**
 * Payment Model
 * 
 * Object handles data saving in wp_wpjb_payment table.
 *
 * @uses Daq_Db_OrmAbstract
 * 
 * @author Greg Winiarski
 * @package WPJobBoard
 */

class Wpjb_Model_Payment extends Daq_Db_OrmAbstract
{
    const JOB = 1;
    const RESUME = 2;
    const MEMBERSHIP = 3;
    const CAND_MEMBERSHIP = 4;
    
    const FOR_JOB = 1;
    const FOR_RESUMES = 2;
    const FOR_MEMBERSHIP = 3;
    const FOR_CAND_MEMBERSHIP = 4;

    /**
     * Model Name in DB
     *
     * @var string
     */
    protected $_name = "wpjb_payment";

    /**
     * Inits Payment Model
     * 
     * @since 4.0.0
     * @return void
     */
    protected function _init()
    {
        $this->_reference["user"] = array(
            "localId" => "user_id",
            "foreign" => "Wpjb_Model_User",
            "foreignId" => "ID",
            "type" => "ONE_TO_ONE"
        );
    }
    
    /**
     * Amount to pay as string
     * 
     * Returns amount to pay, formatted using wpjb_price() function.
     * 
     * @uses wpjb_price()
     * @since 4.0.0
     * @return string Formatted amount to pay
     */
    public function toPay()
    {
        if($this->payment_currency) {
            return wpjb_price($this->payment_sum, $this->payment_currency);
        } else {
            return null;
        }
    }

    /**
     * Amount paid as string
     * 
     * Returns amount already paid, formatted using wpjb_price() function.
     * 
     * @uses wpjb_price()
     * @since 4.0.0
     * @return string Formatted amount paid
     */
    public function paid()
    {
        if($this->payment_sum > 0) {
            return wpjb_price($this->payment_paid, $this->payment_currency);
        } else {
            return null;
        }
    }
    
    /**
     * Returns total price
     * 
     * Total price is to_pay + discount
     * 
     * @since 4.0.0
     * @return float Total price
     */
    public function getPrice()
    {
        return ($this->payment_sum+$this->payment_discount);
    }
    
    /**
     * Returns discount value
     * 
     * @since 4.0.0
     * @return float Discount value
     */
    public function getDiscount()
    {
        return $this->payment_discount;
    }
    
    /**
     * Returns payment value
     * 
     * @since 4.0.0
     * @return float Total to pay
     */
    public function getTotal()
    {
        return $this->payment_sum;
    }
    
    /**
     * Exexcutes and action when payment is accepted.
     * 
     * This function is executed both when admin manually accepts payment and 
     * when payment is accepted automatically (for example by PayPal IPN)
     * 
     * @since 4.0.0
     * @return void
     */
    public function accepted()
    {
        $id = $this->object_id;
        $object = null;
        
        if($this->object_type == self::JOB) {
            $object = new Wpjb_Model_Job($id);
        } elseif($this->object_type == self::RESUME) {
            $object = new Wpjb_Model_Resume($id);
        } elseif($this->object_type == self::MEMBERSHIP) {
            $object = new Wpjb_Model_Membership($id);
        } elseif($this->object_type == self::CAND_MEMBERSHIP) {
            $object = new Wpjb_Model_Membership($id);
        }
        
        if($object && $object->exists()) {
            $accepted = $object->paymentAccepted($this);
        }
        
        $accepted = apply_filters("wpjb_payment_accept", $accepted, $this);
        
        if($accepted) {
            do_action("wpjb_payment_complete", $this, $object);
        }
        
        /*
        if(!$object->id) {
            return;
        }
        
        $object->paymentAccepted($this);
        */
        
    }
    
    /**
     * Saves log message in DB
     * 
     * Note. The message is automatically saved in DB using self::save(), you
     * do not need to call this method separately.
     * 
     * @uses self::save()
     * 
     * @param string $message Message to log in DB
     * @return void
     */
    public function log($message)
    {
        if(!$this->exists()) {
            return;
        }
        
        $log = current_time("mysql") . " — " . $message . "\r\n";
        $this->message = $this->message . $log;
        $this->save();
    }
    
    /**
     * Returns human readable payment ID
     * 
     * Uses internal ID and formats it as "#0000XXX"
     * 
     * @since 4.4.0
     * @return string Formatted ID
     */
    public function id()
    {
        return "#" . str_pad($this->id, 7, "0", STR_PAD_LEFT);
    }
    
    /**
     * Returns payment status label
     * 
     * Returns status label for *this* payment object. 
     * 
     * @uses wpjb_get_payment_status
     * @return string Current status label or null
     */
    public function status()
    {
        $status = wpjb_get_payment_status($this->status);
        
        if(isset($status["label"])) {
            return $status["label"];
        } else {
            return null;
        }
    }
    
    /**
     * Returns payment data
     * 
     * This function returns payment data formatted as array (used mainly in
     * email templates).
     * 
     * @since 4.4.0
     * @return array Payment data
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data["url"] = $this->url();
        $data["admin_url"] = wpjb_admin_url("payment", "edit", $this->id);
        $data["readable_id"] = $this->id();
        $data["readable_to_pay"] = $this->toPay();
        $data["readable_paid"] = $this->paid();
        $data["readable_status"] = $this->status();
        
        return $data;
    }
    
    /**
     * Returns success messages for payment.
     * 
     * The returned message depends on payment object_type
     * 
     * @since 4.4.0
     * @return array List of success messages
     */
    public function successMessages()
    {
        $info = array();
        
        switch($this->object_type) {
            case self::JOB:
                $job = new Wpjb_Model_Job($this->object_id);
                $moderate = $job->is_active && $job->is_approved;
                $info[] = __("<strong>Thank you for submitting your job listing.</strong>", "wpjobboard");
                if(!$moderate) {
                    $info[] = __("Your job posting is being moderated. We will email you once it will be live.", "wpjobboard");
                } else {
                    $info[] = sprintf(__('Your job posting is now live. <a href="%s">Click here to view it</a>.', "wpjobboard"), $job->url());
                }
                break;
            case self::RESUME:
                $resume = new Wpjb_Model_Resume($this->object_id);
                $redirect_url = $resume->url();
                $glue = stripos($redirect_url, "?") === -1 ? "?" : "&";
                
                if(!get_current_user_id()) {
                    $redirect_url .= $glue . "hash=" . md5("{$this->id}|{$this->object_id}|{$this->object_type}|{$this->paid_at}");
                }
                $info[] = __("<strong>Thank you for submitting your order.</strong>", "wpjobboard");
                $info[] = sprintf(__('Access to resume details has been granted. <a href="%s">Click here to view it</a>.', "wpjobboard"), $redirect_url);
                break;
            case self::MEMBERSHIP:
                $info[] = __("<strong>Thank you for submitting your order.</strong>", "wpjobboard");
                $info[] = sprintf(__('Your membership is now active. <a href="%s">Go back to Employer Dashboard</a>.', "wpjobboard"), wpjb_link_to("employer_home"));
                break;
            case self::CAND_MEMBERSHIP:
                $info[] = __("<strong>Thank you for submitting your order.</strong>", "wpjobboard");
                $info[] = sprintf(__('Your membership is now active. <a href="%s">Go back to Candidate Dashboard</a>.', "wpjobboard"), wpjr_link_to("myresume_home"));
            default:
                $info = apply_filters("wpjb_payment_success_messages", $info, $this);
                break;
        }
        
        return $info;
    }
    
    /**
     * Returns payment hash
     * 
     * Payment hash is a string which allows to uniquely identify a payment.
     * The hash is mainly used to allow users to complete payment.
     * 
     * @since 4.4.0
     * @throw Exception If payment does not exist
     * @return string Payment unique hash
     */
    public function hash()
    {
        if(!$this->exists()) {
            throw new Exception("You need to save Payment before using this method.");
        }
        
        $arr = array($this->id, $this->object_id, $this->object_type, $this->created_at);
        $hash = $this->id . "-" . sha1(join("|", $arr));
        
        return apply_filters("wpjb_payment_hash", $hash, $this);
    }
    
    /**
     * Returns payment identified by $hash
     * 
     * Functions tries to find a payment method which is identified by provided
     * hash, the hash should be in format XX-YYYY where
     * XX - is payment ID
     * YYYY - 40 character long payment hash generated using self::hash() function
     * 
     * @see self::hash()
     * 
     * @since 4.4.0
     * @param string $hash Payment hash
     * @return Wpjb_Model_Payment Returns $payment identified by $hash or NULL
     */
    public static function getFromHash($hash)
    {
        list($id, $hs) = explode("-", $hash);
        
        $payment = new Wpjb_Model_Payment($id);
        
        if(!$payment->exists()) {
            return null;
        }
        
        if($hash == $payment->hash()) {
            return $payment;
        } else {
            return null;
        }
    }
    
    /**
     * Returns URL to payment form
     * 
     * By accessing the URL user can finish his payment.
     * 
     * @since 4.4.0
     * @return string URL to payment form
     */
    public function url()
    {
        if(!$this->exists()) {
            return "";
        } else {
            return wpjb_api_url("payment/pay", array("payment_hash"=>$this->hash()));
        }
        
    }
}
