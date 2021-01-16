<?php 

/**
 * Resumes list
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage Resumes
 */

 /* @var $resumeList array of Wpjb_Model_Resume objects */
 /* @var $can_browse boolean True if user has access to resumes */

?>

<div class="wpjb wpjr-page-resumes">

    <?php wpjb_flash(); ?>

    <?php if($search_bar != "disabled"): ?>
    <div id="wpjb-top-search" class="wpjb-layer-inside">
    <form action="<?php echo esc_attr(wpjr_link_to("search")) ?>" method="GET" id="wpjb-top-search-form">
        
        <?php echo $form->renderHidden() ?>
        
        <?php foreach($form->getReordered() as $group): ?>
        <div class="wpjb-search <?php echo esc_html("wpjb-search-group-".$group->getName()) ?>" >
            <?php foreach($group->getReordered() as $name => $field): ?>
            <div class="wpjb-input <?php echo esc_attr( $field->getMeta("classes") ) ?>">
                <?php if( strlen( trim( $field->getLabel() ) ) > 0 ): ?>
                <span class="wpjb-search-input-label"><?php echo esc_html( $field->getLabel() ) ?></span>
                <?php endif; ?>
                
                <?php wpjb_form_render_input($form, $field) ?>
                <?php wpjb_form_input_hint($field) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <div class="wpjb-list-search">
            <a href="#" class="wpjb-button wpjb-button-search wpjb-button-submit" title="<?php _e("Filter Results", "wpjobboard") ?>">
                <span class="wpjb-glyphs wpjb-icon-search"></span>
                <span class="wpjb-mobile-only"><?php _e("Filter Results", "wpjobboard") ?></span>
            </a>
            <a href="#" style="display:none" class="wpjb-button wpjb-button-search" title="<?php _e("More Options", "wpjobboard") ?>">
                <span class="wpjb-glyphs wpjb-icon-filter">
                <span class="wpjb-mobile-only"><?php _e("More Options", "wpjobboard") ?></span>
            </a>
            <input type="submit" value="" style="display: none" />
        </div>
    </form>
    </div>
    <?php if($search_bar == "enabled-live"): ?>
        <script type="text/javascript">
            jQuery(function($) {
                //WPJB_SEARCH_CRITERIA = <?php echo json_encode($search_init) ?>;
                //wpjb_ls_jobs_init();
            });
        </script>
    <?php endif; ?>
    
    <?php endif; ?>
    
    <div class="wpjb-grid wpjb-resume-list">
    
        <?php $result = wpjb_find_resumes($param); ?>
        <?php if ($result->count > 0) : foreach($result->resume as $resume): ?>
            <?php /* @var $resume Wpjb_Model_Resume */ ?>
            <?php $this->resume = $resume; ?>
            <?php include $this->getTemplate("resumes", "index-item") ?>
            <?php endforeach; else :?>
            <div class="wpjb-grid-row">
                <?php _e("No resumes found.", "wpjobboard"); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="wpjb-paginate-links">
        <?php wpjb_paginate_links($url, $result->pages, $result->page, $query, $format) ?>
    </div>


</div>
