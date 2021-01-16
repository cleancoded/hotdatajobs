<?php wp_enqueue_style( 'wpjb-glyphs' ) ?>
<div class="wrap wpjb">


    
<?php foreach($config as $group): ?>
    <?php if(!isset($group["item"]) || empty($group["item"])) continue; ?>
    
    <br class="clear" />
    <h1><?php esc_html_e($group["title"]) ?> </h1>
    
    <div class="clear">&nbsp;</div>

    <div class="wpjb-config-list">
    <?php foreach($group["item"] as $item): ?>
    <div class="wpjb-pricing-box">
        <h3>
            <?php if(isset($item["image"])): ?>
            <img src="<?php esc_attr_e($item["image"]) ?>" style="max-width: 16px;" />
            <?php elseif(isset($item["icon"])): ?>
            <span class="wpjb-glyphs <?php esc_attr_e($item["icon"]) ?>"></span>
            <?php endif; ?>
            
            <?php esc_html_e($item["title"]) ?>
        </h3>
        <?php
            if(!isset($item["action"]) || empty($item["action"])) {
                $item_action = "edit";
            } else {
                $item_action = $item["action"];
            }
        ?>
        <a href="<?php esc_attr_e(wpjb_admin_url("config", $item_action, null, array("form"=>$item["form"]))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    <?php endforeach; ?>
    </div>
    
<?php endforeach; ?>

<br class="clear" />
<h1><?php _e("Payment Methods", "wpjobboard") ?> </h1>
<div class="clear">&nbsp;</div>

<div class="wpjb-config-list">
    <?php foreach(Wpjb_Project::getInstance()->payment->getEngines() as $engine): ?>
    <?php $engine = new $engine; ?>
    <?php if($engine->getForm()): ?>
    <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs <?php esc_attr_e($engine->getIcon()) ?>"></span>
            <?php esc_html_e($engine->getTitle()) ?> <?php if($engine->conf("disabled") == 1): ?><em><?php _e("(disabled)", "wpjobboard") ?></em><?php endif; ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("config", "payment", null, array("engine"=>$engine->getEngine()))) ?>" class="button wpjb-pricing-button"><?php _e("Edit ...", "wpjobboard") ?></a>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
</div>

<br class="clear" />
<h1><?php _e("Other", "wpjobboard") ?> </h1>
<div class="clear">&nbsp;</div>
<div class="wpjb-config-list">
    <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-ambulance"></span>
            <?php _e("Health Check", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("config", "health")) ?>" class="button wpjb-pricing-button"><?php _e("View", "wpjobboard") ?></a>
    </div>
</div>
<div class="wpjb-config-list">
    <div class="wpjb-pricing-box">
        <h3>
            <span class="wpjb-glyphs wpjb-icon-rss"></span>
            <?php _e("Aggregators and RSS Feeds", "wpjobboard") ?>
        </h3>
        <a href="<?php esc_attr_e(wpjb_admin_url("config", "feeds")) ?>" class="button wpjb-pricing-button"><?php _e("View", "wpjobboard") ?></a>
    </div>
</div>



