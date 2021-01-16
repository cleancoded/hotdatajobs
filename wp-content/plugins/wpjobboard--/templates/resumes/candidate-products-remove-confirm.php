
<div class="wpjb wpjb-page-company-home">

    <div class="wpjb-upload-ui">
        <div class="wpjb-upload-inner">
            <span class="wpjb-upload-info"><?php printf( __("Are you sure you want to cancel your subscription? You will be able to use your membership to %s. You won't be charged again. You can buy new membership at any time.", "wpjobboard"), date( wpjb_date_format(), strtotime( $membership->expires_at ) ) ); ?></span>
            <span><a href="<?php echo wpjr_link_to("mymembership"); ?>?action=cancel&id=<?php echo $membership->id; ?>&confirm=yes" class="wpjb-button wpjb-button-primary"><?php _e("Confirm", "wpjobboard"); ?></a></span>
            <span><a href="<?php echo wpjr_link_to("mymembership"); ?>" class="wpjb-button wpjb-button-primary"><?php _e("Cancel", "wpjobboard"); ?></a></span>
        </div>
    </div>

</div>