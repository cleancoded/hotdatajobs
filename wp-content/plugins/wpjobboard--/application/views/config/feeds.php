<div class="wrap wpjb">
    <h1><?php _e("Aggregators and RSS Feeds", "wpjobboard") ?> </h1>

</div>

<div class="wpjb">
    

<div class="clear">&nbsp;</div>

    <div id="poststuff" >
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                <div class="postbox " id="">
                    <h3 class="hndle"><span><?php esc_html_e("Job Aggregators", "wpjobboard") ?></span></h3>
                    <div class="inside" style="overflow:hidden">
                        
                        <?php foreach($agg as $k => $v): ?>
                        <div style="clear:both">
                        <div style="float:left; width:20%; line-height: 30px"><?php echo esc_html($v) ?></div>
                        <input style="float:left; width:79%" type="text" value="<?php esc_attr_e(wpjb_api_url("xml/$k")) ?>" class="wpjb-rss-select" />
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
                
                
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox " id="">
                    <h3 class="hndle"><span><?php esc_html_e("Available Feeds", "wpjobboard") ?></span></h3>
                    <div class="inside" style="overflow:hidden">
                        
                        <div style="clear:both">
                        <div style="float:left; width:20%; line-height: 30px"><?php esc_html_e("All", "wpjobboard") ?></div>
                        <input style="float:left; width:79%" type="text" value="<?php esc_attr_e(wpjb_api_url("xml/rss")) ?>" class="wpjb-rss-select" />
                        </div>
                        
                        <?php foreach(wpjb_get_categories() as $category): ?>
                        <div style="clear:both">
                        <div style="float:left; width:20%; line-height: 30px"><?php echo $category->title ?></div>
                        <input style="float:left; width:79%" type="text" value="<?php esc_attr_e(wpjb_api_url("xml/rss")."?category=".$category->slug) ?>" class="wpjb-rss-select" />
                        </div>
                        <?php endforeach; ?>
  
                    </div>
                </div>
            </div>
                
                
            </div>
        </div>
    </div>


    
</div>

<script type="text/javascript">
jQuery(function($) {
    $("input.wpjb-rss-select").click(function() {
        this.select();
    });
});  
</script>