<?php wp_enqueue_style( 'wpjb-glyphs' ) ?>
<div class="wrap wpjb">

<h1><?php _e("Job Board Forms", "wpjobboard") ?> </h1>

<div class="clear">&nbsp;</div>

<div class="wpjb-config-list">
     <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-briefcase"></span>
            <?php _e("Job Form", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("custom", "edit", null, array("form"=>"job"))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    
     <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-inbox"></span>
            <?php _e("Apply Online Form", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("custom", "edit", null, array("form"=>"apply"))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    
     <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-search"></span>
            <?php _e("Advanced Search Form", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("custom", "edit", null, array("form"=>"job-search"))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    
     <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-building"></span>
            <?php _e("Company Form", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("custom", "edit", null, array("form"=>"company"))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    
</div>

<br class="clear" />

<h1><?php _e("Resumes Forms", "wpjobboard") ?> </h1>

<div class="clear">&nbsp;</div>

<div class="wpjb-config-list">
     <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-user"></span>
            <?php _e("My Resume Form", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("custom", "edit", null, array("form"=>"resume"))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    
     <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-search"></span>
            <?php _e("Advanced Search Form", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("custom", "edit", null, array("form"=>"resume-search"))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    
</div>

</div>

