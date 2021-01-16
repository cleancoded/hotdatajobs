<div class="wrap wpjb">

<h1><?php _e("Pricing", "wpjobboard") ?> </h1>

<div class="clear">&nbsp;</div>

<div class="wpjb-config-list">
    <?php foreach($list->getAll() as $pricing): ?>
    <div class="wpjb-pricing-box">
        <h3><?php echo esc_html($pricing["title"]) ?></h3>
        <a href="<?php echo wpjb_admin_url("pricing", "list", null, array("listing"=>$pricing["name"])) ?>" class="button wpjb-pricing-button"><?php _e("View All", "wpjobboard") ?> (<?php echo $count[$pricing["id"]] ?>)</a>
        <a href="<?php echo wpjb_admin_url("pricing", "add", null, array("listing"=>$pricing["name"])) ?>" class="button wpjb-pricing-button"><?php _e("Add New ...", "wpjobboard") ?></a>
    </div>
    <?php endforeach; ?>
    

</div>


</div>