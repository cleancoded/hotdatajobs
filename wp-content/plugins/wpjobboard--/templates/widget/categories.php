<?php

/**
 * Categories 
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
    <?php if(!empty($categories)): foreach($categories as $category): ?>
    <?php if($param->hide_empty && !$category->getCount()) continue; ?>
    <li>
        <a href="<?php echo wpjb_link_to("category", $category) ?>">
            <?php esc_html_e($category->title) ?>
        </a>
        <?php if($param->count): ?>
        <div class="wpjb-widget-item-count">
            <div class="wpjb-widget-item-num"><?php echo intval($category->getCount()) ?></div>
        </div>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
    <?php else: ?>
    <li><?php _e("No categories found.", "wpjobboard") ?></li>
    <?php endif; ?>
</ul>

<?php echo $theme->after_widget ?>