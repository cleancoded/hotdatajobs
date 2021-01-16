<div class="wpjb wpjr-page-my-home">

    <?php wpjb_flash() ?>
    
    <?php if(is_object(Wpjb_Model_Resume::current())): ?>
    <?php $completed = Wpjb_Model_Resume::current()->completed() ?>
    <div class="wpjb-layer-inside" style="padding:10px; overflow:hidden;clear:both;">
        <div style="text-align:center">
            <span style="font-size:24px;line-height:48px;text-align: center"><?php echo sprintf(__("Profile Completion (%d%%)", "wpjobboard"), $completed) ?></span>
        </div>

        <div style="">
            <div class="progress-bar blue stripes">
                <span style="width: <?php echo $completed ?>%"></span>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php do_action("wpjb_candidate_panel_heading", "top") ?>
    
    <?php foreach($dashboard as $gname => $group): ?>
    <div class="wpjb-boxes">
        <div class="wpjb-boxes-group <?php echo "wpjb-boxes-group-$gname" ?>">
            <span class="wpjb-boxes-group-text"><?php esc_html_e($group["title"]) ?></span>
        </div>
        <?php foreach($group["links"] as $lname => $link): ?>
        <a class="wpjb-box wpjb-layer-inside <?php echo "wpjb-box-$lname" ?>" href="<?php esc_attr_e($link["url"]) ?>">
            <span class="wpjb-box-icon wpjb-glyphs <?php esc_attr_e($link["icon"]) ?>"></span>
            <span class="wpjb-box-title"><?php echo esc_html($link["title"]) ?></h4>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    
    <?php do_action("wpjb_candidate_panel_heading", "bottom") ?>

</div>