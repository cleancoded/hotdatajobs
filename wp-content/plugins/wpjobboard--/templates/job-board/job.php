<?php 

/**
 * Job details
 * 
 * This template is responsible for displaying job details on job details page
 * (template single.php) and job preview page (template preview.php)
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 */

 /* @var $job Wpjb_Model_Job */
 /* @var $company Wpjb_Model_Employer */
         
?>

    <?php $company = $job->getCompany(true); ?>
    <?php $image_size = apply_filters("wpjb_singular_logo_size", "64x64", "job") ?>

    <div>
    
    <div class="wpjb-top-header <?php echo apply_filters( "wpjb_top_header_classes", "", "job", $job->id ) ?>">
        <div class="wpjb-top-header-image">
            <?php if($job->doScheme("company_logo")): ?>
            <?php elseif($job->getLogoUrl()): ?>
            <img src="<?php echo $job->getLogoUrl($image_size) ?>" alt=""  />
            <?php elseif($company->getLogoUrl()): ?>
            <img src="<?php echo $company->getLogoUrl($image_size) ?>" alt=""  />
            <?php else: ?>
            <span class="wpjb-glyphs wpjb-icon-industry wpjb-logo-default-size"></span>
            <?php endif; ?>
        </div>
            
        <div class="wpjb-top-header-content">
            <div>
                <span class="wpjb-top-header-title">
                    <?php if($job->doScheme("company_name")): ?>
                    <?php else: ?>
                    <?php echo esc_html($job->company_name) ?>
                    <?php endif; ?>
                </span>
                
                <ul class="wpjb-top-header-subtitle">
                    
                    <?php do_action( "wpjb_template_job_company_meta_pre", $job ) ?>
                    
                    <?php if($company && $company->hasActiveProfile()): ?>
                    <li class="wpjb-company-has-active-profile">
                        <span class="wpjb-glyphs wpjb-icon-suitcase"></span>
                        <span>
                            <?php $total = wpjb_find_jobs(array("active"=>true, "count_only"=>true, "employer_id"=>$company->id)) ?>
                            <?php echo sprintf(_n("1 active job", "%d active jobs", $total, "wpjobboard"), $total) ?>
                        </span>
                        <a href="<?php echo esc_attr(wpjb_link_to("company", $company)) ?>"><?php _e("(view)", "wpjobboard") ?></a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if($job->company_url): ?>
                    <li class="wpjb-job-company-url">
                        <span class="wpjb-glyphs wpjb-icon-globe"></span> 
                        <a href="<?php echo esc_attr($job->company_url) ?>" class="wpjb-maybe-blank"><?php echo esc_html(parse_url($job->company_url, PHP_URL_HOST)) ?></a>
                    </li>
                    <?php endif; ?>
                    
                    <?php do_action( "wpjb_template_job_company_meta", $job ) ?>
                    
                </ul>
                
            </div>
        </div>

    </div>
          
    <div class="wpjb-grid wpjb-grid-closed-top">
        <?php foreach($job->getMetaRowsData() as $k => $row): ?>
        <?php $value = wpjb_row_value($row["value"], $k, $job, $row["display"]) ?>
        <?php if((!isset($row["enabled"]) || $row["enabled"] == "1") && $value): ?>
        <div class="wpjb-grid-row <?php echo esc_attr("wpjb-row-meta-" . $row["name"] . $row["classes"]) ?>">
            <div class="wpjb-grid-col wpjb-col-35">
                <?php echo esc_html($row["title"]); ?>
                <span class="wpjb-grid-row-icon wpjb-glyphs <?php echo esc_attr($row["icon"]) ?>"></span>
            </div>
            <div class="wpjb-grid-col wpjb-col-60 ">
                <?php if($job->doScheme($k)): ?>
                <?php else: ?>
                <?php echo $value ?>
                <?php endif; ?>
            </div>
            <?php do_action("wpjb_template_job_meta_row_after_$k", $row, $job) ?>
            <?php do_action("wpjb_template_job_meta_row_after", $row, $job) ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        
        <?php foreach($job->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type_exclude"=>"ui-input-textarea")) as $k => $value): ?>
        <div class="wpjb-grid-row <?php esc_attr_e("wpjb-row-meta-".$value->conf("name")) ?>">
            <div class="wpjb-grid-col wpjb-col-35"><?php echo esc_html($value->conf("title")); ?></div>
            <div class="wpjb-grid-col wpjb-col-60">
                <?php if($job->doScheme($k)): ?>
                <?php elseif($value->conf("type") == "ui-input-file"): ?>
                    <?php foreach($job->file->{$value->name} as $file): ?>
                    <a href="<?php esc_attr_e($file->url) ?>" rel="nofollow"><?php esc_html_e($file->basename) ?></a>
                    <?php echo wpjb_format_bytes($file->size) ?><br/>
                    <?php endforeach ?>
                <?php else: ?>
                    <?php esc_html_e(join(", ", (array)$value->values())) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php do_action("wpjb_template_job_meta_text", $job) ?>
    </div>

    <div class="wpjb-text-box">

        <h3><?php _e("Description", "wpjobboard") ?></h3>
        <div class="wpjb-text">
            <?php if($job->doScheme("job_description")): else: ?>
            <?php wpjb_rich_text($job->job_description, $job->meta->job_description_format->value()) ?>
            <?php endif; ?>
        </div>

        <?php foreach($job->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type"=>"ui-input-textarea")) as $k => $value): ?>
        

        <h3><?php esc_html_e($value->conf("title")); ?></h3>
        <div class="wpjb-text">
            <?php if($job->doScheme($k)): else: ?>
            <?php wpjb_rich_text($value->value(), $value->conf("textarea_wysiwyg") ? "html" : "text") ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php do_action("wpjb_template_job_meta_richtext", $job) ?>
    </div>
    
    </div>

