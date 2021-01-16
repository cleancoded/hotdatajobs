

<div class="wpjb-grid-row wpjb-click-area <?php if( $resume->featured_level ): ?> wpjb-featured-resume wpjb-featured-resume-<?php echo $resume->featured_level; ?> <?php endif; ?>">
    <div class="wpjb-grid-col wpjb-col-logo wpjb-logo-round">
        <?php if($resume->doScheme("image")): ?>
        <?php elseif($resume->getAvatarUrl()): ?>
        <div class="wpjb-img-50">
            <img src="<?php echo $resume->getAvatarUrl("50x50") ?>" alt="" class="" />
        </div>
        <?php else: ?>
        <div class="wpjb-img-50 wpjb-icon-none">
            <span class="wpjb-glyphs wpjb-icon-user wpjb-icon-50"></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="wpjb-grid-col wpjb-col-main wpjb-col-title">
        <div class="wpjb-line-major">
            <a href="<?php echo wpjr_link_to("resume", $resume) ?>" class="wpjb-title wpjb-candidate_name"><?php echo esc_html(apply_filters("wpjb_candidate_name", $resume->getSearch(true)->fullname, $resume->id)) ?></a>
        
            <span class="wpjb-sub-title wpjb-sub-opaque">
                <span class="wpjb-glyphs wpjb-icon-location">
                    <?php if($resume->locationToString()): ?>
                    <?php esc_html_e($resume->locationToString()) ?>
                    <?php else: ?>
                    &#2014;
                    <?php endif; ?>
                </span>
            </span>
        </div>

        <div class="wpjb-line-minor">
            
            
            <?php if($resume->doScheme("headline")): else: ?>
            <span class="wpjb-sub"><?php echo esc_html($resume->headline) ?></span>
            <?php endif; ?>
            
            <span class="wpjb-sub wpjb-sub-right wpjb-resume_modified_at">
                <?php echo wpjb_date_display("M, d", $resume->modified_at, true); ?>
            </span>
        </div>
        
    </div>
</div>
