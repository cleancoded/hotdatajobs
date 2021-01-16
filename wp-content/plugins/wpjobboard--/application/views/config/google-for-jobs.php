
<div class="wrap wpjb">
    <h1>
        <?php echo __("Google For Jobs", "wpjobboard") ?>
        <a class="add-new-h2" href="<?php echo wpjb_admin_url("config"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>
    

<?php $this->_include("flash.php"); ?>
   
<?php  

$vars = new Wpjb_List_EmailVars;
$fields = array();

$job_data = $vars->objectJob();
$company_data = $vars->objectCompany();

foreach($vars->objectJob() as $key => $section) {
    foreach($section["data"] as $data) {
        $fields[] = array(
            "key" => "job__".$key."__".$data["name"],
            "label" => "Job / " . $section["header"] . " / ". $data["title"]
        );
    }
}

foreach($vars->objectCompany() as $key => $section) {
    foreach($section["data"] as $data) {
        $fields[] = array(
            "key" => "company__".$key."__".$data["name"],
            "label" => "Company / " . $section["header"] . " / ". $data["title"]
        );
    }
}

$fields[] = array(
    "key" => "null",
    "label" => __("Other / Unset Value For This Field", "wpjobboard")
);
$fields[] = array(
    "key" => "text",
    "label" => __("Other / Static Text")
);

$config = wpjb_conf("google_for_jobs");
$isDisabled = false;

if(isset($config["is_disabled"]) && $config["is_disabled"] == 1) {
    $isDisabled = true;
}

?>
    
