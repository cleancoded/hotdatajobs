<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="<?php echo site_url() ?>'/wp-content/plugins/wpjobboard/application/vendor/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?php echo site_url() ?>/wp-content/plugins/wpjobboard/application/vendor/flot/jquery.flot.min.js"></script>

<div style="float:left;margin-top:5px">
<img src="<?php echo site_url() ?>/wp-content/plugins/wpjobboard/application/public/chart-bar.png" alt=""/>
<?php _e("orders count", "wpjobboard") ?>
&nbsp; &nbsp;
<img src="<?php echo site_url() ?>/wp-content/plugins/wpjobboard/application/public/chart-line.png" alt=""/>
<?php _e("revenue", "wpjobboard") ?>
</div>

<div style=" overflow:hidden; margin-bottom:20px">
    <select name="wpjb_dashboard_stats" id="wpjb_dashboard_period" style="float:right">
        <option value="1"><?php _e("last 7 days", "wpjobboard") ?></option>
        <option value="2"><?php _e("last 30 days", "wpjobboard") ?></option>
    </select>
    
    <select name="wpjb_dashboard_currency" id="wpjb_dashboard_currency" style="float:right">
    <?php foreach($currency as $k => $c): ?>
        <option value="<?php echo $k ?>"><?php esc_html_e($c) ?></option>
    <?php endforeach; ?>
    </select>
    
</div>

<div id="wpjb_dashboard_placeholder" style="height:160px;"></div>

<div class="wpjb-dashboard-info-wrap">
    <div class="wpjb-dashboard-info-cell">
        <span class="wpjb-dashboard-info-desc"><?php _e("Revenue", "wpjobboard") ?></span>
        <span id="wpjb_dashboard_info_revenue">-</span>
    </div>
    
    <div  class="wpjb-dashboard-info-cell">
        <span class="wpjb-dashboard-info-desc"><?php _e("Orders count", "wpjobboard") ?></span>
        <span id="wpjb_dashboard_info_orders">-</span>
    </div>
    
    <div class="wpjb-dashboard-info-cell" style="border-right:0">
        <span class="wpjb-dashboard-info-desc">
            <?php _e("Source", "wpjobboard") ?>
            <span style="color:#999999"><?php _e("jobs/resumes", "wpjobboard") ?></span>
        </span>
        <span>
            <span id="wpjb_dashboard_info_job">-</span>
            /
            <span id="wpjb_dashboard_info_resumes">-</span>
        </span>
    </div>
</div>

<script type="text/javascript">
WpjbDashboard.Init();
</script>