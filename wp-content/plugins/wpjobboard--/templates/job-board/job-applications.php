<?php

/**
 * Company job applications
 * 
 * Template displays job applications
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 */

 /* @var $applicantList array List of applications to display */
 /* @var $job string Wpjb_Model_Job */

?>

<div class="wpjb wpjb-page-job-applications">

    <?php wpjb_flash(); ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>

    <div id="wpjb-top-search" class="wpjb-layer-inside wpjb-filter-applications">
        <form action="<?php echo esc_attr( wpjb_link_to("job_applications")) ?>" method="GET" >
            
        <?php global $wp_rewrite ?>
        <?php if(!$wp_rewrite->using_permalinks()): ?>
        <input type="hidden" name="page_id" value="<?php echo $page_id ?>" />
        <input type="hidden" name="job_board" value="find" />
        <?php endif; ?>
 
        <div class="wpjb-search wpjb-search-group-visible" >
            <div class="wpjb-input wpjb-input-type-half wpjb-input-type-half-left">
                <select name="job_id">
                    <option value=""><?php _e("All Jobs", "wpjobboard") ?></option>
                    <?php foreach($jobsList as $job): ?>
                    <option value="<?php echo esc_html($job->id) ?>" <?php selected($job->id, $job_id) ?>><?php echo esc_html($job->job_title) ?></option>
                    <?php endforeach;; ?>
                </select>
            </div>
            
            <div class="wpjb-input wpjb-input-type-half wpjb-input-type-half-left">
                <select name="job_status">
                    <option value=""><?php _e("All Statuses", "wpjobboard") ?></option>
                    <?php foreach($public_ids as $status_id): ?>
                    <?php $status = wpjb_get_application_status($status_id) ?>
                    <option value="<?php echo esc_html($status_id) ?>" <?php selected($job_status, $status_id) ?>><?php echo esc_html($status["label"])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
 
        <div class="wpjb-list-search">
            <a href="#" class="wpjb-button wpjb-button-search wpjb-button-submit" title="<?php _e("Filter Results", "wpjobboard") ?>">
                <span class="wpjb-glyphs wpjb-icon-search"></span>
                <span class="wpjb-mobile-only"><?php _e("Filter Results", "wpjobboard") ?></span>
            </a>
            <input type="submit" value="" style="display: none" />
        </div>
        
        </form>
    </div>
    
    <div class="wpjb-grid wpjb-grid-compact">
        
        <?php if (!empty($apps->application)): ?>
        <?php foreach($apps->application as $application): ?>
        <?php $job = $application->getJob(true); ?>
        <?php $current_status = wpjb_get_application_status($application->status) ?>
        
        <div class="wpjb-grid-row wpjb-manage-item wpjb-manage-application wpjb-application-status-<?php echo esc_attr($current_status["key"]) ?>" data-id="<?php echo esc_html($application->id) ?>">
            
            <div class="wpjb-grid-col wpjb-col-1 wpjb-manage-header-img" style="width:60px">
                <?php echo get_avatar( $application->email, 52 ) ?>
            </div>
            
            <div class="wpjb-grid-col wpjb-col-90" style="width:calc( 100% - 60px )">
                <div class="wpjb-manage-header">
                    
                    <span class="wpjb-manage-header-left wpjb-line-major wpjb-manage-title">
                        <a href="<?php echo esc_attr(add_query_arg($query_args, wpjb_link_to("job_application", $application))) ?>">
                            <?php if($application->applicant_name): ?>
                            <?php esc_html_e($application->applicant_name) ?>
                            <?php else: ?>
                            <?php _e("ID"); echo ": "; echo $application->id; ?>
                            <?php endif; ?>
                        </a>

                    </span>
                    
                    <ul class="wpjb-manage-header-right">
                        
                        <?php do_action( "wpjb_sh_manage_applications_header_right_before", $application->id ) ?>
                        
                        <li>
                            <span class="wpjb-glyphs wpjb-icon-briefcase"></span>
                            <span class="wpjb-manage-header-right-item-text">
                                <a href="<?php echo wpjb_link_to("job", $job) ?>" class="wpjb-no-text-decoration"><?php echo esc_html( $job->job_title ) ?></a>
                            </span>
                        </li>
                        
                        <li>
                            <span class="wpjb-glyphs wpjb-icon-clock"></span>
                            <span class="wpjb-manage-header-right-item-text">
                            <?php echo esc_html( sprintf( __("%s ago.", "wpjobboard" ), wpjb_time_ago( $application->applied_at ) ) ) ?>
                            </span>
                        </li>
                        
                        <?php do_action( "wpjb_sh_manage_applications_header_right_after", $application->id ) ?>
                    </ul>
                    

                </div>

                
                <div class="wpjb-manage-actions-wrap">

                    <span class="wpjb-manage-actions-left">
  

                        <a href="<?php echo esc_attr(add_query_arg($query_args, wpjb_link_to("job_application", $application))) ?>" class="wpjb-manage-action wpjb-no-320-760"><span class="wpjb-glyphs wpjb-icon-eye"></span><?php _e("View", "wpjobboard") ?></a>

                        <a href="<?php echo wpjb_api_url("print/index"); ?>?id=<?php echo $application->id ?>" target="_blank" class="wpjb-manage-action">
                            <span class="wpjb-glyphs wpjb-icon-print"></span>
                            <?php _e( "Print", "wpjobboard" ) ?>
                        </a>
                        
                        <a href="#" class="wpjb-manage-action wpjb-manage-app-status-change">
                            <span class="wpjb-glyphs wpjb-icon-down-open"></span>
                            <?php _e( "Status", "wpjobboard" ) ?> — 
                            <strong class="wpjb-application-status-current-label"><?php echo esc_html( $current_status["label"] ) ?></strong>
                        </a>
                        
                        

       
                        <?php do_action( "wpjb_sh_manage_applications_actions_left", $job->id, $job->post_id, $application ) ?>
                    </span>
                    <span class="wpjb-manage-actions-right">
                        
                        <?php $rated = absint($application->meta->rating->value()) ?>
                        <span class="wpjb-manage-action wpjb-star-ratings" data-id="<?php echo esc_html($application->id) ?>">
                            <span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin wpjb-star-rating-loader" style="vertical-align: top; display:none"></span>
                            <span class="wpjb-star-rating-bar">
                                <?php for($i=0; $i<5; $i++): ?><span class="wpjb-star-rating wpjb-motif wpjb-glyphs wpjb-icon-star-empty <?php if($rated>$i): ?>wpjb-star-checked<?php endif; ?>" data-value="<?php echo $i+1 ?>" ></span><?php endfor ?>
                            </span>
                        </span>
                        
                        <?php do_action( "wpjb_sh_manage_applications_actions_right", $job->id, $job->post_id, $application ) ?>

                        
                        <a href="#" class="wpjb-manage-action wpjb-manage-action-more"><span class="wpjb-glyphs wpjb-icon-menu"></span><?php _e("More", "wpjobboard") ?></a>
                    </span>

                    <div class="wpjb-manage-actions-more">
                        <?php do_action( "wpjb_sh_manage_applications_actions_more", $job->id, $job->post_id, $application ) ?>
                    </div>
                </div>
   
            </div>
        
            <div style="clear: both; overflow: hidden"></div>

            <div class="wpjb-application-change-status wpjb-filter-applications" style="display: none">
                <select name="job_id" class="wpjb-application-change-status-dropdown">
                <?php foreach($public_ids as $status_id): ?>
                <?php $status = wpjb_get_application_status($status_id) ?>
                    <option 
                        value="<?php echo esc_html($status_id) ?>" 
                        <?php selected($application->status, $status_id) ?>
                        data-can-notify="<?php if(isset($status["notify_applicant_email"]) && !empty($status["notify_applicant_email"])): ?>1<?php endif; ?>"
                        ><?php echo esc_html($status["label"])?>
                    </option>
                <?php endforeach; ?>
                </select>

                <input type="checkbox" value="1" class="wpjb-application-change-status-checkbox" id="wpjb-application-status-<?php echo $application->id ?>"> 
                <label class="wpjb-application-change-status-label" for="wpjb-application-status-<?php echo $application->id ?>"><?php _e("Notify applicant via email", "wpjobboard") ?></label> 

                <span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin wpjb-none wpjb-application-change-status-loader"></span>

                <a href="#" class="wpjb-button wpjb-application-change-status-submit" style="float:right"><?php _e("Change", "wpjobboard") ?></a>                 
            </div>

            <?php do_action( "wpjb_sh_manage_applications_after", $job->id, $job->post_id, $application ) ?>
        </div>

        <?php endforeach; ?>
        <?php else: ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-100 wpjb-grid-col-center">
                <?php _e("No applicants found.", "wpjobboard"); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    

    <?php if(!empty($apps->application)): ?>
    <div class="wpjb-paginate-links">
        <?php wpjb_paginate_links($url, $apps->pages, $apps->page) ?>
    </div>
    <?php endif; ?>

</div>

