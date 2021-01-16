<div class="wpjb wpjr-page-my-applications">

    <?php wpjb_flash() ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>
    
    <div class="wpjb-grid wpjb-grid wpjb-grid-compact">
        <?php if ($result->count > 0): ?>
        <div class="wpjb-grid-row wpjb-grid-head">
            <div class="wpjb-col-65"><?php _e("Job", "wpjobboard") ?></div>
            <div class="wpjb-col-20"><?php _e("Expires", "wpjobboard") ?></div>
            <div class="wpjb-col-15 wpjb-grid-col-right"><?php _e("Status", "wpjobboard") ?></div>
        </div>
        <?php foreach($result->shortlist as $item): ?>
        <?php /* @var $item Wpjb_Model_Shortlist */ ?>
        <?php $object = $item->getObject() ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-65">
                <?php if($object && (in_array(Wpjb_Model_Job::STATUS_ACTIVE, $object->status()) || in_array(Wpjb_Model_Job::STATUS_INACTIVE, $object->status()) || in_array(Wpjb_Model_Job::STATUS_EXPIRED, $object->status()))): ?>
                <a href="<?php esc_attr_e(wpjb_link_to("job", $object)) ?>"><?php esc_html_e($object->job_title) ?></a>
                <?php echo join(" ", wpjb_bulb($object)) ?>
                <?php else: ?>
                <?php _e("Job is inactive or deleted.", "wpjobboard") ?>
                <?php endif; ?>
            </div>
            <div class="wpjb-col-20">
                <?php if($object && $object->job_expires_at == WPJB_MAX_DATE): ?>
                <?php _e("Never", "wpjobboard") ?>
                <?php elseif($object): ?>
                <?php esc_html_e(wpjb_date_display(get_option('date_format'), $object->job_expires_at)) ?>
                <?php else: ?>
                —
                <?php endif; ?>
            </div>
            <div class="wpjb-col-15 wpjb-grid-col-right">
                <a href="<?php esc_attr_e(wpjb_api_url("action/bookmark", array("object"=>"job", "object_id"=>$item->object_id, "redirect_to"=>$url, "do"=>"delete"))) ?>">
                    <?php _e("Delete", "wpjobboard") ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="wpjb-grid-row">
            <div class="wpjb-col-100 wpjb-grid-col-center"><?php _e("You do not have any jobs shortlisted.", "wpjobboard"); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="wpjb-paginate-links">
        <?php wpjb_paginate_links($url, $result->pages, $result->page, $query) ?>
    </div>

</div>