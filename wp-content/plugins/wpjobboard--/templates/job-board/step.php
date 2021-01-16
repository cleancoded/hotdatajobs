<?php if($mode == "standalone"): ?>
<ul class="wpjb-add-job-steps">
    <li <?php if($current_step==1): ?>class="wpjb-step-current wpjb-motif-border-bottom"<?php endif; ?>>
        <span class="wpjb-glyphs wpjb-icon-right-big"></span>
        <?php esc_html_e(wpjb_conf("seo_step_1", $steps[1])) ?>
    </li>
    <li <?php if($current_step==2): ?>class="wpjb-step-current wpjb-motif-border-bottom"<?php endif; ?>>
        <span class="wpjb-glyphs wpjb-icon-right-big"></span>
        <?php esc_html_e(wpjb_conf("seo_step_2", $steps[2])) ?>
    </li>
    <li <?php if($current_step==3): ?>class="wpjb-step-current wpjb-motif-border-bottom"<?php endif; ?>>
        <span class="wpjb-glyphs wpjb-icon-right-big"></span>
        <?php esc_html_e(wpjb_conf("seo_step_3", $steps[3])) ?>
    </li>
</ul>
<?php else: ?>
<?php do_action( "wpjb_jobs_add_steps", $current_step, $this->view ) ?>
<?php endif; ?>
