<?php

/**
 * Featured Jobs
 * 
 * Featured jobs widget template file
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage Widget
 * 
 */

 /* @var $jobList array List of Wpjb_Model_Job objects */

?>

<?php echo $theme->before_widget ?>
<?php if($title) echo $theme->before_title.$title.$theme->after_title ?>

<div class="wpjb wpjb-widget">
    <div class="wpjb-grid wpjb-grid-compact wpjb-grid-closed-top">
        <?php if(!empty($jobList)): foreach($jobList as $job): ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-100">
                <span class="wpjb-widget-grid-link"><a href="<?php echo wpjb_link_to("job", $job) ?>"><?php esc_html_e($job->job_title) ?></a></span><br/>
                <span class="wpjb-sub"><?php esc_html_e($job->locationToString()); ?> <span class="wpjb-glyphs wpjb-icon-location"></span></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-100">
                <span><?php _e("No featured jobs found.", "wpjobboard") ?></span>
            </div>
        </div>
        <?php endif; ?> 
    </div>
</div>

<?php echo $theme->after_widget ?>