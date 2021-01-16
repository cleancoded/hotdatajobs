<?php

/**
 * Job details container
 * 
 * Inside this template job details page is generated (using function 
 * wpjb_job_template)
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 * @var $application_url string
 * @var $job Wpjb_Model_Job
 * @var $related array List of related jobs
 * @var $show_related boolean
 * @var $show stdClass
 */

?>
<div class="wpjb wpjb-job wpjb-page-single">

    <?php wpjb_flash() ?>
    <?php include $this->getTemplate("job-board", "job") ?>
 
    <?php if( $members_only ): ?>
    <div class="wpjb-job-apply" style="margin:24px 0px;">
        <div class="wpjb-flash-error wpjb-flash-small">
            <span class="wpjb-glyphs wpjb-icon-attention"><?php esc_html_e($form_error) ?></span>
        </div>
        
        <div>
            <a class="wpjb-button" href="<?php esc_attr_e(add_query_arg("goto-job", $job->id, wpjr_link_to("login"))) ?>"><?php _e("Login", "wpjobboard") ?></a>
            <a class="wpjb-button" href="<?php esc_attr_e(add_query_arg("goto-job", $job->id, wpjr_link_to("register"))) ?>"><?php _e("Register", "wpjobboard") ?></a>
            
            <?php do_action("wpjb_tpl_single_actions", $job, $can_apply) ?>
        </div>
    </div>
    <?php elseif( $premium_members_only ): ?>
    <div class="wpjb-job-apply" style="margin:24px 0px;">
        <div class="wpjb-flash-error wpjb-flash-small">
            <span class="wpjb-glyphs wpjb-icon-attention"><?php esc_html_e($form_error) ?></span>
        </div>
        
        <div>
            <a class="wpjb-button" href="<?php echo esc_html( wpjr_link_to("mymembership") ); ?>"><?php _e("Buy Membership", "wpjobboard") ?></a>
            
            <?php do_action("wpjb_tpl_single_actions", $job, $can_apply) ?>
        </div>
    </div>
    <?php elseif( $can_apply ): ?>
    
    <div class="wpjb-job-apply" id="wpjb-scroll" style="margin:12px 0px;">
        <div class="wpjb-job-buttons">
            <?php if(!wpjb_conf("front_hide_apply_link")): ?>
            <?php if($application_url): ?>
            <a class="wpjb-button" href="<?php esc_attr_e($application_url) ?>"><?php _e("Apply", "wpjobboard") ?></a>
            <?php else: ?>
            <a class="wpjb-button wpjb-form-toggle wpjb-form-job-apply" href="<?php esc_attr_e(wpjb_link_to("job", $job, array("form"=>"apply"))) ?>#wpjb-scroll" rel="nofollow" data-wpjb-form="wpjb-form-job-apply"><?php _e("Apply Online", "wpjobboard") ?> <span class="wpjb-glyphs wpjb-icon-down-open">&nbsp;</span></a>
            <?php endif; ?>
            <?php endif; ?>
           
            
            <?php do_action("wpjb_tpl_single_actions", $job, $can_apply) ?>
        </div>
        
        <?php if(!wpjb_conf("front_hide_apply_link")): ?>
        <div id="wpjb-form-job-apply" class="wpjb-form-slider wpjb-layer-inside <?php if(!$show->apply): ?>wpjb-none<?php endif;?>">
            
            <?php if($form_error): ?>
            <div class="wpjb-flash-error wpjb-flash-small">
                <span class="wpjb-glyphs wpjb-icon-attention"><?php esc_html_e($form_error) ?></span>
            </div>
            <?php endif; ?>
            
            <form id="wpjb-apply-form" action="<?php esc_attr_e(wpjb_link_to("job", $job, array("form"=>"apply"))) ?>#wpjb-scroll" method="post" enctype="multipart/form-data" class="wpjb-form wpjb-form-nolines">
                <?php echo $form->renderHidden() ?>
                <?php foreach($form->getReordered() as $group): ?>
                <?php /* @var $group stdClass */ ?> 
                
                <?php if($group->title): ?>
                <div class="wpjb-legend"><?php esc_html_e($group->title) ?></div>
                <?php endif; ?>
                
                <fieldset class="wpjb-fieldset-<?php esc_attr_e($group->getName()) ?>">

                    <?php foreach($group->getReordered() as $name => $field): ?>
                    <?php /* @var $field Daq_Form_Element */ ?>
                    <div class="<?php wpjb_form_input_features($field) ?>">

                        <label class="wpjb-label">
                            <?php esc_html_e($field->getLabel()) ?>
                            <?php if($field->isRequired()): ?><span class="wpjb-required">*</span><?php endif; ?>
                        </label>

                        <div class="wpjb-field">
                            <?php wpjb_form_render_input($form, $field) ?>
                            <?php wpjb_form_input_hint($field) ?>
                            <?php wpjb_form_input_errors($field) ?>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </fieldset>
                <?php endforeach; ?>
                
                <div class="wpjb-legend"></div>
                
                <fieldset>
                    <input type="submit" class="wpjb-submit" id="wpjb_submit" value="<?php _e("Send Application", "wpjobboard") ?>" />
                </fieldset>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php $relatedJobs = wpjb_find_jobs($related); ?>
    <?php if($show_related && $relatedJobs->total > 0): ?>
    <div class="wpjb-text">
    <h3><?php _e("Related Jobs", "wpjobboard") ?></h3>
    
    <div class="wpjb-grid wpjb-grid-closed-top wpjb-grid-compact">
    <?php foreach($relatedJobs->job as $relatedJob): ?>
    <?php /* @var $relatedJob Wpjb_Model_Job */ ?>
        <div class="wpjb-grid-row <?php wpjb_job_features($relatedJob); ?>">
            <div class="wpjb-grid-col wpjb-col-70">
                <a href="<?php echo wpjb_link_to("job", $relatedJob); ?>"><?php echo esc_html($relatedJob->job_title) ?></a>
                &nbsp; 
                <?php if($relatedJob->locationToString()): ?>
                <span class="wpjb-glyphs wpjb-icon-location"><?php echo esc_html($relatedJob->locationToString()) ?></span>
                <?php endif; ?>
                <?php if($relatedJob->isNew()): ?><span class="wpjb-bulb"><?php _e("new", "wpjobboard") ?></span><?php endif; ?>
            </div>
            <div class="wpjb-grid-col wpjb-grid-col-right wpjb-col-30 wpjb-glyphs wpjb-icon-clock">
            <?php echo wpjb_date_display(get_option('date_format'), $relatedJob->job_created_at) ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    </div>
    <?php endif; ?>
</div>

