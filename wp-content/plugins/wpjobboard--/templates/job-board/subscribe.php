<!-- START: Subscribe overlay -->
<div id="wpjb-overlay" class="wpjb wpjb-overlay wpjb-subscribe-rss">
     <div>
         <h2>
             <?php _e("Subscribe To Personalized Notifications", "wpjobboard") ?>
             <a href="#" class="wpjb-overlay-close wpjb-glyphs wpjb-icon-cancel" title="<?php _e("Close", "wpjobboard") ?>"></a>
         </h2>
         
         <p>
             <?php _e("You are subscribing to jobs matching your current search criteria.", "wpjobboard") ?>
         </p>
         
         
         <div>
             <strong>
                <span class="wpjb-glyphs wpjb-icon-mail-alt" style="font-size:1.4em"></span>
                <?php _e("Email Notifications", "wpjobboard") ?>
             </strong>
             <?php if( $alerts['current'] < $alerts['max'] || $alerts['max'] == -1 ): ?>
             <form action="" method="post">
                 <p>
                     <span><?php _e("Email notifications will be sent to you", "wpjobboard") ?></span>
                     
                     <input type="radio" value="1" name="frequency[]" id="wpjb-mail-frequency-daily" class="wpjb-subscribe-frequency" checked="checked" />
                     <label for="wpjb-mail-frequency-daily"><?php _e("Daily", "wpjobboard") ?></label>

                     <input type="radio" value="2" name="frequency[]" id="wpjb-mail-frequency-weekly" class="wpjb-subscribe-frequency" />
                     <label for="wpjb-mail-frequency-weekly"><?php _e("Weekly", "wpjobboard") ?></label>

      
                     <input type="text" placeholder="<?php _e("enter your email address here ...", "wpjobboard") ?>" id="wpjb-subscribe-email" value="" name="alert_email" style="width:60%" />
                     <a href="" class="wpjb-button wpjb-subscribe-save">
                         <?php _e("Subscribe", "wpjobboard") ?>
                     </a>
                     <img alt="" class="wpjb-subscribe-load" src="<?php echo get_admin_url() ?>/images/wpspin_light.gif" style="display:none" />
                     
                     <div class="wpjb-flash-info wpjb-subscribe-result wpjb-none">&nbsp;</div>
                 </p>
             </form>
             <?php else: ?>
             <p>
                <?php printf( __( "Alerts limit reached %d/%d", "wpjobboard" ), $alerts['current'], $alerts['max'] ); ?>
             </p>
             <?php endif; ?>
         </div>
         
         
         <div>
             <strong>
                 <span class="wpjb-glyphs wpjb-icon-rss-squared" style="font-size:1.4em"></span>
                 <?php _e("Custom RSS Feed", "wpjobboard") ?>
             </strong>
             <form action="" method="post">
                 
                 <p>
                     <span><?php _e("Your personalized RSS Feed is below, copy the address to your RSS reader.", "wpjobboard") ?></span>
                     
                     <input type="text" value="<?php esc_attr_e($feed_url) ?>" name="feed" style="width:60%" />
                     <a href="<?php esc_attr_e($feed_url) ?>" class="wpjb-button btn">
                         <?php _e("Subscribe", "wpjobboard") ?>
                     </a>
                 </p>
             </form>
         </div>
     </div>
</div>

<script type="text/javascript">
if (typeof ajaxurl === 'undefined') {
    ajaxurl = "<?php echo admin_url('admin-ajax.php') ?>";
}
WPJB_SEARCH_CRITERIA = <?php echo wp_json_encode($param) ?>;
</script>

<!-- END: Subscribe overlay -->