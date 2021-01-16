<?php 

/**
 * Payment history item
 * 
 * This template is responsible for displaying job list item on job list page
 * (template index.php) it is alos used in live search
 * 
 * @author Mark Winiarski
 * @package Templates
 * @subpackage JobBoard
 */

 /* @var $job Wpjb_Model_Job */

?>

    <div class="wpjb-grid-row wpjb-manage-item">
    
        
            
        <div class="wpjb-grid-col wpjb-col-logo">

            
            <div class="wpjb-img-50 wpjb-icon-none">
                <?php if( Wpjb_Model_Payment::JOB == $item->object_type): ?>
                <span class="wpjb-glyphs wpjb-icon-briefcase wpjb-icon-50"></span>
                <?php elseif( Wpjb_Model_Payment::RESUME == $item->object_type): ?>
                <span class="wpjb-glyphs wpjb-icon-user wpjb-icon-50"></span>
                <?php elseif( Wpjb_Model_Payment::MEMBERSHIP == $item->object_type): ?>
                <span class="wpjb-glyphs wpjb-icon-users wpjb-icon-50"></span>
                <?php elseif( Wpjb_Model_Payment::CAND_MEMBERSHIP == $item->object_type): ?>
                <span class="wpjb-glyphs wpjb-icon-users wpjb-icon-50"></span>
                <?php endif; ?>
            </div>
            
        </div>
        
        <div class="wpjb-grid-col wpjb-col-main wpjb-col-title">
            
            <div class="wpjb-line-major">
                
                <strong><?php esc_html_e($item->id()) ?></strong>
                <?php $status = wpjb_get_payment_status($item->status) ?>
                <?php if($item->status == 2): ?>
                    <?php $status_bulb = '#86c202'; ?>
                <?php elseif($item->status == 3): ?>
                    <?php $status_bulb = '#c9011e'; ?>
                <?php elseif($item->status == 4): ?>
                    <?php $status_bulb = '#aaaaaa'; ?>
                <?php else: ?>
                    <?php $status_bulb = '#ffb119'; ?>
                <?php endif; ?>
                <span class="wpjb-bulb" style="background-color: <?php echo esc_html($status_bulb); ?>">
                    <?php esc_html_e($status["label"]) ?> 
                </span>
                     
                <span class="wpjb-job_type wpjb-sub-title">
                    <span class="wpjb-glyphs wpjb-icon-clock" title="<?php _e('Creation Date', 'wpjobboard'); ?>"></span>
                    <abbr title="<?php _e('Creation Date', 'wpjobboard'); ?>"><?php echo wpjb_date($item->created_at); ?></abbr>
                </span>
            </div>
            
            <div class="wpjb-line-minor">
                <small>
                    <a href="#" class="wpjb-manage-action-more">
                        <span class="wpjb-glyphs wpjb-icon-down-open"></span>
                        <?php _e("Payment Details", "wpjobboard"); ?>
                    </a> 
                    <?php if( in_array($item->status, array(1, 3) ) ): ?>
                    |
                    <a href="<?php wpjb_link_to("payment") ?>?pay_now=<?php echo esc_html( intval( $item->id ) ); ?>" class="">
                        <span class="wpjb-glyphs "></span>
                        <span style="color: red;"><?php _e("Pay Now", "wpjobboard"); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php do_action("wpjb_tpl_pricing_history_actions", $item->id); ?>
                </small>   

                
                <span class="wpjb-sub wpjb-sub-opaque wpjb-sub-right wpjb-job_created_at">
                    <?php if(strtolower($item->engine) == 'paypal'): ?>
                    <span class="wpjb-glyphs wpjb-icon-paypal" title="<?php _e("Paid using: PayPal", "wpjobboard"); ?>"></span>
                    <?php elseif(strtolower($item->engine) == 'stripe'): ?>
                    <span class="wpjb-glyphs wpjb-icon-cc-stripe" title="<?php _e("Paid using: Stripe", "wpjobboard"); ?>"></span>
                    <?php elseif(strtolower($item->engine) == 'cash'): ?>
                    <span class="wpjb-glyphs wpjb-icon-money" title="<?php _e("Paid using: Cash", "wpjobboard"); ?>"></span>
                    <?php else: ?>
                    <span class="wpjb-glyphs wpjb-icon-question-circle-o" title="<?php _e("Unknown Payment Method", "wpjobboard"); ?>"></span>
                    <?php endif; ?>
                    <?php echo wpjb_price($item->payment_sum, $item->payment_currency) ?>
                    <!--small><?php echo esc_html($item->engine); ?></small-->
                </span>
                
            </div>
        </div>
        
        <div class="wpjb-manage-actions-more">
            <?php
                switch($item->object_type) {
                    case Wpjb_Model_Payment::JOB: $object = new Wpjb_Model_Job($item->object_id); break;
                    case Wpjb_Model_Payment::RESUME: $object = new Wpjb_Model_Resume($item->object_id); break;
                    case Wpjb_Model_Payment::MEMBERSHIP: $object = new Wpjb_Model_Membership($item->object_id); break;
                    case Wpjb_Model_Payment::CAND_MEMBERSHIP: $object = new Wpjb_Model_Membership($item->object_id); break;
                    default: $object = null;
                }
            ?>
            
            <div class="wpjb-grid wpjb-manage-action wpjb-col-100" style="height: auto; background-color: whitesmoke;">
                <div class="wpjb-grid-row">
                    <div class="wpjb-grid-col wpjb-col-40"><?php _e("Payment For", "wpjobboard") ?></div>
                    
                        <?php if(!in_array($item->object_type, array(4))): ?>
                            <?php do_action("wpjb_payment_for", $item) ?>
                            <?php elseif(!$object || !$object->exists()): ?>
                            <?php _e("Deleted", "wpjobboard") ?>
                            <?php elseif($item->object_type == Wpjb_Model_Payment::JOB): ?>
                            <div class="wpjb-grid-col wpjb-col-60 wpjb-glyphs wpjb-icon-briefcase">
                            <a href="<?php echo esc_url(wpjb_link_to('job_edit', $object)) ?>"><?php esc_html_e($object->job_title) ?></a>
                            </div>
                            <?php elseif($item->object_type == Wpjb_Model_Payment::RESUME): ?>
                            <div class="wpjb-grid-col wpjb-col-60 wpjb-glyphs wpjb-icon-user">
                            <?php if($item->is_active == 1 && $item->is_public == 1): ?> 
                            <a href="<?php echo esc_url(wpjr_link_to('resume', $object)) ?>"><?php esc_html_e($object->getSearch(true)->fullname) ?></a>
                            <?php else: ?>
                            <?php esc_html_e($object->getSearch(true)->fullname) ?>
                            <?php endif; ?>
                            </div>
                            <?php elseif($item->object_type == Wpjb_Model_Payment::MEMBERSHIP): ?>
                            <div class="wpjb-grid-col wpjb-col-60 wpjb-glyphs wpjb-icon-users">
                            <?php esc_html_e($object->getPricing(true)->title) ?>
                            </div>
                            <?php elseif($item->object_type == Wpjb_Model_Payment::CAND_MEMBERSHIP): ?>
                            <div class="wpjb-grid-col wpjb-col-60 wpjb-glyphs wpjb-icon-users">
                            <?php esc_html_e($object->getPricing(true)->title) ?>
                            </div>
                            <?php else: ?>
                            <strong>&nbsp;</strong>
                        <?php endif; ?>
                </div>
                <?php if( !in_array($item->status, array(1, 3) ) ): ?>
                <div class="wpjb-grid-row">
                    <div class="wpjb-grid-col wpjb-col-40"><?php _e("Transaction ID", "wpjobboard") ?></div>
                    <div class="wpjb-grid-col wpjb-col-60 wpjb-glyphs wpjb-icon-id-card" >
                    <?php  echo esc_html($item->external_id); ?>
                    </div>
                </div>
                <div class="wpjb-grid-row">
                    <div class="wpjb-grid-col wpjb-col-40"><?php _e("Paid", "wpjobboard") ?></div>
                    <div class="wpjb-grid-col wpjb-col-60 wpjb-glyphs wpjb-icon-thumbs-up" >
                    <?php printf("<b>%s</b> on: %s", wpjb_price($item->payment_paid, $item->payment_currency), wpjb_date($item->paid_at) ); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php do_action("wpjb_tpl_pricing_history_more_content", $item->id); ?>
        </div>
    </div>