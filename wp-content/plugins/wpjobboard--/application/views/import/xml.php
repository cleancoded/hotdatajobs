<?php wp_enqueue_script("wpjb-admin-import") ?>
<style type="text/css">
    .wpjb-import-log-table {
        margin-top: 10px;
    }
    .wpjb-import-log .dashicons {
        vertical-align: middle;
        padding-right: 1em;
    }
    .wpjb-import-item-type {
        padding-right: 1em;
        text-transform: capitalize;
        width: 100px;
        display: inline-block;
    }
    .wpjb-import-log-link {
        padding: 0;
        margin: 0;
        float: right;
    }
    .wpjb-old-queue {
        margin: 20px 0px 20px 0;
        padding: 10px 10px;
        background-color: white;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    .wpjb-import-error-label,
    .wpjb-old-queue-warning {
        color: darkred;
        font-weight: bold;
        font-size: 1.1em
    }
    .wpjb-old-queue-list {
        font-size: 1.1em;
        text-decoration: none;
    }
    
</style>

<script type="text/javascript">
// Custom example logic

var WpjbInterval = null;
var WpjbImportBusy = false;
var WpjbImportIteration = 0;
var WpjbImportTotal = 0;
var WpjbImportUrl = {
    clearqueue: "<?php echo html_entity_decode(wpjb_admin_url("import", "clearqueue", null, array("noheader"=>1))) ?>",
    upload: "<?php echo html_entity_decode(wpjb_admin_url("import", "xmlupload", null, array("noheader"=>1))) ?>",
    count: "<?php echo html_entity_decode(wpjb_admin_url("import", "xmlcount", null, array("noheader"=>1))) ?>",
    import: "<?php echo html_entity_decode(wpjb_admin_url("import", "xmlimport", null, array("noheader"=>1))) ?>"
};
var WpjbImport = {
    filters: [
        <?php if($canUnzip): ?>
        {title : "WPJobBoard XML Zip", extensions : "zip"},     
        <?php endif; ?>
        {title : "WPJobBoard XML", extensions : "xml"}
    ]
};

</script>

<div class="wrap wpjb">

<h1>
    <?php _e("Import", "wpjobboard") ?> 
</h1>

    <?php $this->_include("flash.php"); ?>

    <?php if(!empty($queue)): ?>
    <div class="wpjb-old-queue">
        <span class="wpjb-old-queue-warning">
            <?php _e("Wait! There are some files in import directory (most likely from old or failed import). Move or delete them before starting new import.", "wpjobboard") ?>
        </span>
        
        <ul class="wpjb-old-queue-list">
            <?php foreach($queue as $q): ?>
            <li data-filename="<?php echo esc_html($q["filename"]) ?>">
                <span class="dashicons dashicons-download"></span>
                <a href="<?php echo esc_attr($q["url"]) ?>"><?php echo esc_html($q["filename"]) ?></a>
            </li>
            <?php endforeach; ?>
            
        </ul>
        
        <div class="wpjb-old-queue-delete-fail wpjb-none">
            
        </div>
        
        <a href="#" class="button wpjb-old-queue-close" style="display: none"><?php _e("Close", "wpjobboard") ?></a>
        <a href="#" class="button wpjb-old-queue-delete"><?php _e("Delete ALL", "wpjobboard") ?></a>
        <img class="ajax-loading" src="<?php esc_attr_e(admin_url("/images/wpspin_light.gif")) ?>" />

    </div>
    <?php endif; ?>
    
    
<form action="" method="post" enctype="multipart/form-data">



<div id="container">
	
    <br />
    
    <a href="#" id="pickfiles" class="button">
        <span class="wpjb-upload-empty"><?php _e("Select File", "wpjobboard") ?></span>
    </a>

    <div id="filelist" style="margin: 15px 0 15px 0; font-size:12px"></div>
    <div id="importlist" style="margin: 15px 0 15px 0; font-size:12px"></div>
    <div id="importsuccess" style="display:none"><?php _e("Done! You can close this window now.", "wpjobboard") ?></div>

    <input style="display:none" id="uploadfiles" type="submit" value="<?php _e("Upload and start import", "wpjobboard") ?>" class="button-primary" name="Submit"/>
    <img id="ajax-loading-img" class="ajax-loading" src="<?php esc_attr_e(admin_url("/images/wpspin_light.gif")) ?>" />

    <div class="wpjb-import-error wpjb-none">
        <span class="wpjb-import-error-label">Error occured while importing.</span>
        <div class="wpjb-import-error-response">

        </div>
    </div>

    <table class="widefat striped wpjb-import-log-table">
        <tbody class="wpjb-import-log">

        </tbody>
    </table>
    
</div>
			
    
</form>



</div>


