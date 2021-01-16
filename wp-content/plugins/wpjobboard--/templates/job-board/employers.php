<?php

/**
 * Jobs list
 * 
 * This template file is responsible for displaying list of jobs on job board
 * home page, category page, job types page and search results page.
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 * @var $param array List of job search params
 * @var $search_bar string Either enabled or disabled
 * @var $search_init array Array of initial search params (used only with live search)
 * @var $pagination bool Show or hide pagination
 */

?>

<div class="wpjb wpjb-page-employers-index">

    <?php wpjb_flash(); ?>

    <?php if($search_bar != "disabled"): ?>
    <div id="wpjb-top-search" class="wpjb-layer-inside">
    <form action="" method="GET" id="wpjb-top-search-form">
        
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
    
    
    
    <div class="wpjb-employer-list wpjb-grid">
    
        <?php $result = Wpjb_Model_Company::search($param) ?>
        <?php if ($result->count) : foreach($result->company as $company): ?>
        <?php include $this->getTemplate("job-board", "employers-item") ?>
        <?php endforeach; else :?>
        <div class="wpjb-grid-row">
            <?php _e("No employers found.", "wpjobboard"); ?>
        </div>
        <?php endif; ?>
    

    </div>
 

    <?php if($pagination): ?>
    <div class="wpjb-paginate-links">
        <?php wpjb_paginate_links($url, $result->pages, $result->page, $query, $format) ?>
    </div>
    <?php endif; ?>

    
</div>
