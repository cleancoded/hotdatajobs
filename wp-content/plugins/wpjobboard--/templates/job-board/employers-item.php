<?php 

/**
 * Job list item
 * 
 * This template is responsible for displaying job list item on job list page
 * (template index.php) it is alos used in live search
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 */

 /* @var $company Wpjb_Model_Company */

?>

    <div class="wpjb-grid-row">
        <div class="wpjb-grid-col wpjb-col-logo">
            <?php if($company->doScheme("company_logo")): ?>
            <?php elseif($company->getLogoUrl()): ?>
            <div class="wpjb-img-50">
                <img src="<?php echo $company->getLogoUrl("50x50") ?>" alt="" class="" />
            </div>
            <?php else: ?>
            <div class="wpjb-img-50 wpjb-icon-none">
                <span class="wpjb-glyphs wpjb-icon-industry wpjb-icon-50"></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="wpjb-grid-col wpjb-col-main wpjb-col-title">
            
            <div class="wpjb-line-major">
                <a href="<?php echo wpjb_link_to("company", $company) ?>" class="wpjb-company_name wpjb-title"><?php echo esc_html($company->company_name) ?></a>
                <span class="wpjb-sub-title wpjb-company_slogan wpjb-sub-opaque"> 
                    <span class="wpjb-glyphs wpjb-icon-suitcase"></span>
                    <?php $employer_jobs = wpjb_find_jobs(array("active"=>true, "count_only"=>true, "employer_id"=>$company->id)) ?>
                    <?php echo sprintf(_n("1 job", "%d jobs", $employer_jobs, "wpjobboard"), $employer_jobs) ?>
                </span>
                <span class="wpjb-sub-title wpjb-sub-opaque">
                    <span class="wpjb-glyphs wpjb-icon-location"><?php echo esc_html($company->locationToString()) ?></span>
                </span>

            </div>
            
            <div class="wpjb-line-minor">
                
                
                <span class="wpjb-sub">
                    <?php if($company->company_slogan): ?>
                    <?php echo esc_html($company->company_slogan) ?>
                    <?php else: ?>
                    —
                    <?php endif ?>
                </span>
                
                
                <span class="wpjb-sub wpjb-sub-right wpjb-company_user_registered">
                    <?php echo wpjb_date_display("M, d", $company->getUser(true)->user_registered, false); ?>
                </span>
            </div>
        </div>
        
    </div>


