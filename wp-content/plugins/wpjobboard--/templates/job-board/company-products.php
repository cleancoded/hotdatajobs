<?php

/**
 * Company job applications
 * 
 * Template displays job applications
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 */

 /* @var $applicantList array List of applications to display */
 /* @var $job string Wpjb_Model_Job */

?>

<div class="wpjb wpjb-page-company-products">

    <?php wpjb_flash(); ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>
    
    <h2>
        <?php _e("Active Memberships", "wpjobboard"); ?>
        &nbsp;<a href="<?php echo get_permalink( wpjb_conf('urls_link_membership_pricing') ) ?>" style="font-weight: normal"><?php _e("(Buy Membership)", "wpjobboard"); ?></a>
    </h2>
    
    <?php $has_active = false; ?>
    <?php if (!empty($result)) : foreach($result as $pricing): ?>
    <?php /* @var $pricing Wpjb_Model_Pricing */ ?>
    <?php $summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id("employer")) ?>
    <?php if( $pricing->is_active == 0 || !is_object($summary) ): continue; endif; ?>
    <?php $has_active = true; ?>
    
    <?php 
    $sub_id = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_id', $summary->id);
    $sub_status = Wpjb_Model_MetaValue::getSingle('membership', 'subscription_status', $summary->id);
    $subscription = null;
    if( isset($sub_id) && $sub_id->id > 0 ) {
        $stripe = new Wpjb_Payment_Stripe();
        try {
            $subscription = $stripe->getSubscription( $sub_id->value );
        } catch(Exception $e) {
            $subscription = null;
        }
    } 
    ?>
    
    <div class="wpjb-company-product">
        <div class="wpjb-company-product-header wpjb-motif-bg-dark">
            <h3><?php esc_html_e($pricing->title) ?></h3>
            <div class="wpjb-company-product-status">
                <!--span class="wpjb-bulb wpjb-bulb-active"><?php _e("Active", "wpjobboard") ?></span-->
                <?php if($summary->days_left < 7): ?>
                <span class="wpjb-bulb wpjb-bulb-expiring" style="background-color: #D54E21"><?php _e("Expiring", "wpjobboard") ?></span>
                <?php endif; ?>
                <?php if($pricing->meta->is_recurring->value() && $sub_status->value == "-1"): ?>
                <span class="wpjb-bulb wpjb-bulb-expiring" style="background-color: #D54E21"><?php _e("Canceled", "wpjobboard") ?></span>
                <?php endif; ?>
            </div>         
        </div>
        
        <div class="wpjb-company-product-subheader">
            <div class="wpjb-company-product-indicator">
                <span class="wpjb-glyphs wpjb-icon-calendar"></span>
                <?php if($summary->expires_at != "9999-12-31"): ?>
                <?php printf( __('Days Left: %1$d'), $summary->days_left ); ?>
                <?php else: ?>
                <?php _e('Never Expires', "wpjobboard") ; ?>
                <?php endif; ?>
            </div>
            <?php if($subscription && $pricing->meta->is_recurring->value() && $sub_status->value != "-1"): ?>
            <div class="wpjb-company-product-indicator">
                <span class="wpjb-glyphs wpjb-icon-arrows-cw"></span>
                <?php printf( __('Next Payment: %s', "wpjobboard"), date( wpjb_date_format(), $subscription->current_period_end ) ); ?>
            </div>
            <?php endif; ?>
            <?php do_action('wpjb_company_products_subheader'); ?>
        </div>
        <div class="wpjb-company-product-details">
            <?php $package = unserialize($pricing->meta->package->value()) ?>
            <?php $single_job = $package[Wpjb_Model_Pricing::PRICE_SINGLE_JOB]; ?>
            <?php $single_resume = $package[Wpjb_Model_Pricing::PRICE_SINGLE_RESUME]; ?> 

            <?php if(!empty($single_job)): ?>
            <?php foreach($single_job as $id => $usage): ?>
            <?php $product = new Wpjb_Model_Pricing($id); ?>
            <div class="wpjb-company-product-details-single">
                <strong><?php esc_html_e( $product->title ) ?></strong>
                <?php if($usage["status"] == "unlimited"): ?>
                    <?php printf( __("Uses: Unlimited", "wpjobboard") ); ?>
                <?php else: ?>
                    <?php printf( __('Uses: %1$d / %2$d', "wpjobboard"), $summary->bundle[$product->price_for][$id]["used"], $usage["usage"] ); ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if(!empty($single_resume)): ?>
            <?php foreach($single_resume as $id => $usage): ?>
                <?php $product = new Wpjb_Model_Pricing($id); ?>
                <div class="wpjb-company-product-details-single">
                    <strong><?php esc_html_e( $product->title ) ?></strong>
                    <?php if($usage["status"] == "unlimited"): ?>
                        <?php printf( __("Uses: Unlimited", "wpjobboard") ); ?>
                    <?php else: ?>
                        <?php printf( __('Uses: %1$d / %2$d', "wpjobboard"), $summary->bundle[$product->price_for][$id]["used"], $usage["usage"] ); ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
            
            <?php do_action('wpjb_company_products_additional_details', $pricing, $package); ?>
            
        </div>
        <?php $archive_summary = Wpjb_Model_Membership::getArchivePackageSummary($pricing->id, wpjb_get_current_user_id("employer")) ?>
        <div class="wpjb-company-product-actions">
            <div class="wpjb-company-product-actions-left">
                <?php if(!$pricing->meta->is_recurring->value()): ?>
                <a href="<?php esc_attr_e(wpjb_link_to("membership_purchase", $pricing)) ?>" class="wpjb-manage-action wpjb-glyphs wpjb-icon-arrows-cw"><?php _e("Rebuy Package", "wpjobboard"); ?></a>
                <?php endif; ?>
                
                <?php if($subscription && $pricing->meta->is_recurring->value() && $sub_status->value != "-1"): ?>
                <a href="<?php echo wpjb_link_to("membership") ?>?action=cancel&id=<?php echo $summary->id ?>" class="wpjb-manage-action wpjb-glyphs wpjb-icon-cancel"><?php _e("Cancel Subscription", "wpjobboard"); ?></a>
                <?php elseif($pricing->meta->is_recurring->value() && $sub_status->value == "-1"): ?>
                <?php _e("You already canceled this membership. It will be moved to archive, after expiration date.", "wpjobboard"); ?>
                <?php endif; ?>
                <?php do_action('wpjb_company_products_actions_left'); ?>
            </div>
            <div class="wpjb-company-product-actions-right">
                <?php do_action('wpjb_company_products_actions_right'); ?>
                <?php if(isset($archive_summary)): ?>
                <a href="#" class="wpjb-manage-action wpjb-manage-action-more wpjb-glyphs wpjb-icon-menu"><?php _e("More", "wpjobboard"); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="wpjb-company-product-additional">          
            <?php if(isset($archive_summary)): ?>
            <?php foreach($archive_summary as $asummary): ?>
            <?php if($asummary->started_at == '0000-00-00' && $asummary->expires_at == '0000-00-00' ): continue; endif; ?>
                <div class="wpjb-company-product-header ">
                    <?php if( date("m", strtotime( $asummary->started_at ) ) == date("m", strtotime( $asummary->expires_at ) ) ): ?>
                        <?php echo esc_html( date( "d", strtotime( $asummary->started_at ) ) . ' - ' . date( "d F", strtotime( $asummary->expires_at ) ) ); ?> 
                    <?php else: ?>
                        <?php echo esc_html( date( "d F", strtotime( $asummary->started_at ) ) . ' - ' . date( "d F", strtotime( $asummary->expires_at ) ) ); ?> 
                    <?php endif; ?>
                    <?php if( date("Y", strtotime( $asummary->started_at ) ) == date("Y", strtotime( $asummary->expires_at ) ) ): ?>
                        <?php echo esc_html( date( "Y", strtotime( $asummary->started_at ) ) ); ?> 
                    <?php else: ?>
                        <?php echo esc_html( date( "Y", strtotime( $asummary->started_at ) ) . '/' . date( "y", strtotime( $asummary->started_at ) ) ); ?> 
                    <?php endif; ?>
                </div>
                 
                <?php $package = unserialize($pricing->meta->package->value()) ?>
                <?php $single_job = $package[Wpjb_Model_Pricing::PRICE_SINGLE_JOB]; ?>
                <?php $single_resume = $package[Wpjb_Model_Pricing::PRICE_SINGLE_RESUME]; ?>
                
                <div class="wpjb-company-product-details">
                    <?php if(!empty($single_job)): ?>
                    <?php foreach($single_job as $id => $usage): ?>
                    <?php $product = new Wpjb_Model_Pricing($id); ?>
                    <div class="wpjb-company-product-details-single">
                        <strong><?php esc_html_e( $product->title ) ?></strong>
                        <?php if($usage["status"] == "unlimited"): ?>
                            <?php printf( __("Uses: Unlimited", "wpjobboard") ); ?>
                        <?php else: ?>
                            <?php printf( __('Uses: %1$d / %2$d', "wpjobboard"), $asummary->bundle[$product->price_for][$id]["used"], $usage["usage"] ); ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if(!empty($single_resume)): ?>
                    <?php foreach($single_resume as $id => $usage): ?>
                        <?php $product = new Wpjb_Model_Pricing($id); ?>
                        <div class="wpjb-company-product-details-single">
                            <strong><?php esc_html_e( $product->title ) ?></strong>
                            <?php if($usage["status"] == "unlimited"): ?>
                                <?php printf( __("Uses: Unlimited", "wpjobboard") ); ?>
                            <?php else: ?>
                                <?php printf( __('Uses: %1$d / %2$d', "wpjobboard"), $asummary->bundle[$product->price_for][$id]["used"], $usage["usage"] ); ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php do_action('wpjb_company_products_additional_details', $pricing, $package); ?>
                </div> 
            <?php endforeach; ?>
            <?php else: ?>
                <?php _e("There is no archive records for this membership", "wpjobboard"); ?>
            <?php endif; ?> 
        </div>
    </div>
    <?php endforeach; endif; ?>
    
    <?php if( !$has_active ): ?>
    <div class="wpjb-upload-ui">
        <div class="wpjb-upload-inner">
            <span class="wpjb-upload-info"><?php _e("You do not have any active memberships.", "wpjobboard"); ?></span>
            <span><a href="<?php echo get_permalink( wpjb_conf('urls_link_membership_pricing') ) ?>" class="wpjb-button"><?php _e("Buy Membership!", "wpjobboard"); ?></a></span>
        </div>
    </div>
    <?php endif; ?>
    
    
    <h2><?php _e("Archived Memberships", "wpjobboard"); ?></h2>
    <?php $archived = 0; ?>
    <?php if (!empty($result)) : foreach($result as $pricing): ?>
    <?php /* @var $pricing Wpjb_Model_Pricing */ ?>
    <?php $active_summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id("employer")) ?>
    <?php if( is_object($active_summary) ): continue; endif; ?>
    <?php $archived++; ?>
    
    <?php $summary = Wpjb_Model_Membership::getArchivePackageSummary($pricing->id, wpjb_get_current_user_id("employer")) ?>
    <?php if(!empty($summary)): ?>
    
    <div class="wpjb-company-product">
        <div class="wpjb-company-product-header" style="background-color: #707070;">
            <h3><?php esc_html_e($pricing->title) ?></h3>  
            <div class="wpjb-company-product-status">
                <a href="#" class="wpjb-manage-action wpjb-manage-action-more wpjb-glyphs wpjb-icon-menu" style="background-color: #fff;"><?php _e("More", "wpjobboard"); ?></a>
            </div>
        </div>

        <div class="wpjb-company-product-additional">
            <?php $archive_summary = Wpjb_Model_Membership::getArchivePackageSummary($pricing->id, wpjb_get_current_user_id("employer")) ?>
            <?php if(isset($archive_summary)): ?>
            <?php foreach($archive_summary as $asummary): ?>
                <?php if($asummary->started_at == '0000-00-00' && $asummary->expires_at == '0000-00-00' ): continue; endif; ?>
                <div class="wpjb-company-product-header ">
                    <?php if( date("m", strtotime( $asummary->started_at ) ) == date("m", strtotime( $asummary->expires_at ) ) ): ?>
                        <?php echo esc_html( date( "d", strtotime( $asummary->started_at ) ) . ' - ' . date( "d F", strtotime( $asummary->expires_at ) ) ); ?> 
                    <?php else: ?>
                        <?php echo esc_html( date( "d F", strtotime( $asummary->started_at ) ) . ' - ' . date( "d F", strtotime( $asummary->expires_at ) ) ); ?> 
                    <?php endif; ?>
                    <?php if( date("Y", strtotime( $asummary->started_at ) ) == date("Y", strtotime( $asummary->expires_at ) ) ): ?>
                        <?php echo esc_html( date( "Y", strtotime( $asummary->started_at ) ) ); ?> 
                    <?php else: ?>
                        <?php echo esc_html( date( "Y", strtotime( $asummary->started_at ) ) . '/' . date( "y", strtotime( $asummary->started_at ) ) ); ?> 
                    <?php endif; ?>
                </div>
                 
                <?php $package = unserialize($pricing->meta->package->value()) ?>
                <?php $single_job = $package[Wpjb_Model_Pricing::PRICE_SINGLE_JOB]; ?>
                <?php $single_resume = $package[Wpjb_Model_Pricing::PRICE_SINGLE_RESUME]; ?>
                
                <div class="wpjb-company-product-details">
                    <?php if(!empty($single_job)): ?>
                    <?php foreach($single_job as $id => $usage): ?>
                    <?php $product = new Wpjb_Model_Pricing($id); ?>
                    <div class="wpjb-company-product-details-single">
                        <strong><?php esc_html_e( $product->title ) ?></strong>
                        <?php if($usage["status"] == "unlimited"): ?>
                            <?php printf( __("Uses: Unlimited", "wpjobboard") ); ?>
                        <?php else: ?>
                            <?php printf( __('Uses: %1$d / %2$d', "wpjobboard"), $asummary->bundle[$product->price_for][$id]["used"], $usage["usage"] ); ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if(!empty($single_resume)): ?>
                    <?php foreach($single_resume as $id => $usage): ?>
                        <?php $product = new Wpjb_Model_Pricing($id); ?>
                        <div class="wpjb-company-product-details-single">
                            <strong><?php esc_html_e( $product->title ) ?></strong>
                            <?php if($usage["status"] == "unlimited"): ?>
                                <?php printf( __("Uses: Unlimited", "wpjobboard") ); ?>
                            <?php else: ?>
                                <?php printf( __('Uses: %1$d / %2$d', "wpjobboard"), $asummary->bundle[$product->price_for][$id]["used"], $usage["usage"] ); ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php do_action('wpjb_company_products_additional_details', $pricing, $package); ?>
                </div> 
            <?php endforeach; ?>
            <?php else: ?>
                <?php _e("There is no archive records for this membership", "wpjobboard"); ?>
            <?php endif; ?> 
        </div>
    </div>
    <?php endif; endforeach; endif; ?>
    <?php if($archived == 0): ?>
        <?php _e("You do not have any archived memberships", "wpjobboard"); ?>
    <?php endif; ?>
    
    
    <script type="text/javascript">
        jQuery(function() {
            jQuery(".wpjb-manage-action-more").click(function(event) {
                event.preventDefault();
                jQuery(event.target).parent().parent().parent().find('.wpjb-company-product-additional').slideToggle();
            })
        });
    </script>

</div>

