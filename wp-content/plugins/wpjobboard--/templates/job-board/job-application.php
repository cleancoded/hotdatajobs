<?php

/**
 * Job application details
 *
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 /* @var $job Wpjb_Model_Job */
 /* @var $application Wpjb_Model_Application */

?>

<style type="text/css">
    @media print {
        header,
        footer {
            display: none;
        }
        .wpjb-application-change-status  {
            display: none;
        }
        .wpjb-icon-down-open {
            display: none;
        }
        .wpjb-breadcrumb,
        a.wpjb-manage-action {
            display: none;
        }
    }
</style>

<div class="wpjb wpjb-page-job-application">

    <?php wpjb_flash(); ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>
    
     <div class="wpjb-grid wpjb-grid-compact">
        

        <?php $job = $application->getJob(true); ?>
        <?php $current_status = wpjb_get_application_status($application->status) ?>
        
        <div class="wpjb-grid-row wpjb-manage-item wpjb-manage-application" data-id="<?php echo esc_html($application->id) ?>">
            
            <div class="wpjb-grid-col wpjb-col-1 wpjb-manage-header-img" style="width:80px">
                <?php echo get_avatar( $application->email, 64 ) ?>
            </div>
            
            
            
            <div class="wpjb-grid-col wpjb-col-90" style="width:calc( 100% - 80px )">
                <div class="wpjb-manage-header">
                    
                    <span class="wpjb-manage-header-left wpjb-line-major wpjb-manage-title">
                        <strong style="font-size:1.2em">
                            <?php if($application->applicant_name): ?>
                            <?php esc_html_e($application->applicant_name) ?>
                            <?php else: ?>
                            <?php _e("ID"); echo ": "; echo $application->id; ?>
                            <?php endif; ?>
                        </strong>

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
  
                       
                        <a href="#" class="wpjb-manage-action wpjb-manage-app-status-change">
                            <span class="wpjb-glyphs wpjb-icon-down-open"></span>
                            <?php _e( "Status", "wpjobboard" ) ?> — 
                            <strong class="wpjb-application-status-current-label"><?php echo esc_html( $current_status["label"] ) ?></strong>
                        </a>
                        
                        <a href="<?php echo wpjb_api_url("print/index"); ?>?id=<?php echo $application->id ?>" target="_blank" class="wpjb-manage-action">
                            <span class="wpjb-glyphs wpjb-icon-print"></span>
                            <?php _e( "Print", "wpjobboard" ) ?>
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
            
        </div>
     </div>


    
    <div class="wpjb-grid wpjb-grid-job-application-details">

        <?php if(is_array(wpjb_conf("cv_show_applicant_resume")) && $application->getResume()): ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-30">
               <?php _e("Applicant Resume", "wpjobboard") ?>
           </div>
           <div class="wpjb-col-65 wpjb-glyphs wpjb-icon-link-ext-alt">
                 <a href="<?php esc_attr_e(wpjr_link_to("resume", $application->getResume(), array("application_id"=>$application->id))) ?>"><?php _e("View Resume", "wpjobboard") ?></a>
           </div>
        </div>
        <?php endif; ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-30">
                <?php _e("Applicant E-mail", "wpjobboard") ?>
            </div>
            <div class="wpjb-col-65 wpjb-glyphs wpjb-icon-mail-alt">
                <?php esc_html_e($application->email) ?>
            </div>
        </div>        
        <div class="wpjb-grid-row">
            <div class="wpjb-col-30">
                <?php _e("Date Sent", "wpjobboard") ?>
            </div>
            <div class="wpjb-col-65 wpjb-glyphs wpjb-icon-clock">
                <?php echo wpjb_date_display(get_option("date_format"), $application->applied_at) ?>
            </div>
        </div>        
        <?php foreach($application->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type_exclude"=>"ui-input-textarea")) as $k => $value): ?>
        <div class="wpjb-grid-row <?php esc_attr_e("wpjb-row-meta-".$value->conf("name")) ?>">
            <div class="wpjb-grid-col wpjb-col-30"><?php esc_html_e($value->conf("title")); ?></div>
            <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs <?php esc_attr_e($value->conf("render_icon", "wpjb-icon-empty")) ?>">
                <?php if($application->doScheme($k)): ?>
                <?php elseif($value->conf("type") == "ui-input-file"): ?>
                    <?php foreach($application->file->{$value->name} as $file): ?>
                    <a href="<?php esc_attr_e($file->url) ?>" rel="nofollow"><?php esc_html_e($file->basename) ?></a>
                    <?php echo wpjb_format_bytes($file->size) ?><br/>
                    <?php endforeach ?>
                <?php else: ?>
                    <?php esc_html_e(join(", ", (array)$value->values())) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if(count($application->getFiles())): ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-30">
                <?php _e("Attached Files", "wpjobboard") ?>
            </div>
            <div class="wpjb-col-65">
                <?php foreach($application->getFiles() as $file): ?>
                <a href="<?php echo esc_attr($file->url) ?>"><?php echo esc_html($file->basename) ?></a>
                ~ <?php echo esc_html(wpjb_format_bytes($file->size)) ?>
                <br/>
                <?php endforeach; ?>
            </div>
        </div>   
        <?php endif; ?>
        
        <?php do_action("wpjb_template_application_meta_text", $application) ?>
    </div>
    
    <div class="wpjb-text-box">

        <h3><?php _e("Message", "wpjobboard") ?></h3>
        <div class="wpjb-text">
            <?php wpjb_rich_text($application->message) ?>
        </div>

        <?php foreach($application->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type"=>"ui-input-textarea")) as $k => $value): ?>
        <h3><?php esc_html_e($value->conf("title")); ?></h3>
        <div class="wpjb-text">
            <?php wpjb_rich_text($value->value(), $value->conf("textarea_wysiwyg") ? "html" : "text") ?>
        </div>
        <?php endforeach; ?>

        <?php do_action("wpjb_template_application_meta_richtext", $application) ?>
    </div>
    

    <div class="wpjb-application-change-status ">
        <?php if($app_older): ?>
        <a href="<?php echo esc_attr(add_query_arg($query_args, wpjb_link_to( "job_application", $app_older))) ?>" class="wpjb-button wpjb-glyphs wpjb-icon-left" title="<?php _e("Older", "wpjobboard") ?>"></a>
        <?php else: ?>
        <a href="#" class="wpjb-button wpjb-glyphs wpjb-icon-left" title="<?php _e("Older", "wpjobboard") ?>" style="cursor: not-allowed"></a>
        <?php endif; ?>
        
        <span class=""><strong><?php echo absint($app_i) ?></strong> / <?php echo esc_html($apps->total) ?></span>
        
        <?php if($app_newer): ?>
        <a href="<?php echo esc_attr(add_query_arg($query_args, wpjb_link_to( "job_application", $app_newer))) ?>" class="wpjb-button wpjb-glyphs wpjb-icon-right" title="<?php _e("Newer", "wpjobboard") ?>"></a>
        <?php else: ?>
        <a href="#" class="wpjb-button wpjb-glyphs wpjb-icon-right" title="<?php _e("Newer", "wpjobboard") ?>" style="cursor: not-allowed"></a>
        <?php endif; ?>

    </div>

</div>