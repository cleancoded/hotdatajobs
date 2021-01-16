<div class="wrap wpjb">
    
    <h1>
        <?php _e("Custom Fields Editor", "wpjobboard"); ?> 
        &raquo;
        <?php esc_html_e($formTitle) ?>
    </h1>
<?php $this->_include("flash.php"); ?>
    
<?php $imgDir = esc_html(plugin_dir_url(dirname(dirname(__FILE__))))."vendor/visual-editor/images" ?>

<form id="post" method="post" action="post.php" name="post">


<div class="metabox-holder has-right-sidebar" id="poststuff">
<div class="inner-sidebar" id="side-info-column">
<div class="postbox" id="">
<div class="wpjb-toolbox wpjb-toolbox-default" id="">
    <div class="handlediv" title="<?php _e("Click to toggle", "wpjobboard") ?>"><br /></div>
    <h3 class="hndle"><span><?php _e("Basic Fields", "wpjobboard") ?></span></h3>
    <div class="inside">
        <ul class="ve-field-types">
            <li class="wpjb-toolbox-item ui-input-group">
                <img src="<?php echo $imgDir ?>/ui-group-box.png" /> 
                <span><?php _e("Group Box", "wpjobboard") ?></span>
            </li>
            <li class="wpjb-toolbox-item wpjb-ui-element ui-input-label">
                <img src="<?php echo $imgDir ?>/ui-label.png" /> 
                <span><?php _e("Label", "wpjobboard") ?></span>
            </li>
            <li class="wpjb-toolbox-item wpjb-ui-element ui-input-text">
                <img src="<?php echo $imgDir ?>/ui-text-field.png" /> 
                <span><?php _e("Text Box", "wpjobboard") ?></span>
            </li>
            <li class="wpjb-toolbox-item wpjb-ui-element ui-input-checkbox">
                <img src="<?php echo $imgDir ?>/ui-check-box.png" /> 
                <span><?php _e("Check Box", "wpjobboard") ?></span>
            </li>
            <li class="wpjb-toolbox-item wpjb-ui-element ui-input-radio">
                <img src="<?php echo $imgDir ?>/ui-radio-button.png" /> 
                <span><?php _e("Radio Button", "wpjobboard") ?></span>
            </li>
            <li class="wpjb-toolbox-item wpjb-ui-element ui-input-select">
                <img src="<?php echo $imgDir ?>/ui-combo-box-blue.png" /> 
                <span><?php _e("Drop Down", "wpjobboard") ?></span>
            </li>
            <li class="wpjb-toolbox-item wpjb-ui-element ui-input-textarea">
                <img src="<?php echo $imgDir ?>/ui-text-area.png" /> 
                <span><?php _e("Text Area", "wpjobboard") ?></span>
            </li>
            <li class="wpjb-toolbox-item wpjb-ui-element ui-input-file">
                <img src="<?php echo $imgDir ?>/drive-upload.png" /> 
                <span><?php _e("File Upload", "wpjobboard") ?></span>
            </li>
        </ul>
    </div>
</div>
    
<?php if($toolbox == "default"): ?>
<div class="wpjb-toolbox wpjb-toolbox-trash">
    <div class="handlediv" title="<?php _e("Click to toggle", "wpjobboard") ?>"><br /></div>
    <h3 class="hndle hndlei"><span><?php _e("Trash", "wpjobboard") ?></span></h3>
    <div class="inside">
        <ul class="ve-trash">
        </ul>
    </div>
</div>
<?php elseif($toolbox == "search"): ?>

<style type="text/css">
    img.ve-event-delete { display: none}
    .wpjb-toolbox-default { display: none}
</style>
    
<div class="wpjb-toolbox wpjb-toolbox-trash">
    <div class="handlediv" title="<?php _e("Click to toggle", "wpjobboard") ?>"><br /></div>
    <h3 class="hndle hndlei"><span><?php _e("Unused", "wpjobboard") ?></span></h3>
    <div class="inside">
        <ul class="ve-trash">
        </ul>
    </div>
</div>
    
<?php endif; ?>
   </div> 
    
    <div>
        <a class="button" id="ve-save-form"><?php _e("Save form", "wpjobboard") ?></a>
        <img alt="" class="ve-loader" src="<?php echo get_admin_url() ?>/images/wpspin_light.gif" />
        
        <div id="ve-save-success" class="success" title="<?php _e("Click to dispose", "wpjobboard") ?>"><?php _e("The form was saved successfully.", "wpjobboard") ?></div>
        <div id="ve-save-failed" class="wpjb-error" title="<?php _e("Click to dispose", "wpjobboard") ?>"><?php _e("Unexpected error, the form could not be saved.", "wpjobboard") ?></div>
    </div>
</div>



<div id="post-body">
    <ul class="wpjb-ve-area">

    </ul>
