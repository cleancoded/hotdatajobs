<?php echo $theme->before_widget ?>
<?php if($title) echo $theme->before_title.$title.$theme->after_title ?>

<?php if( $alerts['current'] < $alerts['max'] || $alerts['max'] == -1 ): ?>
    <?php if($is_smart): ?>

    <div class="wpjb wpjb-widget-smart-alert">
        <div><span><?php _e("Do you like these job search results?", "wpjobboard") ?></span></div>
        <a href="#" class="wpjb-subscribe wpjb-button"><?php _e("Subscribe Now ...", "wpjobboard") ?></a>
    </div>

    <?php else: ?>

    <?php wp_enqueue_script('wpjb-alert'); ?>

    <script type="text/javascript">
    if (typeof ajaxurl === 'undefined') {
        ajaxurl = "<?php echo admin_url('admin-ajax.php') ?>";
    }
    </script>

    <div class="wpjb-widget-alert wpjb">
        <form action="<?php esc_attr_e(wpjb_link_to("alert_confirm")) ?>" method="post">
        <input type="hidden" name="add_alert" value="1" />
        <ul id="wpjb_widget_alerts" class="wpjb_widget">
            <?php $form = new Wpjb_Form_Frontend_Alert() ?>
            <?php foreach($form->getReordered() as $group): ?>
                <?php foreach($group->getReordered() as $name => $field): ?>
                <li>
                    <?php echo $field->render() ?>
                </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <li>
                <div class="wpjb-widget-alert-result" style="padding:2px 6px; margin: 0 0 5px 0; display: none"></div>
                <input type="submit" class="wpjb-button wpjb-widget-alert-save" value="<?php _e("Create Email Alert", "wpjobboard") ?>" />
            </li>
        </ul>
        </form>
    </div>

    <?php endif; ?>
<?php else: ?>
    <div class="wpjb-widget-alert wpjb">
        <?php printf( __( "Alerts limit reached %d/%d", "wpjobboard" ), $alerts['current'], $alerts['max'] ); ?>
    </div>
<?php endif; ?>
<?php echo $theme->after_widget ?>