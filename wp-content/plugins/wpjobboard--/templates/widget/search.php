<?php

/**
 * Search jobs
 * 
 * Search jobs widget template file
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage Widget
 * 
 */


?>

<?php echo $theme->before_widget ?>
<?php if($title) echo $theme->before_title.$title.$theme->after_title ?>

<div id="wpjb_widget_alerts" class="wpjb_widget">
    
    <form action="<?php echo wpjb_link_to("search"); ?>" method="get">
        <?php if(!$use_permalinks): ?>
        <input type="hidden" name="page_id" value="<?php echo $page_id ?>" />
        <?php endif; ?>
        <input type="text" name="query" placeholder="<?php _e("Keyword, location ..." ,"wpjobboard") ?>" />

        <input type="submit" value="<?php _e("Search", "wpjobboard") ?>" />
    </form>

</div>

<?php echo $theme->after_widget ?>