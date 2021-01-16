
<div class="wpjb wpjb-page-company-home">

    <?php wpjb_flash() ?>
    
    <?php do_action("wpjb_employer_panel_heading", "top") ?>
    
    <?php foreach($dashboard as $gname => $group): ?>
    <div class="wpjb-boxes">
        <div class="wpjb-boxes-group <?php echo "wpjb-boxes-group-$gname" ?>">
            <span class="wpjb-boxes-group-text"><?php echo esc_html($group["title"]) ?></span>
        </div>
        <?php foreach($group["links"] as $lname => $link): ?>
        <a class="wpjb-box wpjb-layer-inside <?php echo "wpjb-box-$lname" ?>" href="<?php echo esc_attr($link["url"]) ?>">
            <span class="wpjb-box-icon wpjb-glyphs <?php echo esc_attr($link["icon"]) ?>"></span>
            <span class="wpjb-box-title">
                <?php echo esc_html($link["title"]) ?>
                <?php do_action("wpjb_employer_panel_after_title", $lname, $link ) ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    
    <?php do_action("wpjb_employer_panel_heading", "bottom") ?>
    
</div>