<form action="" method="post" enctype="multipart/form-data" class="wpjb-form">
    <div id="poststuff" >
        <div id="post-body" class="metabox-holder columns-2">
            
            <div id="post-body-content" style="">
                
                <div class="postbox wpjb-form-postbox wpjb-namediv wpjb-form-group-types-map-intro">
                    <h3>
                        <?php echo __("Google For Jobs", "wpjobboard") ?>
                    </h3>
                    <div class="inside " style="    padding: 12px 12px;">
                        <div style="margin-bottom: 6px">
                            <input type="checkbox" id="disable_google_jobs" name="disable_google_jobs" value="1" <?php checked($isDisabled) ?> />
                            <label for="disable_google_jobs">
                                Disable Google Jobs integration.
                            </label>
                        </div>
                        
                    </div>

                </div>
                
                <div class="postbox wpjb-form-postbox wpjb-namediv wpjb-form-group-types-map-intro">
                    <h3>
                        <?php echo __("Employment Type Map", "wpjobboard") ?>
                    </h3>
                    <div class="inside " style="    padding: 12px 12px;">
                        <div style="margin-bottom: 6px">
                            The fields below allow you to match Google Job Types (field labels) to 
                            Job Types (dropdowns) saved in your database.
                        </div>
                        
                        <div style="margin-bottom: 6px">
                            Mapping job types is optional but highly recommended (and takes only few seconds).
                        </div>

                        <div>
                            <a href='#' class='button wpjb-gfj-toggle-types'>show fields <span class='dashicons dashicons-arrow-down-alt2'></span></a>
                            <span class="wpjb-gfj-toggle-set-wrap"><span class="wpjb-gfj-toggle-set">0</span> out of 8 set</span>
                        </div>
                    </div>

                </div>
                
                <div class="postbox wpjb-form-postbox wpjb-namediv wpjb-form-group-description-template">
                    <h3>
                        <?php echo __("Job Description Template", "wpjobboard") ?>
                    </h3>
                    <div class="inside wpjb-gfj-jdt-intro" style="padding: 12px 12px;">
                        <div style="margin-bottom: 6px">
                            Modifying job description template allows you to set how the job details will be
                            visible in the Google Jobs search results.
                        </div>
                        
                        <div style="margin-bottom: 6px">
                            By default the Job Description only includes text from the Job Description field
                            but by using variables you can also include custom fields data.
                        </div>

                        <div>
                            <a href='#' class='button wpjb-gfj-jdt-button-edit'>Edit</a>
                        </div>
                    </div>
                    
                    <div class="inside wpjb-gfj-jdt-edit" style="padding: 12px 12px; display: none">
                        <div style="margin-bottom: 6px">
                            <textarea class="wpjb-gfj-jdt-textarea"><?php echo (isset($config["template"]) && $config["template"]) ? esc_html($config["template"]) : '{$job.job_description}' ?></textarea>
                        </div>
                        <div style="margin-bottom: 6px">
                            <?php _e( "NOTE: In the description template you can use the same variables as in the Settings (WPJB) / Emails panel.", "wpjobboard") ?>
                        </div>
                        <div style="margin-bottom: 6px">
                            <a href="#" class="button wpjb-gfj-jdt-button-ok">OK</a>
                        </div>
                    </div>
                </div>
                
                <?php daq_form_layout($form) ?>
                
                <div id="wpjb-gfj-global-error" class="wpjb-gfj-error wpjb-none">
                    <span class="dashicons dashicons-warning"></span>
                    <span class="wpjb-gfj-global-error-text"></span>
                    <span style="float:right">
                        <a href="#" class="wpjb-global-close"><span class="dashicons dashicons-no"></span></a>
                    </span>
                </div>
                <div id="wpjb-gfj-global-success" class="wpjb-none">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e("Config Saved", "wpjobboard") ?>
                    <span style="float:right">
                        <a href="#" class="wpjb-global-close"><span class="dashicons dashicons-no"></span></a>
                    </span>
                </div>
                
                <div class="postbox wpjb-form-postbox wpjb-namediv wpjb-form-group-preview">
                    <?php if(wpjb_find_jobs(array("filter"=>"all", "count_only"=>1, "count"=>1, "page"=>1)) > 0): ?>
                    <h3>
                        Preview Using Job '<em class="wpjb-gfj-preview-title">...</em>'
                        <a href="#" class="wpjb-gfj-preview-view" style="font-weight: normal; margin-left:6px">view</a>
                        <a href="#" class="wpjb-gfj-preview-change" style="font-weight: normal; margin-left:6px">use different job</a>
                    </h3>
                    <div class="inside wpjb-gfj-preview-editor">
                        <span class="wpjb-gfj-preview-editor-actions-left">
                            <a href="#" class="wpjb-gfj-preview-save button-primary" style="margin-right: 10px">Save</a>
                            <a href="#" class="wpjb-gfj-preview-refresh button">Refresh</a>
                            <a href="#" class="wpjb-gfj-preview-validate button">Validate</a>
                            <span class="wpjb-gfj-loader" style="display: none;"><img src="<?php echo admin_url() ?>/images/wpspin_light.gif" alt="" /></span>
                        </span>
                        
                        <span  class="wpjb-gfj-preview-editor-actions-right">
                            <label>
                                <span style="opacity: 0.75; margin-right:4px">Automatically refresh scheme</span>
                                <form>
                                    <input class="wpjb-gfj-preview-auto" type="checkbox" checked="checked" />
                                </form>
                            </label>
                        </span>
                        
                        <div id="wpjb-gfj-preview-error">
                            <div class="wpjb-gfj-job-more">

                                <div class="wpjb-gfj-job-required wpjb-gfj-job-block">
                                    <span class="wpjb-gfj-job-block-text">
                                    <?php _e("The below fields are <strong>required</strong>.", "wpjobboard") ?>
                                    </span>
                                    <ul class="wpjb-gfj-job-block-list"></ul>
                                </div>

                                <div class="wpjb-gfj-job-recommended wpjb-gfj-job-block">
                                    <span class="wpjb-gfj-job-block-text">
                                    <?php _e("The below fields are <strong>recommended</strong>. Fill them if possible.", "wpjobboard") ?>
                                    </span>
                                    <ul class="wpjb-gfj-job-block-list"></ul>
                                </div>

                            </div>
                        </div>
                        
                        <div id="wpjb-gfj-ace" class="wpjb-gfj-ace"></div>
                        
                        <form action="https://search.google.com/structured-data/testing-tool" method="post" target="_blank" class="wpjb-gfj-submit">
                            <input type="hidden" class="wpjb-gfj-form-input" name="code" value="" />
                        </form>
                    </div>
                    
                    <div class="inside wpjb-gfj-preview-search">   
                        <div class="" style="margin:1em 0 0 0; padding:6px;">
                            <input id="wpjb-gfj-suggest" type="text" style="line-height:24px; width: 100%" placeholder="<?php _e("Start typing job title some suggestions will appear ...", "wpjobboard") ?>" /> 
                        </div>

                        <table class="widefat striped wpjb-import-log-table">
                            <tbody id="wpjb-gfj-suggestions" class="wpjb-import-log">

                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <h3><?php _e("Preview", "wpjobboard") ?></h3>
                    <div class="inside wpjb-gfj-preview-none">   
                        
                        <div class="inside" style="padding: 12px">
                            <?php echo sprintf(__("You need to <a href=\"%s\">add</a> at least one job to use preview.", "wpjobboard"), wpjb_admin_url("jobs", "add")) ?>
                        </div>
                        <div id="wpjb-gfj-ace" style="display: none"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            

        </div>
    </div>

