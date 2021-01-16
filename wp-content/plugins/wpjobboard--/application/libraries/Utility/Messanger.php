<?php
/**
 * Description of Messanger
 *
 * @author greg
 * @package
 */

class Wpjb_Utility_Messanger
{
    public static function parse(Wpjb_Model_Email $mail, Wpjb_Model_Job $job, array $append)
    {
        $active = "active";
        if(!$job->is_active && !$job->is_approved) {
            $active = "inactive";
        }

        $time = strtotime ( $job->job_created_at );
        $newdate = strtotime ('+'.$job->job_visible.' day', $time) ;
        $expiration = date ( "Y-m-d H:i:s" , $newdate );

        /* @var $job Wpjb_Model_Job */

        $exchangeArray = array(
          "id" => $job->getId(),
          "created" => $job->job_created_at,
          "visible" => $job->job_visible,
          "price" => $job->paymentAmount(),
          "paid" => $job->paymentPaid(),
          //"promo_code" => $job->promoCode,
          "discount" => $job->paymentDiscount(),
          "company" => $job->company_name,
          "location" => $job->locationToString(),
          "email" => $job->company_email,
          "position_title" => $job->job_title,
          "listing_type" => $job->getType(true)->title,
          "category" => $job->getCategory(true)->title,
          "active" => $active,
          "url" => Wpjb_Project::getInstance()->getUrl()."/".Wpjb_Project::getInstance()->router()->linkTo("job", $job),
          //"pay_paypal" => Core::url("pay/paypal/" . $job->id),
          "expiration" => $expiration,
        );
        foreach($append as $k => $v) {
            $exchangeArray[$k] = $v;
        }

        $body = $mail->mail_body;
        $mail_title = $mail->mail_title;
        foreach($exchangeArray as $key => $value) {
            //$v = esc_html($value, false);
            $v = $value;
            $body = str_replace('{$'.$key.'}', $v, $body);
            $mail_title = str_replace('{$'.$key.'}', $v, $mail_title);
        }

        return array($mail_title, $body);
    }

    public static function send($key, Wpjb_Model_Job $job, $append = array())
    {
        $mail = new Wpjb_Model_Email($key);

        list($title, $body) = self::parse($mail, $job, $append);

        if($mail->sent_to == 1) {
            $sendTo = $mail->mail_from;
        } else {
            $sendTo = $job->company_email;
        }

        if($key == 7) {
            $sendTo = $append['alert_email'];
        }
        if($key == 8) {
            $sendTo = $append['applicant_email'];
        }

        $headers = "From: ". $mail->mail_from;
        $headers.= " <" .  $mail->mail_from . ">\r\n";

        wp_mail($sendTo, $title, $body, $headers);
    }
}

?>