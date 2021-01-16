<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Payment
 *
 * @author Grzegorz
 */
class Wpjb_Module_Api_Payment extends Daq_Controller_Abstract
{
    public function payAction()
    {
        Wpjb_Project::getInstance()->assets->addScriptsFront();
        
        $request = Daq_Request::getInstance();
        $payment = Wpjb_Model_Payment::getFromHash($request->get("payment_hash"));
        $pricing = new Wpjb_Model_Pricing($payment->pricing_id);
        
        
        
        if(!$pricing instanceof Wpjb_Model_Pricing || !$pricing->exists()) {
            return;
        }
        
        switch($pricing->price_for) {
            case Wpjb_Model_Pricing::PRICE_SINGLE_JOB: $pricing_type = __("Job", "wpjobboard"); break;
            case Wpjb_Model_Pricing::PRICE_SINGLE_RESUME: $pricing_type = __("Resume Access", "wpjobboard"); break;
            case Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP: $pricing_type = __("Employer Membership", "wpjobboard"); break;
            case Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP: $pricing_type = __("Candidate Membership", "wpjobboard"); break;
        }
        
        $title = sprintf(__("Complete Order %s", "wpjobboard"), $payment->id());

        $shortcode = new Wpjb_Shortcode_Dynamic();
        $view = new stdClass();
        $view->pricing = $pricing;
        $view->gateways = Wpjb_Project::getInstance()->payment->getEnabled();
        $view->pricing_item = $pricing_type . " &quot;" . $pricing->title . "&quot;";
        $view->defaults = new Daq_Helper_Html("span", array(
            "id" => "wpjb-checkout-defaults",
            "class" => "wpjb-none",

            "data-payment_hash" => $payment->hash(),
            "data-pricing_id" => $pricing->id,
            "data-fullname" => $payment->fullname,
            "data-email" => $payment->email,

        ), " ");

        if(function_exists( 'is_rtl' ) && is_rtl()) {
            $text_direction = "rtl";
        } else {
            $text_direction = "ltr";
        }
?>
<!DOCTYPE html>
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono
-->
<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) && function_exists( 'is_rtl' ) ) language_attributes(); else echo "dir='$text_direction'"; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width">
	<title><?php echo $title ?></title>
	<style type="text/css">
		html {
			background: #f1f1f1;
		}
		body {
			background: #fff;
			color: #444;
			font-family: "Open Sans", sans-serif;
			margin: 2em auto;
			padding: 1em 2em;
			max-width: 700px;
			-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
			box-shadow: 0 1px 3px rgba(0,0,0,0.13);
		}
		ul li {
			margin-bottom: 10px;
			font-size: 14px ;
		}
		a {
			color: #0073aa;
		}
		a:hover,
		a:active {
			color: #00a0d2;
		}
		a:focus {
			color: #124964;
		    -webkit-box-shadow:
		    	0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, .8);
		    box-shadow:
		    	0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, .8);
			outline: none;
		}
		<?php
		if ( 'rtl' == $text_direction ) {
			echo 'body { font-family: Tahoma, Arial; }';
		}
		?>
	</style>
        <?php //wp_head() ?>
</head>
<body>
    <h1><?php echo esc_html($title) ?></h1>
    <?php $shortcode->view = $view; ?>
    <?php echo $shortcode->render("default", "payment"); ?>
    <?php wp_enqueue_script("wpjb-js") ?>
    <?php wp_enqueue_script("wpjb-payment") ?>
    <?php wp_enqueue_style("wpjb-css") ?>
    <?php wp_footer() ?>
</body>
</html>

<?php
    }
}

?>
