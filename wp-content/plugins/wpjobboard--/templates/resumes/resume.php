<?php 

/**
 * Job details
 * 
 * This template is responsible for displaying job details on job details page
 * (template single.php) and job preview page (template preview.php)
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage Resumes
 */

 /* @var $resume Wpjb_Model_Resume */
 /* @var $can_browse boolean True if user has access to resumes */
 
?>

<div class="wpjb wpjr-page-resume">

    <?php wpjb_flash() ?>
    <?php $image_size = apply_filters("wpjb_singular_logo_size", "64x64", "resume") ?>
    
    <div class="wpjb-top-header <?php echo apply_filters( "wpjb_top_header_classes", "wpjb-use-round-image", "resume", $resume->id ) ?>">
        <div class="wpjb-top-header-image">
            <?php if($resume->doScheme("image")): ?>
            <?php elseif($resume->getAvatarUrl()): ?>
            <img src="<?php echo esc_attr($resume->getAvatarUrl($image_size)) ?>" alt="<?php echo esc_attr($resume->headline) ?>"  />
            <?php else: ?>
            <span class="wpjb-glyphs wpjb-icon-user wpjb-logo-default-size"></span>
            <?php endif; ?>
        </div>
            
        <div class="wpjb-top-header-content">
            <div>
                <span class="wpjb-top-header-title">
                    <?php if($resume->doScheme("headline")):  ?>
                    <?php elseif($resume->headline): ?>
                    <?php echo esc_html($resume->headline) ?>
                    <?php else: ?>
                    —
                    <?php endif; ?>
                </span>
                
                <ul class="wpjb-top-header-subtitle">
                    
                    <?php do_action( "wpjb_template_resume_meta_pre", $resume ) ?>
                    
                    <?php if(wpjb_conf("show_maps")): ?>
                    <li class="wpjb-resume-location">
                        <span class="wpjb-glyphs wpjb-icon-map"></span>
                        <span>
                            <?php if($resume->getGeo()->status==2): ?>
                            <a href="<?php echo esc_attr(wpjb_google_map_url($resume)) ?>" class="wpjb-tooltip" title="<?php echo esc_attr("show on map", "wpjobboard") ?>"><?php echo esc_html($resume->locationToString()) ?><span class="wpjb-glyphs wpjb-icon-down-open"></span></a>
                            <?php else: ?>
                            <?php echo esc_html($resume->locationToString()) ?>
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php endif; ?>
                    
                    <li class="wpjb-resume-modified-at">
                        <span class="wpjb-glyphs wpjb-icon-clock"></span>
                        <?php echo wpjb_date_display(get_option('date_format'), $resume->modified_at) ?>
                    </li>
                    
                    <?php do_action( "wpjb_template_resume_meta", $resume ) ?>
                    
                </ul>
                
                <em class="wpjb-top-header-subtitle">
                    
                </em>
            </div>
        </div>
    </div>

    <?php if(wpjb_conf("show_maps") && $resume->getGeo()->status==2): ?>
    <div class="wpjb-none wpjb-map-slider">
        <iframe style="width:100%;height:350px;margin:0;padding:0;" width="100%" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
    </div>
    <?php endif; ?>
    
    <?php if($resume->description): ?>
    <div class="wpjb-text-box" style="margin: 1em 0 1em 0; font-size: 1.1em">
        <?php if($resume->doScheme("description")): else: ?>
        <div class="wpjb-text"><?php echo wpjb_rich_text($resume->description, "html") ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="wpjb-grid wpjb-grid-closed-top">       
        
        <?php if(!empty($resume->getTag()->category)): ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-grid-col wpjb-col-30"><?php _e("Category", "wpjobboard"); ?></div>
            <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs wpjb-icon-tags">
                <?php foreach($resume->getTag()->category as $category): ?>
                    <a href="<?php esc_attr_e(wpjr_link_to("category", $category)) ?>"><?php esc_html_e($category->title) ?></a>
                <?php endforeach; ?>
            </div>
        </div>   
        <?php endif; ?>
        
        <?php if($resume->getUser(true)): ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-grid-col wpjb-col-30"><?php _e("E-mail", "wpjobboard"); ?></div>
            <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs wpjb-icon-mail-alt">
                <?php if($resume->doScheme("user_email")):  ?>
                <?php elseif(in_array("user_email", $tolock) && !$can_browse): ?>
                <span class="wpjb-glyphs wpjb-icon-lock"><em><?php _e("Locked", "wpjobboard") ?></em></span>
                <?php else: ?>
                <?php esc_html_e($resume->getUser()->user_email) ?>
                <?php endif; ?>
            </div>
        </div>  
        <?php endif; ?>
        
        <?php if($resume->phone): ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-grid-col wpjb-col-30"><?php _e("Phone Number", "wpjobboard") ?></div>
            <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs wpjb-icon-phone">
                <?php if($resume->doScheme("phone")): ?>
                <?php elseif(in_array("phone", $tolock) && !$can_browse): ?>
                <span class="wpjb-glyphs wpjb-icon-lock"><em><?php _e("Locked", "wpjobboard") ?></em></span>
                <?php else: ?>
                <?php esc_html_e($resume->phone) ?>
                <?php endif; ?>
            </div>
        </div>   
        <?php endif; ?>        
        
        <?php if($resume->getUser(true)->user_url): ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-grid-col wpjb-col-30"><?php _e("Website", "wpjobboard") ?></div>
            <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs wpjb-icon-link-ext-alt">
                <?php if($resume->doScheme("user_url")): ?>
                <?php elseif(in_array("user_url", $tolock) && !$can_browse): ?>
                <span class="wpjb-glyphs wpjb-icon-lock"><em><?php _e("Locked", "wpjobboard") ?></em></span>
                <?php else: ?>
                <a href="<?php esc_attr_e($resume->getUser()->user_url) ?>"><?php esc_html_e($resume->getUser()->user_url) ?></a>
                <?php endif; ?>
            </div>
        </div>   
        <?php endif; ?>
        
        <?php foreach($resume->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type_exclude"=>"ui-input-textarea")) as $k => $value): ?>
        <div class="wpjb-grid-row <?php esc_attr_e("wpjb-row-meta-".$value->conf("name")) ?>">
            <div class="wpjb-grid-col wpjb-col-30"><?php esc_html_e($value->conf("title")); ?></div>
            <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs <?php esc_attr_e($value->conf("render_icon", "wpjb-icon-empty")) ?>">
                <?php if($resume->doScheme($k)): ?>
                <?php elseif(in_array($k, $tolock) && !$can_browse): ?>
                    <span class="wpjb-glyphs wpjb-icon-lock"><em><?php _e("Locked", "wpjobboard") ?></em></span>
                <?php elseif($value->conf("render_callback")): ?>
                    <?php call_user_func($value->conf("render_callback")); ?>
                <?php elseif($value->conf("type") == "ui-input-file"): ?>
                    <?php foreach($resume->file->{$value->name} as $file): ?>
                    <a href="<?php esc_attr_e($file->url) ?>" rel="nofollow"><?php esc_html_e($file->basename) ?></a>
                    <?php echo wpjb_format_bytes($file->size) ?><br/>
                    <?php endforeach ?>
                <?php else: ?>
                    <?php esc_html_e(join(", ", (array)$value->values())) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
            
        <?php do_action("wpjb_template_resume_meta_text", $resume) ?>
    </div>
    
    <?php
        $dList = array(
            __("Education", "wpjobboard") => $resume->getEducation(),
            __("Experience", "wpjobboard") => $resume->getExperience()
        );
    ?>
    
    <?php foreach($dList as $title => $details): ?>
    <?php if(!empty($details)): ?>
    <div class="wpjb-text-box">
        <h3><?php esc_html_e($title) ?></h3>
        <?php foreach($details as $detail): ?>
        
        <div class="wpjb-resume-detail">
            <div class="wpjb-column-left">
                
                <strong><?php esc_html_e($detail->detail_title) ?></strong>
                <?php if($detail->grantor): ?>
                <span> @ <?php esc_html_e($detail->grantor) ?></span>
                <?php endif; ?>

            </div>
            <div class="wpjb-column-right wpjb-motif">
                <?php $glue = "" ?>
                <?php if($detail->started_at != "0000-00-00"): ?>
                <?php esc_html_e(wpjb_date_display("M Y", $detail->started_at)) ?>
                <?php $glue = "—"; ?>
                <?php endif; ?>

                <?php if($detail->is_current): ?>
                <?php echo $glue." "; esc_html_e("Current", "wpjobboard") ?>
                <?php elseif($detail->completed_at != "0000-00-00"): ?>
                <?php echo $glue." "; esc_html_e(wpjb_date_display("M Y", $detail->completed_at)) ?>
                <?php endif; ?>
            </div>
            <?php if($detail->detail_description): ?>
            <div class="wpjb-clear"><?php echo wpjb_rich_text($detail->detail_description) ?></div>
            <?php endif; ?>
            
            <?php do_action("wpjb_template_resume_detail_meta_text", $detail) ?>
        </div>
        
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <div class="wpjb-text-box">
        <?php foreach($resume->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type"=>"ui-input-textarea")) as $k => $value): ?>
        
        <h3><?php esc_html_e($value->conf("title")); ?></h3>
        <div class="wpjb-text">
            <?php if($resume->doScheme($k)): else: ?>
            <?php wpjb_rich_text($value->value(), $value->conf("textarea_wysiwyg") ? "html" : "text"); ?>
            <?php endif; ?>
        </div>
        
        <?php endforeach; ?>

        <?php do_action("wpjb_template_resume_meta_richtext", $resume) ?>
    </div>

    <div id="wpjb-scroll" class="wpjb-job-content">
        <h3><?php _e("Contact Candidate", "wpjobboard") ?></h3>
        
        <?php if($c_message): ?><div class="wpjb-flash-info"><?php esc_html_e($c_message) ?></div><?php endif; ?>
        
        <div>
            <?php if($button->contact): ?>
            <a class="wpjb-button wpjb-form-toggle wpjb-form-resume-contact" data-wpjb-form="wpjb-form-resume-contact" href="<?php esc_attr_e(wpjr_link_to("resume", $resume, array("form"=>"contact"))) ?>#wpjb-scroll" rel="nofollow"><?php _e("Contact Candidate", "wpjobboard") ?> <span class="wpjb-glyphs wpjb-icon-down-open"></span></a>
            <?php endif; ?>
            
            <?php if($button->login): ?>
            <a class="wpjb-button" href="<?php esc_attr_e(wpjb_link_to("employer_login", null, array("redirect_to"=>  base64_encode($current_url)))) ?>"><?php _e("Login", "wpjobboard") ?></a>
            <?php endif; ?>
            
            <?php if($button->register): ?>
            <a class="wpjb-button" href="<?php esc_attr_e(wpjb_link_to("employer_new", null, array("redirect_to"=>  base64_encode($current_url)))) ?>"><?php _e("Register", "wpjobboard") ?></a>
            <?php endif; ?>
            
            <?php if($button->purchase): ?>
            <a class="wpjb-button wpjb-form-toggle wpjb-form-resume-purchase" data-wpjb-form="wpjb-form-resume-purchase" href="<?php esc_attr_e(wpjr_link_to("resume", $resume, array("form"=>"purchase"))) ?>#wpjb-scroll" rel="nofollow"><?php _e("Purchase", "wpjobboard") ?> <span class="wpjb-glyphs wpjb-icon-down-open">&nbsp;</span></a>
            <?php endif; ?>
            
            <?php if($button->verify): ?>
            <a class="wpjb-button" href="<?php esc_attr_e(wpjb_link_to("employer_verify")) ?>"><?php _e("Request verification", "wpjobboard") ?></a>
            <?php endif; ?>
        </div>
        
        <?php foreach($f as $k => $form): ?>
        <div id="wpjb-form-resume-<?php echo $k ?>" class="wpjb-form-resume wpjb-form-slider wpjb-layer-inside <?php if(!$show->$k): ?>wpjb-none<?php endif; ?>">
            
        <?php if($form_error): ?>
        <div class="wpjb-flash-error wpjb-flash-small">
            <span class="wpjb-glyphs wpjb-icon-attention"><?php esc_html_e($form_error) ?></span>
        </div>
        <?php endif; ?>
            
        <form class="wpjb-form wpjb-form-nolines" action="<?php esc_attr_e(wpjr_link_to("resume", $resume)) ?>#wpjb-scroll" method="post">

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
                <input type="submit" class="wpjb-submit" value="<?php _e("Submit", "wpjobboard") ?>" />
            </fieldset>  
            

        </form>
        </div>
        <?php endforeach; ?>
        
    </div>
    
</div>

