<div class="wpjb wpjb-page-company-product-details">
    
    <?php wpjb_flash(); ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>

    <span style="line-height:2.6em; font-size:1.3em">
        <?php if($summary->expires_at == '9999-12-31'): ?>
            <?php _e("Never Expires", "wpjobboard") ?>
        <?php else: ?>
            <?php printf(__('Expires <strong>%1$s</strong>', "wpjobboard"), wpjb_date_display(get_option("date_format"), $summary->expires_at)) ?>
            <?php if($summary->days_left == 0): ?>
            <?php _e("(expires today)", "wpjobboard") ?>
            <?php else: ?>
            <?php echo sprintf( _n( '(1 day left)', '(%s days left)', $summary->days_left, 'wpjobboard' ), $summary->days_left ) ?>
            <?php endif; ?>
        <?php endif; ?>
    </span>
    
    <?php if($summary->updates_at): ?>
    <div class="wpjb-flash-info">
        <span><?php printf(__("Some credits will expire on <strong>%s</strong>.", "wpjobboard"), wpjb_date_display(get_option("date_format"), $summary->expires_at)) ?></span>
    </div>
    <?php endif ?>
    
    <div class="wpjb-grid wpjb-grid-compact">
        
            <?php 
                $package = $summary->bundle;
                $inc_grup = array(
                    Wpjb_Model_Pricing::PRICE_SINGLE_JOB => __("Job Postings Included", "wpjobboard"),
                    Wpjb_Model_Pricing::PRICE_SINGLE_RESUME => __("Resumes Access Included", "wpjobboard")
                );
            ?>
            <?php foreach($inc_grup as $k => $title): ?>
            <?php if(isset($package[$k]) && !empty($package[$k])): ?>
            <div class="wpjb-grid-row">
                <span class="" style="font-size:1.2em"><?php esc_html_e($title) ?></span>
            </div>
            <?php foreach($package[$k] as $usage): ?>
            <?php $product = new Wpjb_Model_Pricing($usage["id"]) ?>
            <div class="wpjb-grid-row">
                <div class="wpjb-col-50">
                    <?php esc_html_e($product->title) ?>
                </div>
                <div class="wpjb-col-50">
                    <span>
                        <?php if($usage["status"] == "unlimited"): ?>
                            <?php _e("Unlimited", "wpjobboard") ?>
                        <?php else: ?>
                            <?php echo $usage["used"]."/".$usage["usage"] ?>
                            <?php printf(__("(%d left)", "wpjobboard"), $usage["usage"]-$usage["used"]) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            <?php endforeach; ?>
        
    </div>
    
    <div style="margin:10px 0px">
        <a href="<?php esc_attr_e(wpjb_link_to("membership", null, array("purchase"=>$pricing->id))) ?>" class="wpjb-button"><?php _e("Renew", "wpjobboard") ?></a>
        <a href="<?php esc_attr_e(wpjb_link_to("membership")) ?>" class="wpjb-button"><?php _e("Go Back", "wpjobboard") ?></a>
    </div>
</div>