</form>






</div>

<script type="text/html" id="tmpl-wpjb-gfj-map-Text">
    <div class="wpjb-gfj-item">
    <# if ( data._display == "edit" ) { #>
    <div class="wpjb-gfj-item-edit">
        <# if ( data.description ) { #>
        <span class="wpjb-description">{{ data.description }}.</span>
        <# } #>
        
        <div class="wpjb-gfj-error wpjb-none"></div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }}</strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value=""><?php _e("Select field to map ...", "wpjobboard") ?></option>

                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                    
                    
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        <div>
            <# if( data.isNew === true ) { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Add", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-discard"><?php _e("Discard", "wpjobboard") ?></a>
            <# } else { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Update", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
            <# } #>
        </div>
    </div>
    
    <# } else { #>
    
    <div class="wpjb-gfj-item-view">
        <span class="wpjb-gfj-item-view-map">
            <span class="dashicons {{ data._dashicon }}"></span>
            {{ data.label }}
            <span class="dashicons dashicons-arrow-right-alt2"></span>
            {{ data.value.label }}
        </span>
        
        <span  class="wpjb-gfj-item-view-actions" style="">
            <a href="#" class="button wpjb-gfj-item-action-edit"><?php _e("Edit", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-delete"><?php _e("Delete", "wpjobboard") ?></a>
        </span>
    </div>
    
    <input type="hidden" class="wpjb-gfj-map" name="{{ data._uid }}_{{ data.name }}" data-id="{{ data._uid }}" data-name="{{ data.name }}" value="{{ data.value.name }}" />
    
    <# } #>
    
    </div>
</script>

<script type="text/html" id="tmpl-wpjb-gfj-map-Identifier">
    <div class="wpjb-gfj-item">
    <# if ( data._display == "edit" ) { #>
    <div class="wpjb-gfj-item-edit">
        <# if ( data.description ) { #>
        <span class="wpjb-description">{{ data.description }}.</span>
        <# } #>
        
        <div class="wpjb-gfj-error wpjb-none"></div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Company Name", "wpjobboard" ) ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input"data-name="name" >
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.name.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.name.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Job Unique ID", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="value">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.value.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.value.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        <div>
            <# if( data.isNew === true ) { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Add", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-discard"><?php _e("Discard", "wpjobboard") ?></a>
            <# } else { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Update", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
            <# } #>
        </div>
    </div>
    
    <# } else { #>
    
    <div class="wpjb-gfj-item-view">
        <span class="wpjb-gfj-item-view-map">
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Company Name", "wpjobboard" ) ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.name.label }}
            </div>
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Job Unique ID", "wpjobboard") ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.value.label }}
            </div>
        </span>
        
        <span  class="wpjb-gfj-item-view-actions" style="">
            <a href="#" class="button wpjb-gfj-item-action-edit"><?php _e("Edit", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-delete"><?php _e("Delete", "wpjobboard") ?></a>
        </span>
    </div>
    
    <input type="hidden" class="wpjb-gfj-map" name="{{ data._uid }}_{{ data.name }}" data-id="{{ data._uid }}" data-name="{{ data.name }}" value="{{ data.value.name }}" />
    
    <# } #>
    
    </div>
</script>
    
<script type="text/html" id="tmpl-wpjb-gfj-map-Organization">
    <div class="wpjb-gfj-item">
    <# if ( data._display == "edit" ) { #>
    <div class="wpjb-gfj-item-edit">
        <# if ( data.description ) { #>
        <span class="wpjb-description">{{ data.description }}.</span>
        <# } #>
        
        <div class="wpjb-gfj-error wpjb-none"></div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Company Name", "wpjobboard" ) ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input"data-name="name" >
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.name.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.name.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("URL", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="sameAs">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.sameAs.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.sameAs.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Logo", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="logo">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.logo.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.logo.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        
        <div>
            <# if( data.isNew === true ) { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Add", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-discard"><?php _e("Discard", "wpjobboard") ?></a>
            <# } else { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Update", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
            <# } #>
        </div>
    </div>
    
    <# } else { #>
    
    <div class="wpjb-gfj-item-view">
        <span class="wpjb-gfj-item-view-map">
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Company Name", "wpjobboard" ) ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.name.label }}
            </div>
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("URL", "wpjobboard") ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.sameAs.label }}
            </div>
        </span>
        
        <span  class="wpjb-gfj-item-view-actions" style="">
            <a href="#" class="button wpjb-gfj-item-action-edit"><?php _e("Edit", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-delete"><?php _e("Delete", "wpjobboard") ?></a>
        </span>
    </div>
    
    <input type="hidden" class="wpjb-gfj-map" name="{{ data._uid }}_{{ data.name }}" data-id="{{ data._uid }}" data-name="{{ data.name }}" value="{{ data.value.name }}" />
    
    <# } #>
    
    </div>
</script>

<script type="text/html" id="tmpl-wpjb-gfj-map-Place">
    <div class="wpjb-gfj-item">
    <# if ( data._display == "edit" ) { #>
    <div class="wpjb-gfj-item-edit">
        <# if ( data.description ) { #>
        <span class="wpjb-description">{{ data.description }}.</span>
        <# } #>
        
        <div class="wpjb-gfj-error wpjb-none"></div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Street Address", "wpjobboard" ) ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="streetAddress" >
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.streetAddress.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.streetAddress.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("City", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="addressLocality">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.addressLocality.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.addressLocality.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Region", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="addressRegion">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.addressRegion.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.addressRegion.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Postal Code", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="postalCode">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.postalCode.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.postalCode.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Country", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="addressCountry">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.addressCountry.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.addressCountry.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        
        <div>
            <# if( data.isNew === true ) { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Add", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-discard"><?php _e("Discard", "wpjobboard") ?></a>
            <# } else { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Update", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
            <# } #>
        </div>
    </div>
    
    <# } else { #>
    
    <div class="wpjb-gfj-item-view">
        <span class="wpjb-gfj-item-view-map">
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Street Address", "wpjobboard" ) ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.streetAddress.label }}
            </div>
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("City", "wpjobboard") ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.addressLocality.label }}
            </div>
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Region", "wpjobboard" ) ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.addressRegion.label }}
            </div>
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Postal Code", "wpjobboard") ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.postalCode.label }}
            </div>
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Country", "wpjobboard") ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.addressCountry.label }}
            </div>
        </span>
        
        <span  class="wpjb-gfj-item-view-actions" style="">
            <a href="#" class="button wpjb-gfj-item-action-edit"><?php _e("Edit", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-delete"><?php _e("Delete", "wpjobboard") ?></a>
        </span>
    </div>
    
    <input type="hidden" class="wpjb-gfj-map" name="{{ data._uid }}_{{ data.name }}" data-id="{{ data._uid }}" data-name="{{ data.name }}" value="{{ data.value.name }}" />
    
    <# } #>
    
    </div>
</script>

<script type="text/html" id="tmpl-wpjb-gfj-map-MonetaryAmount">
    <div class="wpjb-gfj-item">
    <# if ( data._display == "edit" ) { #>
    <div class="wpjb-gfj-item-edit">
        <# if ( data.description ) { #>
        <span class="wpjb-description">{{ data.description }}.</span>
        <# } #>
        
        <div class="wpjb-gfj-error wpjb-none"></div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Value", "wpjobboard" ) ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input"data-name="value" >
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.value.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.value.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        
        <div class="wpjb-gfj-item-edit-inner">
            
            <div class="wpjb-gfj-item-edit-label">
                <strong>{{ data.label }} - <?php _e("Currency", "wpjobboard") ?></strong>
            </div>
            
            <div class="wpjb-gfj-item-edit-divider">
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </div>
            
            <div class="wpjb-gfj-item-edit-input" data-name="currency">
                <select class="wpjb-gfj-internal-field-map" name="internal_field_name">
                    <option value="inherit"><?php _e("Inherit", "wpjobboard") ?></option>
                    <?php foreach($fields as $key => $opt): ?>
                    <option <# if( data.value.currency.name == "<?php echo esc_attr($opt["key"]) ?>") { #>selected="selected"<# } #> value="<?php echo esc_attr($opt["key"]) ?>"><?php echo esc_html($opt["label"]) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" class="wpjb-gfj-internal-text-value" value="{{ data.value.currency.text }}" style="display: none" placeholder="<?php echo esc_attr(__("Enter text here ...", "wpjobboard")) ?>" />
            </div>
        </div>
        <div>
            <# if( data.isNew === true ) { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Add", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-discard"><?php _e("Discard", "wpjobboard") ?></a>
            <# } else { #>
            <a href="#" class="button wpjb-gfj-item-action-save"><?php _e("Update", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
            <# } #>
        </div>
    </div>
    
    <# } else { #>
    
    <div class="wpjb-gfj-item-view">
        <span class="wpjb-gfj-item-view-map">
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("Company Name", "wpjobboard" ) ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.value.label }}
            </div>
            <div>
                <span class="dashicons {{ data._dashicon }}"></span>
                {{ data.label }} - <?php _e("URL", "wpjobboard") ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
                {{ data.value.currency.label }}
            </div>
        </span>
        
        <span  class="wpjb-gfj-item-view-actions" style="">
            <a href="#" class="button wpjb-gfj-item-action-edit"><?php _e("Edit", "wpjobboard") ?></a>
            <a href="#" class="button wpjb-gfj-item-action-delete"><?php _e("Delete", "wpjobboard") ?></a>
        </span>
    </div>
    
    <input type="hidden" class="wpjb-gfj-map" name="{{ data._uid }}_{{ data.name }}" data-id="{{ data._uid }}" data-name="{{ data.name }}" value="{{ data.value.name }}" />
    
    <# } #>
    
    </div>
</script>
    
<script type="text/html" id="tmpl-wpjb-gfj">
    <tr>
        <td class="import-system row-title">
            <a href="{{ data.admin_url }}" style="line-height: 28px;">{{ data.job_title }}</a>
            <span style="float:right">
                <a href="#" class="wpjb-gfj-item-select button" ><?php _e("Select", "wpjobboard") ?></a>
            </span>
        </td>
    </tr>
</script>

<?php

$gconf = wpjb_conf("google_for_jobs");
$gmap = array();

if(isset($gconf["jsonld"]) && is_array($gconf["jsonld"])) {
    $gmap = $gconf["jsonld"];
}

?>

<script type="text/javascript">
var WPJB_GFJ_DATA = {
    FORCED_ID: <?php echo (int)Daq_Request::getInstance()->get("forced-id") ?>,
    MAP: <?php echo json_encode($gmap) ?>,
    FIELD: <?php echo json_encode($fields) ?>
}; 
</script>


<style type="text/css">
    .wpjb-form-group-shortcoded label {
        padding: 3px 5px 2px;
        margin: 0 1px;
        background: #eaeaea;
        background: rgba(0,0,0,.07);
        font-size: 13px;
        line-height: 19px;
        display: inline-block;
        font-family: Consolas,Monaco,monospace !important;
        unicode-bidi: embed;
    }
    .wpjb-form .wpjb-form-group-map .wpjb-td-first {
        display: none;
    }
    .wpjb-gfj-item-edit {
        margin: 12px 0 12px 0;; padding:12px;background-color:whitesmoke
    }
    
    .wpjb-gfj-item-edit .wpjb-description {
        padding:6px 0 6px 0; margin:0
    }
    
    .wpjb-gfj-item-edit .wpjb-gfj-item-edit-inner {
        clear: both; overflow: hidden;
    }
    
    .wpjb-gfj-item-edit .wpjb-gfj-item-edit-inner .wpjb-gfj-item-edit-label {
        width: 40%; float: left; padding: 2px; line-height: 28px; height: 28px;
    }
    
    .wpjb-gfj-item-edit .wpjb-gfj-item-edit-inner .wpjb-gfj-item-edit-divider {
        width:3%; float: left
    }
    
    .wpjb-gfj-item-edit .wpjb-gfj-item-edit-inner .wpjb-gfj-item-edit-input {
        width:50%;float:right;
    padding-right: 1px;
    }
    
    .wpjb-gfj-item-view {
        margin: 4px 0 4px 0;
        padding: 3px; 
        line-height:28px; 
        cursor: move; /* fallback if grab cursor is unsupported */
        cursor: grab;
        cursor: -moz-grab;
        cursor: -webkit-grab;
        border: 2px solid transparent;
    }
    
    .wpjb-gfj-item-view:hover {
        border: 2px dashed whitesmoke;
    }

    .wpjb-gfj-item-view:active {
        cursor: grabbing;
        cursor: -moz-grabbing;
        cursor: -webkit-grabbing;
    }

    .wpjb-gfj-item-view .wpjb-gfj-item-view-map {
        display:inline-block
    }
    
    .wpjb-gfj-item-view .wpjb-gfj-item-view-actions {
        float:right; display:inline-block
    }
    
    .wpjb-gfj-item span.dashicons {
            padding-top: 4px;

    }
    
    .wpjb-gfj-preview-editor {
        display: none;
    }
    
    .wpjb-gfj-preview-editor-actions-left {
        line-height: 28px;
    padding: 10px 0 0 12px;
    display: inline-block;
    font-size: 14px;
    }
    
    .wpjb-gfj-preview-editor-actions-right {
        line-height:28px; float: right; display:inline-block;padding: 10px 12px 0 12px;
    }
                            
    .wpjb-gfj-preview-editor-actions-left strong {
    padding: 0 5px;
    }
    
    .wpjb-gfj-preview-editor #wpjb-gfj-ace {
        height:500px;margin: 12px 12px 0 12px; 
    }
    
    .wpjb-gfj-toggle-set-wrap {
        line-height: 28px;
        margin-left: 12px;
    }
    
    a.button.wpjb-gfj-toggle-types span.dashicons {
        vertical-align: middle;
    }
    .wpjb-gfj-error {
        border: 1px solid #DE5400;
        background-color: #f04124;
        padding: 2px 4px;
        color: white;
        margin: 4px 0px;
        width: 100%;
        box-sizing: border-box;
    }
    
    #wpjb-gfj-global-error {
        padding: 8px 8px;
    }
    
    #wpjb-gfj-global-success {
        background-color: #5b9dd9;
        padding: 8px 8px;
        color: white;
        margin: 4px 0px;
        width: 100%;
        box-sizing: border-box;
    }
    
    #wpjb-gfj-global-error a,
    #wpjb-gfj-global-success a {
        text-decoration: none;
        color: white;
    }
    
    #wpjb-gfj-global-success a:before {
        
    }
    
    #wpjb-gfj-preview-error {
        margin: 12px;
    }
    
    #wpjb-gfj-preview-error .wpjb-gfj-job-block-list li {
        display: inline-block;
        margin: 0 12px 1px 0;
    }
</style>





<?php wp_enqueue_script("wpjb-admin-google-for-jobs"); ?>
