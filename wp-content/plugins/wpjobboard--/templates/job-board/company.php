<?php

/**
 * Company profile page
 * 
 * This template displays company profile page
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 */

/* @var $jobList array List of active company job openings */
/* @var $company Wpjb_Model_Company Company information */

?>

<div class="wpjb wpjb-job wpjb-page-company">


    <?php wpjb_flash() ?>

    <?php if($company->isVisible() || (Wpjb_Model_Company::current() && Wpjb_Model_Company::current()->id == $company->id)): ?>
    
    <?php $user = $company->getUser(true) ?>
    <?php $image_size = apply_filters("wpjb_singular_logo_size", "64x64", "company") ?>
    
    <div class="wpjb-top-header <?php echo apply_filters( "wpjb_top_header_classes", "wpjb-use-vcard", "company", $company->id ) ?>">
        <div class="wpjb-top-header-image">
            <?php if($company->doScheme("company_logo")): ?>
            <?php elseif($company->getLogoUrl()): ?>
            <img src="<?php echo $company->getLogoUrl($image_size) ?>" alt=""  />
            <?php else: ?>
            <span class="wpjb-glyphs wpjb-icon-industry wpjb-logo-default-size"></span>
            <?php endif; ?>
        </div>
            
        <div class="wpjb-top-header-content">
            <div>
                <span class="wpjb-top-header-title">
                    <?php if($company->doScheme("company_slogan")): ?>
                    <?php elseif($company->company_slogan): ?>
                    <?php echo esc_html($company->company_slogan) ?>
                    <?php else: ?>
                    —
                    <?php endif; ?>
                </span>
                

                <ul class="wpjb-top-header-subtitle">
                    
                    <li>
                        <span class="wpjb-glyphs wpjb-icon-map"></span>
                        <span>
                            <?php if(wpjb_conf("show_maps") && $company->getGeo()->status==2): ?>
                            <a href="<?php echo esc_attr(wpjb_google_map_url($company)) ?>" class="wpjb-tooltip" title="<?php esc_attr_e("show on map", "wpjobboard") ?>"><?php esc_html_e($company->locationToString()) ?><span class="wpjb-glyphs wpjb-icon-down-open"></span></a>
                            <?php else: ?>
                            <?php echo esc_html($company->locationToString()) ?>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li title="<?php esc_html(sprintf(__('%s ago', "wpjobboard"), daq_time_ago_in_words($user->time->user_registered))) ?> ">
                        <span class="wpjb-glyphs wpjb-icon-clock"></span>
                        <span><?php echo wpjb_date_display(get_option('date_format'), $user->user_registered) ?></span>
                    </li>
                    
                    <?php if($company->company_website): ?>
                    <li>
                        <span class="wpjb-glyphs wpjb-icon-globe"></span> 
                        
                        <a href="<?php echo esc_attr($company->company_website) ?>" class="wpjb-maybe-blank"><?php echo esc_html(parse_url($company->company_website, PHP_URL_HOST)) ?></a>
                    </li>
                    <?php endif; ?>
                    

                    
                </ul>
                

                
            </div>
        </div>

    </div>

    <?php if(wpjb_conf("show_maps") && $company->getGeo()->status==2): ?>
    <div class="wpjb-text wpjb-none wpjb-map-slider">
        <iframe style="width:100%;height:350px;margin:0;padding:0;" width="100%" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
        <!--span class="wpjb-glyphs wpjb-icon-arrows-cw wpjb-spin" style="display:block; text-align: center; font-size:64px"></span-->
    </div>
    <?php endif; ?>
    
    <div class="wpjb-grid wpjb-grid-closed-top">      

        <?php foreach($company->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type_exclude"=>"ui-input-textarea")) as $k => $value): ?>
        <div class="wpjb-grid-row <?php esc_attr_e("wpjb-row-meta-".$value->conf("name")) ?>">
            <div class="wpjb-grid-col wpjb-col-30"><?php esc_html_e($value->conf("title")); ?></div>
            <div class="wpjb-grid-col wpjb-col-65 wpjb-glyphs <?php esc_attr_e($value->conf("render_icon", "wpjb-icon-empty")) ?>">
                <?php if($company->doScheme($k)): ?>
                <?php elseif($value->conf("type") == "ui-input-file"): ?>
                    <?php foreach($company->file->{$value->name} as $file): ?>
                    <a href="<?php esc_attr_e($file->url) ?>" rel="nofollow"><?php esc_html_e($file->basename) ?></a>
                    <?php echo wpjb_format_bytes($file->size) ?><br/>
                    <?php endforeach ?>
                <?php else: ?>
                    <?php esc_html_e(join(", ", (array)$value->values())) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
            
        <?php do_action("wpjb_template_company_meta_text", $company) ?>
        
    </div>
    
    <div class="wpjb-text-box">
        <?php if(!$company->company_info): else: ?>
        <h3><?php _e("Company Information", "wpjobboard") ?></h3>
        <div class="wpjb-text">
            <?php if($company->doScheme("company_info")): else: ?>
            <?php wpjb_rich_text($company->company_info, $company->meta->company_info_format->value()) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php foreach($company->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type"=>"ui-input-textarea")) as $k => $value): ?>
        
        <h3><?php esc_html_e($value->conf("title")); ?></h3>
        <div class="wpjb-text">
            <?php if($company->doScheme($k)): else: ?>
            <?php wpjb_rich_text($value->value(), $value->conf("textarea_wysiwyg") ? "html" : "text") ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php do_action("wpjb_template_company_meta_richtext", $company) ?>
    </div>
    
    <div class="wpjb-text">
        <h3 style="font-weight:normal;text-transform: none"><?php echo esc_html(sprintf(__("Current job openings at %s", "wpjobboard"), $company->company_name)) ?></h3>
        
        <div class="wpjb-job-list wpjb-grid">

            <?php $result = apply_filters("wpjb_filter_jobs", wpjb_find_jobs($param), array(), "employer") ?>
            <?php if ($result->count) : foreach($result->job as $job): ?>
            <?php /* @var $job Wpjb_Model_Job */ ?>
            <?php $this->job = $job; ?>
            <?php include $this->getTemplate("job-board", "index-item") ?>
            <?php endforeach; else :?>
            <div class="wpjb-grid-row">
                <?php _e("No job listings found.", "wpjobboard"); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="wpjb-paginate-links">
            <?php wpjb_paginate_links(get_permalink(), $result->pages, $result->page, null, null) ?>
        </div>
    </div>

    <?php endif; ?>

</div>
