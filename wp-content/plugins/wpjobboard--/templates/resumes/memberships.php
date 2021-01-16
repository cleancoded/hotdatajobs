<?php

/**
 * Add job form
 * 
 * Template displays add job form
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 */

 /* @var $form Wpjb_Form_AddJob */
 /* @var $can_post boolean User has job posting priviledges */

?>

<div class="wpjb wpjb-page-memberships">

    <?php foreach($memberships as $pricing): ?>
    <?php if($pricing->is_active != 1): continue; endif; ?>
    <div class="wpjb-single-membership <?php if( $pricing->id == $featured || ( !isset($featured) && $pricing->meta->is_featured->value() == 1 ) ): ?>wpjb-single-membership-featured<?php endif; ?>">
        <h2 class="wpjb-motif-bg-dark"><?php echo esc_html($pricing->title); ?></h2>
        <div class="wpjb-membership-price">
            <?php if($pricing->price == 0): ?>
                <?php _e("Free", "wpjobboard") ?>
            <?php else: ?>
                <?php echo esc_html(wpjb_price($pricing->price, $pricing->currency)) ?>
                <?php if($pricing->meta->is_recurring->value()): ?>
                <small><?php printf( __( "/%d day(s)", "wpjobboard" ), $pricing->meta->visible->value() ) ?> <?php //_e("/mth", "wpjobboard"); ?></small>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="wpjb-membership-time">
            <?php if( $pricing->meta->visible->value() > 0 ): ?>
                <span><?php printf( __('%s days', 'wpjobboard'), $pricing->meta->visible->value() ) ?></span>
            <?php else: ?>
                <span><?php _e("Unlimited", "wpjobboard") ?></span>
            <?php endif; ?>
                
            
        </div>
        <div class="wpjb-membership-details">
            
            <?php $package = unserialize($pricing->meta->package->value()) ?>
            
            <?php if( $package['featured_level'] ): ?>
            <div class="wpjb-company-product-details-single">
                <span class="wpjb-glyphs wpjb-icon-flag"></span>
                <?php printf( __("<strong>Featured Level</strong>: %d", "wpjobboard" ), $package['featured_level'] ); ?>
            </div>
            <?php endif; ?>
            
            <?php if( $package['alert_slots'] ): ?>
            <div class="wpjb-company-product-details-single">
                <span class="wpjb-glyphs wpjb-icon-bell"></span>
                <?php printf( __("<strong>Alerts Slots</strong>: %d", "wpjobboard" ), $package['alert_slots'] ); ?>
            </div>
            <?php endif; ?>
            
            <?php if( $package['have_access'] ): ?>
            <div class="wpjb-company-product-details-single">
                <span class="wpjb-glyphs wpjb-icon-lock"></span>
                <?php _e("<strong>Have access to pages</strong>:", "wpjobboard" ); ?> <br/>
                <?php foreach( $package['have_access'] as $page_id ): ?>
                <span class="wpjb-glyphs wpjb-icon-empty"></span>
                    <?php echo get_the_title( $page_id ); ?><br/>
                <?php endforeach; ?>
                
            </div>
            <?php endif; ?>
           
            <?php if( wpjb_conf("cv_members_are_searchable") == 1 && $package['is_searchable'] ): ?>
            <div class="wpjb-company-product-details-single">
                <span class="wpjb-glyphs wpjb-icon-search"></span>
                <?php _e("<strong>Searchable</strong>: yes", "wpjobboard" ); ?>
            </div>
            <?php endif; ?>
            
            <?php if( wpjb_conf("cv_members_can_apply") == 1 && $package['can_apply'] ): ?>
            <div class="wpjb-company-product-details-single">
                <?php _e("<strong>Can Apply</strong>: yes", "wpjobboard" ); ?>
            </div>
            <?php endif; ?>
            
            
            <?php do_action('wpjb_membership_details', $pricing, $package); ?>
        </div>
        <div class="wpjb-membership-actions">
            
            <?php if( array_key_exists($pricing->id, $subscriptions) && $subscriptions[$pricing->id]->stripe_id && $subscriptions[$pricing->id]->stripe_status == -1 ): ?>
                <?php _e("You canceled this plan. Wait till subscription expire, before you buy new one.", "wpjobboard"); ?>
            <?php elseif( array_key_exists($pricing->id, $subscriptions) && $subscriptions[$pricing->id]->stripe_id ): ?>
                <?php _e("You Already Have This Plan", "wpjobboard"); ?>
            <?php else: ?>
            <a href="<?php the_permalink() ?>?membership_id=<?php echo esc_html( intval( $pricing->id ) ); ?>" class="wpjb-button">
                <?php if($pricing->price == 0): ?>
                <?php _e("Get It Now!", "wpjobboard"); ?>
                <?php else: ?>
                <?php _e("Purchase Now!", "wpjobboard"); ?>
                <?php endif; ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>
