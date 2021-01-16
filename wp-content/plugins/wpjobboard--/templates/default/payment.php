<?php

/**
 * Save job
 * 
 * Template displayed when job is being saved
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 */

 /* @var $payment Object Payment form */
 /* @var $payment_form String */

$taxer = new Wpjb_Utility_Taxer();
$taxer->setPrice($pricing->price);


?>

<?php wp_enqueue_script( 'wpjb-payment' ) ?>

<div class="wpjb wpjb-page-default-payment">

    <?php wpjb_flash(); ?>
    <?php if(isset($breadcrumbs)) wpjb_breadcrumbs($breadcrumbs) ?>
    
    <?php echo $defaults ?>
    
    <div id="wpjb-checkout" class="wpjb-grid wpjb-grid-closed-top">
        <div class="wpjb-grid-row" style="background-color: whitesmoke; font-weight: bold">
            <div class="wpjb-col-65"><?php _e("Item", "wpjobboard") ?></div>
            <div class="wpjb-col-30 wpjb-grid-col-right"><?php _e("Price", "wpjobboard") ?></div>
        </div> 
        <div class="wpjb-grid-row wpjb-payment-item">
            <div class="wpjb-col-65"><?php esc_html_e($pricing_item) ?></div>
            <div class="wpjb-col-30 wpjb-grid-col-right"><span class="wpjb-value" data-price-default="<?php esc_html_e(wpjb_price($taxer->value->price, $pricing->currency)) ?>" data-pricing-id="<?php echo $pricing->id ?>"><?php esc_html_e(wpjb_price($taxer->value->price, $pricing->currency)) ?></span></div>
        </div>   
        <div class="wpjb-grid-row wpjb-payment-discount wpjb-none">
            <div class="wpjb-col-65"><?php _e("Discount", "wpjobboard") ?></strong></div>
            <div class="wpjb-col-30 wpjb-grid-col-right"><span class="wpjb-value" data-price-default="<?php esc_html_e(wpjb_price($taxer->value->discount, $pricing->currency)) ?>"><?php esc_html_e(wpjb_price($taxer->value->discount, $pricing->currency)) ?></span></div>
        </div>            
        <div class="wpjb-grid-row wpjb-payment-total">
            <div class="wpjb-col-65" >
                <?php if($taxer->isEnabled()): ?>
                <span class="wpjb-payment-tax-label"><?php _e("Subtotal", "wpjobboard") ?></span>
                <span class="wpjb-payment-tax-label"><?php echo sprintf(__('%1$s @ %2$s', "wpjobboard"), __("Tax", "wpjobboard"), intval($taxer->value->rate)."%") ?></span>
                <?php endif; ?>
                <strong style="font-size: larger"><?php _e("Total", "wpjobboard") ?></strong>
            </div>
            <div class="wpjb-col-30 wpjb-grid-col-right">
                <?php if($taxer->isEnabled()): ?>
                <span class="wpjb-payment-tax-label wpjb-value-subtotal" data-price-default="<?php esc_html_e(wpjb_price($taxer->value->subtotal, $pricing->currency)) ?>"><?php esc_html_e(wpjb_price($taxer->value->subtotal, $pricing->currency)) ?></span>
                <span class="wpjb-payment-tax-label wpjb-value-tax" data-price-default="<?php esc_html_e(wpjb_price($taxer->value->tax, $pricing->currency)) ?>"><?php esc_html_e(wpjb_price($taxer->value->tax, $pricing->currency)) ?></span>
                <?php endif; ?>
                <strong class="wpjb-value" style="font-size:larger" data-price-default="<?php esc_html_e(wpjb_price($taxer->value->total, $pricing->currency)) ?>"><?php esc_html_e(wpjb_price($taxer->value->total, $pricing->currency)) ?></strong>
            </div>
        </div>
    </div>
    
    <div class="" style="padding:0 1% 0 1%; border-color: whitesmoke; margin-top:30px; margin-bottom: 30px">
        
        <span class="wpjb-enter-discount-start">
            <?php _e("Have a discount code?", "wpjobboard") ?>
            <a href="#" class="wpjb-enter-discount" rel="nofollow"><?php _e("Click to enter it.", "wpjobboard") ?></a>
        </span>
        
        <span class="wpjb-none wpjb-enter-discount-applied">
            <span class="wpjb-glyphs wpjb-icon-ok"></span>
            <span class="wpjb-enter-discount-msg"></span>
            <a href="#" class="wpjb-enter-discount" rel="nofollow"><?php _e("Change", "wpjobboard") ?></a>
        </span>
        
        <div class="wpjb-enter-discount-form wpjb-none" style="margin: 1em 0 0 0 ">
            <input name="discount" class="wpjb-enter-discount-value" type="text" value="" autocomplete="off" />
            <a href="#" class="wpjb-enter-discount-apply wpjb-button"><?php _e("Apply", "wpjobboard") ?></a>
            <span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin" style="visibility:hidden; font-size: 18px; vertical-align: middle; color: black;"></span>
            
            <span class="wpjb-none wpjb-enter-discount-failed">
                <span class="wpjb-glyphs wpjb-icon-cancel-circled"></span>
                <span class="wpjb-enter-discount-msg"></span>
            </span>
        </div>
        
    </div>
    
    <?php if( isset( $pricing->meta->is_recurring ) && $pricing->meta->is_recurring->value() ): ?>
    <div id="recurring-agree-box" style="padding:0 1% 0 1%; border-color: whitesmoke; margin-top:30px; margin-bottom: 30px">
        <label for="recurring-agree" class="wpjb-cute-input wpjb-cute-checkbox">
            <input type="checkbox" value="5" name="recurring-agree" class="wpjb-ls-type" id="recurring-agree">
            <div class="wpjb-cute-input-indicator"></div>
            <span class="wpjb-input-description" style="padding-left: 25px;"> 
                <?php if( wpjb_conf('urls_link_membership_pricing') == get_the_ID() ): ?>
                <?php printf( __('I acknowledge and agree that I am purchasing a subscription. The subscription will be charged automatically <strong>every %d day(s)</strong> until cancelled. You can cancel subscription at any time from your <a href="%s">Employer Dashboard</a>.', "wpjobboard"), $pricing->meta->visible->value(), wpjb_link_to("employer_home") ); ?>
                <?php else: ?>
                <?php printf( __('I acknowledge and agree that I am purchasing a subscription. The subscription will be charged automatically <strong>every %d day(s)</strong> until cancelled. You can cancel subscription at any time from your <a href="%s">Candidate Dashboard</a>.', "wpjobboard"), $pricing->meta->visible->value(), wpjr_link_to("myresume_home") ); ?>
                <?php endif; ?>
            </span>
        </label>
    </div>
    <?php endif; ?>

    <div class="wpjb-checkout-free wpjb-none">
        <div class="wpjb-flash-info">
            <?php printf(__('<strong>100%% discount applied.</strong><br/> Click "%1$s" button to skip payment and complete your order.'), __("Place Order", "wpjobboard")) ?>
        </div>
    </div>
    
    <?php if(empty($gateways)): ?>
    
    <div class="wpjb-flash-error">
        <?php _e("No payment methods enabled!", "wpjobboard") ?>
    </div>
    
    <?php else: ?>
    
    <ul id="wpjb-checkout-gateway" class="wpjb-tabs wpjb-tabs-reverse">
        <?php $is_first = true ?>
        <?php foreach($gateways as $gclass): ?>
        <?php if( isset( $pricing->meta->is_recurring ) && $pricing->meta->is_recurring->value() && $gclass != "Wpjb_Payment_Stripe" ): continue; endif; ?>
        <?php $gateway = new $gclass; ?>
        <li class="wpjb-tab-link <?php echo $is_first ? 'current' : '' ?>" data-gateway="<?php echo $gclass ?>">
            <?php if($gateway->getIconFrontend()): ?>
            <span class="wpjb-glyphs <?php echo $gateway->getIconFrontend() ?>"></span>
            <?php endif; ?>
            <a href="#1234"><?php esc_html_e($gateway->getCustomTitle()) ?></a> 
        </li>
        <?php $is_first = false; ?>
        <?php endforeach; ?>
    </ul>
    
    
    <div class="wpjb-grid wpjb-grid-compact wpjb-tab-content wpjb-checkout-form" style="padding:1em 15px 1em 15px">
        <span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin"></span>
    </div>
    
    <div id="wpjb-checkout-success" class="wpjb-none"></div>
    
    <div class="wpjb-place-order-wrap" style="margin-top:30px;">
        <a href="#" class="wpjb-button wpjb-place-order"><?php _e("Place Order", "wpjobboard") ?></a>
        <span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin" style="visibility:hidden; font-size: 18px; vertical-align: middle; color: black;"></span>
    </div>

    <?php endif; ?>
</div>

