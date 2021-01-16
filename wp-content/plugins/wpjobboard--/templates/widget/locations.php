<?php

/**
 * Locations
 * 
 * Categories widget template
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage Widget
 * 
 */

 /* @var $categories array List of Wpjb_Model_Tag objects */
 /* @var $param stdClass Widget configurations options */


?>

<?php echo $theme->before_widget ?>
<?php if($title) echo $theme->before_title.$title.$theme->after_title ?>

<ul class="<?php if($param->count): ?>wpjb-widget-with-count<?php endif; ?>">
    <?php if(!empty($locations)): foreach($locations as $location): ?>
    <?php if(empty($location["title"])) continue; ?>
    <li>
        <a href="<?php esc_attr_e($url.$glue.http_build_query($location["query"])) ?>">
            <?php esc_html_e($location["title"]) ?>
        </a>
        <?php if($param->count): ?>
        <div class="wpjb-widget-item-count">
            <div class="wpjb-widget-item-num"><?php echo intval($location["count"]) ?></div>
        </div>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
    <?php else: ?>
    <li><?php _e("No active locations found.", "wpjobboard") ?></li>
    <?php endif; ?>
</ul>

<?php echo $theme->after_widget ?>