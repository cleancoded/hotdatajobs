<?php

/**
 * Company job stats
 * 
 * Template displays company jobs stats
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 */

 /* @var $jobList array List of jobs to display */
 /* @var $browse string One of: active; expired */
 /* @var $expiredCount int Total number of company expired jobs */
 /* @var $activeCount int Total number of company active jobs */

?>

<div class="wpjb wpjb-page-company-panel">

    <?php wpjb_flash(); ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>
    
    <ul class="wpjb-tabs">
        <li class="wpjb-tab-link <?php if($browse == "active"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("employer_panel") ?>"><?php _e("Active", "wpjobboard"); ?></a> (<?php echo $total->active ?>)
        </li>
        <li class="wpjb-tab-link <?php if($browse == "pending"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("employer_panel", null, array("filter"=>"pending")) ?>"><?php _e("Pending", "wpjobboard"); ?></a> (<?php echo $total->pending ?>)
        </li>
        <li class="wpjb-tab-link <?php if($browse == "expired"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("employer_panel", null, array("filter"=>"expired")) ?>"><?php _e("Expired", "wpjobboard"); ?></a> (<?php echo $total->expired ?>)
        </li>
        <li class="wpjb-tab-link <?php if($browse == "filled"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("employer_panel", null, array("filter"=>"filled")) ?>"><?php _e("Filled", "wpjobboard"); ?></a> (<?php echo $total->filled ?>)
        </li>
    </ul>
    
    <div class="wpjb-grid wpjb-tab-content">
        <?php if(!empty($result->job)): ?>
        <?php foreach($result->job as $job): ?>
        <div class="wpjb-grid-row wpjb-manage-item <?php wpjb_panel_features($job) ?>">
            
            
            <div class="wpjb-grid-col wpjb-col-100">
                <div class="wpjb-manage-header">
                    <?php if($job->doScheme("job_title")): else: ?>
                    <span class="wpjb-manage-header-left wpjb-line-major wpjb-manage-title">
                        <a href="<?php echo wpjb_link_to("job", $job) ?>"><?php esc_html_e($job->job_title) ?></a>
                        <?php if($job->is_featured): ?>
                        <span class="wpjb-glyphs wpjb-icon-flag" title="<?php echo esc_attr( "Featured", "wpjobboard" ) ?>"></span>
                        <?php endif; ?>
                        
                        <?php if($job->is_filled): ?>
                        <span class="wpjb-glyphs wpjb-icon-user-circle" title="<?php echo esc_attr( "This position is already taken", "wpjobboard") ?>"></span>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    
                    <ul class="wpjb-manage-header-right">
                        
                        <?php do_action( "wpjb_sh_manage_header_right_before", $job->id, $job->post_id ) ?>
                        
                        <li>
                            <span class="wpjb-glyphs wpjb-icon-clock"></span>
                            <span class="wpjb-manage-header-right-item-text">
                                <abbr title="<?php _e("Expiration Date") ?>">
                                    <?php if($job->job_expires_at === WPJB_MAX_DATE): ?>
                                         <?php _e("Never", "wpjobboard") ?>
                                     <?php elseif($job->expired()): ?>
                                         <?php _e("Expired", "wpjobboard") ?>
                                     <?php else: ?>
                                         <?php echo esc_html(wpjb_date_display(get_option("date_format"), $job->job_expires_at)) ?>
                                     <?php endif; ?>
                                </abbr>
                            </span>
                        </li>
                        
                        <?php do_action( "wpjb_sh_manage_header_right_after", $job->id, $job->post_id ) ?>
                    </ul>
                    

                </div>

                
                <div class="wpjb-manage-actions-wrap">

                    <span class="wpjb-manage-actions-left">
                        <?php $apps = Wpjb_Model_Application::search(array("job"=>$job->id, "count_only"=>true, "filter"=>"public")) ?>
                        
                        <?php if($apps == 0): ?>
                        <a href="<?php echo esc_attr( wpjb_link_to( "job_applications", null, array( "job_id"=>$job->id ) ) ) ?>" title="Applications" class="wpjb-manage-action wpjb-no-hover"><span class="wpjb-glyphs wpjb-icon-inbox"></span> <?php echo $apps ?></a>
                        <?php else: ?>
                        <span style="position:relative">
                            <a href="<?php echo esc_attr( wpjb_link_to( "job_applications", null, array( "job_id"=>$job->id ) ) ) ?>" title="Applications" class="wpjb-manage-action wpjb-no-hover wpjb-motif-bg wpjb-motif-border wpjb-manage-applications">
                                <span class="wpjb-glyphs wpjb-icon-inbox"></span> 
                                <strong><?php echo $apps ?></strong>

                            </a>
                            
                            <?php $apps_unread = Wpjb_Model_Application::search(array("job"=>$job->id, "count_only"=>true, "status"=>1)) ?>
                            <?php if($apps_unread): ?>
                            <span class="wpjb-notify-new wpjb-manage-applications-new" title="<?php _e( "Unread Applications", "wpjobboard" ) ?>"><?php echo absint( $apps_unread ) ?></span>
                            <?php endif; ?>
                        </span>
                        
                        <?php endif; ?>
                        
                        <a href="<?php echo wpjb_link_to("job", $job) ?>" class="wpjb-manage-action"><span class="wpjb-glyphs wpjb-icon-eye"></span><?php _e("View", "wpjobboard") ?></a>
                        <a href="<?php echo wpjb_link_to("job_edit", $job); ?>" title="<?php _e("Edit", "wpjobboard") ?>" class="wpjb-manage-action"><span class="wpjb-glyphs wpjb-icon-pencil-squared"></span><?php _e("Edit", "wpjobboard") ?></a>
                        <a href="<?php echo wpjb_link_to("job_delete", $job) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-manage-action wpjb-manage-action-delete" data-id="<?php echo get_the_ID() ?>" data-nonce="<?php echo wp_create_nonce('wpjobboard-manage-delete') ?>">
                            <span class="wpjb-glyphs wpjb-icon-trash"></span><?php _e("Delete", "wpjobboard") ?>
                        </a>

                        <div class="wpjb-manage-action wpjb-manage-delete-confirm">
                            <span class="wpjb-glyphs wpjb-icon-trash-1"></span>
                            <?php _e( "Are you sure?", "wpjobboard" ) ?>
                            <span class="animate-spin wpjb-icon-spinner wpjb-manage-action-spinner" style="display:none"></span>
                            <a href="#" class="wpjb-manage-action-delete-yes"><?php _e( "Yes", "wpjobboard" ) ?></a>
                            <a href="#" class="wpjb-manage-action-delete-no"><?php _e( "Cancel", "wpjobboard" ) ?></a>
                        </div>

                        <?php do_action( "wpjb_sh_manage_actions_left", $job->id, $job->post_id ) ?>
                    </span>
                    <span class="wpjb-manage-actions-right">
                        <?php do_action( "wpjb_sh_manage_actions_right", $job->id, $job->post_id ) ?>

                        <a href="#" class="wpjb-manage-action wpjb-manage-action-more"><span class="wpjb-glyphs wpjb-icon-menu"></span><?php _e("More", "wpjobboard") ?></a>
                    </span>


                </div>
                
                <div class="wpjb-manage-actions-more">
                    <?php do_action( "wpjb_sh_manage_actions_more", $job->id, $job->post_id ) ?>

                    <a href="<?php echo wpjb_link_to("step_add", null, array("republish"=>$job->id)) ?>" class="wpjb-manage-action">
                        <span class="wpjb-glyphs wpjb-icon-plus"></span>
                        <?php _e("Republish", "wpjobboard") ?>
                    </a>

                    <?php if($browse == "pending" && in_array(Wpjb_Model_Job::STATUS_PAYMENT, $job->status())): ?>
                    <a href="<?php echo $job->paymentUrl() ?>" class="wpjb-manage-action">
                        <span class="wpjb-glyphs wpjb-icon-money"></span>
                        <?php _e("Make Payment ...", "wpjobboard") ?>
                    </a>
                    <?php endif; ?>

                    <?php if($job->is_filled): ?>
                    <a href="<?php echo wpjb_api_url("action/job", array("id"=>$job->id, "do"=>"unfill", "redirect_to"=>$url)) ?>" class="wpjb-manage-action">
                        <span class="wpjb-glyphs wpjb-icon-user-circle-o"></span>
                        <?php _e("Mark as not filled", "wpjobboard") ?>
                    </a>
                    <?php else: ?>
                    <a href="<?php echo wpjb_api_url("action/job", array("id"=>$job->id, "do"=>"fill", "redirect_to"=>$url)) ?>" class="wpjb-manage-action">
                        <span class="wpjb-glyphs wpjb-icon-user-circle"></span>
                        <?php _e("Mark as filled", "wpjobboard") ?>
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php do_action( "wpjb_sh_manage_after", $job->id, $job->post_id ) ?>
   
            </div>

        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-100 wpjb-col-center">
                <?php _e("No job listings found.", "wpjobboard"); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="wpjb-paginate-links">
        <?php wpjb_paginate_links($url, $result->pages, $result->page) ?>
    </div>


</div>

