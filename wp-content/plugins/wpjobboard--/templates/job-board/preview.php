<div class="wpjb wpjb-page-preview">

    <?php wpjb_flash(); ?>

    <?php if($canPost): ?>
    <?php include $this->getTemplate("job-board", "step") ?>
    <h2><?php esc_html_e($job->job_title) ?></h2>
    <?php include $this->getTemplate("job-board", "job"); ?>

    <div style="text-align:left; margin:20px 0 0 0; padding: 20px 0 0 0; border-top: 3px solid whitesmoke">
        <p>
            <a class="wpjb-button" href="<?php esc_attr_e($urls->add) ?>">&#171; <?php _e("Edit Listing", "wpjobboard") ?></a> &nbsp;
            <a class="wpjb-button" href="<?php esc_attr_e($urls->save); ?>"><?php _e("Publish Listing", "wpjobboard") ?> &raquo;</a>
        <p>
    </div>
    <?php endif; ?>

</div>