</div>
    
<br class="clear">
</div><!-- /poststuff -->
</form>


<script type="text/javascript">


var form = <?php echo json_encode($form) ?>;
VisualEditor.ImgDir = "<?php echo $imgDir ?>";
var VE_FORM = "<?php echo $formName ?>";


</script>
<link media="all" type="text/css" href="<?php echo site_url() ?>/wp-includes/js/thickbox/thickbox.css?ver=3.4-alpha-19620" id="thickbox-css" rel="stylesheet" />

<div style="display:none" id="ve-property-editor">
    <div class="wpjb-tabs-div">
        <ul class="wpjb-tabs" id="wpjb-tabs">
            <li class="active general"><a class="active" href="javascript:ve_switch('general');"><?php _e("General", "wpjobboard") ?></a></li>
            <li class="specific options"><a href="javascript:ve_switch('options');"><?php _e("Options", "wpjobboard") ?></a></li>
            <li class="specific ui-input-text"><a href="javascript:ve_switch('ui-input-text');"><?php _e("Text Field", "wpjobboard") ?></a></li>
            <li class="specific ui-input-select"><a href="javascript:ve_switch('ui-input-select');"><?php _e("Dropdown", "wpjobboard") ?></a></li>
            <li class="specific ui-input-checkbox"><a href="javascript:ve_switch('ui-input-checkbox');"><?php _e("Checkbox", "wpjobboard") ?></a></li>
            <li class="specific ui-input-radio"><a href="javascript:ve_switch('ui-input-radio');"><?php _e("Radio", "wpjobboard") ?></a></li>
            <li class="specific ui-input-textarea"><a href="javascript:ve_switch('ui-input-textarea');"><?php _e("Text Area", "wpjobboard") ?></a></li>
            <li class="specific ui-input-file"><a href="javascript:ve_switch('ui-input-file');"><?php _e("File", "wpjobboard") ?></a></li>
            <li class="specific ui-input-label"><a href="javascript:ve_switch('ui-input-label');"><?php _e("Label", "wpjobboard") ?></a></li>
        </ul>
        
        <div class="general wpjb-tab">
            <h3><?php _e("General Options", "wpjobboard") ?></h3>
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr class="field-name">
                            <td><?php _e("Name<small>Field name</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="name" id="ve-field-name" /></td>
                        </tr>
                        <tr class="field-title">
                            <td><?php _e("Label<small>Field question or title</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="title" /></td>
                        </tr>
                        <tr class="field-hint">
                            <td><?php _e("Sub Label<small>Small description below the input field</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="hint" /></td>
                        </tr>
                        <tr class="field-required">
                            <td><?php _e("Required<small>Require filling the field.</small>", "wpjobboard") ?></td>
                            <td><input type="checkbox" name="is_required" /></td>
                        </tr>
                        <tr class="field-visibility">
                            <td><?php _e("Visibility<small>Who can see these field.</small>", "wpjobboard") ?></td>
                            <td>
                                <select name="visibility">
                                    <option value="0"><?php _e("Anyone", "wpjobboard") ?></option>
                                    <option value="1"><?php _e("Forms Only", "wpjobboard") ?></option>
                                    <option value="2"><?php _e("Admin Only", "wpjobboard") ?></option>
                                </select>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="options wpjb-tab ve-none">
            <h3><?php _e("Options", "wpjobboard") ?></h3>
  
            <table>
                <tbody>
                    <tr class="">
                        <td><?php _e("Fill method<small>Validation rules</small>", "wpjobboard") ?></td>
                        <td>
                            <select name="fill_method" class="ve-fill-method">
                                <option value="default"><?php _e("Default values", "wpjobboard") ?></option>
                                <option value="callback"><?php _e("Callback function", "wpjobboard") ?></option>
                                <option value="choices"><?php _e("Predefined choices", "wpjobboard") ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="ve-fill-callback">
                        <td><?php _e("Function Name<small>Existing function name.</small>", "wpjobboard") ?></td>
                        <td><input type="text" name="fill_callback" /></td>
                    </tr>
                    <tr class="ve-fill-options">
                        <td><?php _e("Options<small>Options.</small>", "wpjobboard") ?></td>
                        <td>
                            <textarea rows="10" cols="30" name="fill_choices">

                            </textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        
        </div>

        <div class="ui-input-text wpjb-tab ve-none">
            <h3><?php _e("Text Field Options", "wpjobboard") ?></h3>
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr class="field-validation">
                            <td><?php _e("Validation<small>Validation rules</small>", "wpjobboard") ?></td>
                            <td>
                                <select name="validation_rules">
                                    <option value=""></option>
                                    <option value="Daq_Validate_Email"><?php _e("E-mail Address", "wpjobboard") ?></option>
                                    <option value="Daq_Validate_Url"><?php _e("URL", "wpjobboard") ?></option>
                                    <option value="Daq_Validate_Float"><?php _e("Number", "wpjobboard") ?></option>
                                    <option value="Daq_Validate_Int"><?php _e("Integer", "wpjobboard") ?></option>
                                    <option value="Daq_Validate_Date"><?php _e("Date", "wpjobboard") ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class="field-validation">
                            <td><?php _e("Input Length<small>String minimum and maximum length</small>", "wpjobboard") ?></td>
                            <td>
                                <input style="width: 48%;" type="text" name="validation_min_size" id="validation_min_size" placeholder="<?php _e("Min", "wpjobboard") ?>" />
                                <input style="width: 48%;" type="text" name="validation_max_size" id="validation_max_size" placeholder="<?php _e("Max", "wpjobboard") ?>" />
                            </td>
                        </tr>
                        <tr class="field-placeholder">
                            <td><?php _e("Hint Example<small>Show example in gray</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="placeholder" /></td>
                        </tr>
                        
                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="ui-input-file wpjb-tab ve-none">
            <h3><?php _e("File Upload Options", "wpjobboard") ?></h3>
            <!-- Future use: display: [file list, gallery] -->
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr class="field-file-size">
                            <td><?php _e("Max. file size<small>Maximum upload file size (in bytes)</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="file_size" /></td>
                        </tr>
                        <tr class="field-file-ext">
                            <td><?php _e("Allowed formats<small>Comma separated allowed file extensions</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="file_ext" /></td>
                        </tr>
                        <tr class="field-file-limit">
                            <td><?php _e("Files number<small>Maximum number of files</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="file_num" /></td>
                        </tr>

                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="ui-input-select wpjb-tab ve-none">
            <h3><?php _e("Drop Down Options", "wpjobboard") ?></h3>
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr class="field-file-size">
                            <td><?php _e("Multiselect<small>How many choices user can make</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="select_choices" /></td>
                        </tr>
                        <tr class="field-required">
                            <td><?php _e("Empty Option<small>Allow to select empty option.</small>", "wpjobboard") ?></td>
                            <td><input type="checkbox" name="empty_option" /></td>
                        </tr>
                        <tr class="field-file-size">
                            <td><?php _e("Empty Option Text<small>Text visible in empty option e.g.: -- choose option --</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="empty_option_text" /></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="ui-input-textarea wpjb-tab ve-none">
            <h3><?php _e("Text Area Options", "wpjobboard") ?></h3>
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr class="field-validation">
                            <td><?php _e("Input Length<small>String minimum and maximum length</small>", "wpjobboard") ?></td>
                            <td>
                                <input style="width: 48%;" type="text" name="validation_min_size" id="validation_min_size" placeholder="<?php _e("Min", "wpjobboard") ?>" />
                                <input style="width: 48%;" type="text" name="validation_max_size" id="validation_max_size" placeholder="<?php _e("Max", "wpjobboard") ?>" />
                            </td>
                        </tr>
                        <tr class="field-textarea-wysiwyg">
                            <td><?php _e("WYSIWYG<small>Use WYSIWYG editor</small>", "wpjobboard") ?></td>
                            <td>
                                <select name="textarea_wysiwyg">
                                    <option value="0"><?php _e("No", "wpjobboard") ?></option>
                                    <option value="1"><?php _e("Basic TinyMCE", "wpjobboard") ?></option>
                                    <option value="2"><?php _e("Full TinyMCE", "wpjobboard") ?></option>
                                </select>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="ui-input-checkbox wpjb-tab ve-none">
            <h3><?php _e("Checkbox Options", "wpjobboard") ?></h3>
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr class="field-file-size">
                            <td><?php _e("Multiselect<small>How many choices user can make</small>", "wpjobboard") ?></td>
                            <td><input type="text" name="select_choices" /></td>
                        </tr>

                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="ui-input-label wpjb-tab ve-none">
            <h3><?php _e("Label Options", "wpjobboard") ?></h3>
            <form action="" method="post">
                <table>
                    <tbody>
                        <tr class="field-file-size">
                            <td><?php _e("Text<small>You can use HTML inside the label</small>", "wpjobboard") ?></td>
                            <td><textarea name="description" style="width:100%"></textarea></td>
                        </tr>

                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="ui-input-radio wpjb-tab ve-none">
            <h3><?php _e("Radio Options", "wpjobboard") ?></h3>
            <h4><?php _e("No options for radio field.", "wpjobboard") ?></h4>
        </div>
        
        <div style="margin-top:10px; text-align:center">
            <span><a href="#" class="ve-save button"><?php _e("Close Settings", "wpjobboard") ?></a></span>
        </div>
    </div>
    
    
    
    
</div>

</div>
