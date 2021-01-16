<?php

/**
 * Job types 
 * 
 * Job types widget template
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage Widget
 * 
 */

 /* @var $job_types array List of Wpjb_Model_JobType objects */
 /* @var $param stdClass Widget configurations options */


?>

<?php echo $theme->before_widget ?>
<?php if($title) echo $theme->before_title.$title.$theme->after_title ?>

<ul class="<?php if($param->count): ?>wpjb-widget-with-count<?php endif; ?>">
    <?php if(!empty($job_types)): foreach($job_types as $type): ?>
    <?php if($param->hide_empty && !$type->getCount()) continue; ?>
    <li>
        <a href="<?php echo wpjb_link_to("type", $type) ?>">
            <?php esc_html_e($type->title) ?>
        </a>
        <?php if($param->count): ?>
        <div class="wpjb-widget-item-count">
            <div class="wpjb-widget-item-num"><?php echo intval($type->getCount()) ?></div>
        </div>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
    <?php else: ?>
    <li><?php _e("No job types found.", "wpjobboard") ?></li>
    <?php endif; ?>
</ul>

<?php echo $theme->after_widget ?>