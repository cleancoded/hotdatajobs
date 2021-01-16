<div class="wrap wpjb">
 
    <h1>
        <?php if($form->getId() > 0): ?>
            <?php printf( __("Edit Alert (ID: %s)", "wpjobboard"), $form->getObject()->id ) ?> 
        <?php else: ?>
            <?php printf( __("Add New Alert", "wpjobboard"), $form->getObject()->id ) ?> 
        <?php endif; ?>
        <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("alerts", "add")) ?>"><?php _e("Add New", "wpjobboard") ?></a> 
    </h1>
    
    <?php $this->_include("flash.php"); ?>
    
    <script type="text/javascript">
        Wpjb.Id = <?php echo $form->getObject()->getId() ?>;
    </script>

    <?php wp_enqueue_script("suggest"); ?>
    

    <form action="" method="post" enctype="multipart/form-data" class="wpjb-form">

        <div class="wpjb-sticky-anchor"></div>

        <div id="poststuff" >

            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">

                    <?php daq_form_layout( $form, array("exclude_groups" => "_internal" ) ) ?>
                    
                    <div class="postbox wpjb-form-postbox wpjb-namediv wpjb-form-group-alert wpjb-alert-params">
                        <h3><?php _e("Alert Params", "wpjobboard"); ?></h3>
                        <div class="inside">
                            <table class="form-table wpjb-form-table">
                                <tbody>
                                    <?php if(isset($params) && count($params) > 0): ?>
                                    <?php foreach($params as $label => $value): ?>
                                    <?php if(!isset($value) || empty($value)): continue; endif; ?>
                                    <?php if(!$sub_form->hasElement($label)): continue; endif; ?>
                                    <tr valign="top" class="wpjb-alert-param">
                                        <th class="wpjb-td-first" valign="top">
                                            <label for="<?php echo esc_html($label); ?>"><?php echo esc_html($sub_form->getElement($label)->getLabel()); ?></label>
                                        </th>
                                        <td>
                                            <?php $sub_form->getElement($label)->setValue($value); ?>
                                            <?php echo $sub_form->getElement($label)->render(); ?>
                                        </td>
                                        <td style="width: 5%;">
                                            <a href="" class="button wpjb-remove-alert-param wpjb-glyphs wpjb-icon-trash">
                                                
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr class="wpjb-alerts-empty">
                                        <td colspan="3">
                                        <?php _e("This alert have no additional params.", "wpjobboard"); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <tr class="wpjb-alerts-new-box">
                                        <th colspan="3" style="text-align: right;">
                                            <a href="" class="button wpjb-glyphs wpjb-icon-plus wpjb-alert-params-add" id="wpjb-add-alert-param">
                                                <?php _e("Add New Param", "wpjobboard"); ?>
                                            </a>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" value="<?php _e("Update Alert", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
                    </p>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div class="wpjb-sticky" id="side-info-column" style="">
                        <div class="meta-box-sortables ui-sortable" id="side-sortables">
                            <div class="postbox " id="submitdiv">
                                <div class="handlediv"><br></div>
                                <h3 class="hndle"><span><?php _e("Alert", "wpjobboard") ?></span></h3>
                                <div class="inside">
                                    <div id="submitpost" class="submitbox">
                                        <div id="minor-publishing">

                                            <?php 
                                                if(isset($user) && $user instanceof WP_User) {
                                                    $avatar_id_or_email = $form->getObject()->user_id;
                                                    $display_name = $user->display_name;
                                                    $user_id = $user->ID;
                                                } else {
                                                    $avatar_id_or_email = $form->getObject()->email;
                                                    $display_name = __("Anonymous", 'wpjobboard');
                                                    $user_id = null;
                                                }
                                            ?>
                                            <div class="misc-pub-section wpjb-mini-profile">
                                                <div class="wpjb-avatar">
                                                    <?php echo get_avatar($avatar_id_or_email, 48) ?>
                                                </div>

                                                <strong><?php esc_html_e($display_name) ?></strong><br/>

                                                <p>
                                                    <span class="wpjb-star-ratings" data-id="<?php echo esc_html($form->getObject()->id) ?>">
                                                        &nbsp;
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="misc-pub-section wpjb-inline-section curtime">
                                                <span id="timestamp"><?php _e("Alert Created", "wpjobboard") ?>: <b><?php esc_html_e( wpjb_date($form->getObject()->created_at) ) ?></b></span>
                                            </div>
                                            
                                            <div class="misc-pub-section wpjb-inline-section curtime">
                                                <?php if($form->getObject()->last_run && $form->getObject()->last_run != "0000-00-00 00:00:00"): ?>
                                                <span id="timestamp"><?php _e("Last Run", "wpjobboard") ?>: <b><?php esc_html_e( wpjb_date($form->getObject()->last_run) ) ?></b></span>
                                                <?php else: ?>
                                                <span id="timestamp"><?php _e("Last Run", "wpjobboard") ?>: <b><?php esc_html_e( "Never", "wpjobboard" ) ?></b></span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="misc-pub-section wpjb-inline-section wpjb-inline-suggest misc-pub-section-last">
                                                <span><span class="wpjb-sidebar-dashicon dashicons dashicons-id-alt"></span> <?php _e("User", "wpjobboard") ?>: <b class="wpjb-inline-label">&nbsp;</b></span>
                                                <a class="wpjb-inline-edit hide-if-no-js" href="#"><?php _e("Edit") ?></a> 

                                                <?php if($user_id): ?>
                                                    | <a href="<?php esc_attr_e(admin_url("user-edit.php?user_id={$user_id}")) ?>" title="<?php _e("view linked user account", "wpjobboard") ?>"><?php _e("Account", "wpjobboard") ?></a>
                                                <?php endif; ?>

                                                <div class="wpjb-inline-field wpjb-inline-select hide-if-js">

                                                    <?php echo $form->getElement("user_id_text")->render(); ?>
                                                    <a href="#" class="wpjb-inline-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                                                    <small class="wpjb-autosuggest-help" ><?php _e("start typing user: name, login or email in the box above, some suggestions will appear.", "wpjobboard") ?></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="major-publishing-actions">  
                                            <?php if($form->getId() > 0): ?>
                                            <div id="delete-action">
                                                <a href="<?php esc_attr_e(wpjb_admin_url("alert", "delete", $form->getObject()->id, array("noheader"=>1))) ?>" class="submitdelete deletion wpjb-delete-item-confirm"><?php _e("Delete", "wpjobboard") ?></a>
                                            </div>
                                            <div id="publishing-action">
                                                <input type="submit" accesskey="p" tabindex="5" value="<?php _e("Update alert", "wpjobboard") ?>" class="button-primary" id="publish" name="publish">
                                            </div>
                                            <?php else: ?>
                                            <div id="publishing-action">
                                                <input type="submit" accesskey="p" tabindex="5" value="<?php _e("Add alert", "wpjobboard") ?>" class="button-primary" id="publish" name="publish">
                                            </div>
                                            <?php endif; ?>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="postbox" id="submitdiv">
                                <div class="handlediv"><br></div>
                                <h3 class="hndle"><span><?php _e("Alert Logs", "wpjobboard") ?></span></h3>
                                <div class="wpjb inside">
                                    <div id="submitpost" class="submitbox">
                                        <div id="minor-publishing">
                                            <div class="misc-pub-section wpjb-grid">
                                                
                                                <?php $logs = unserialize($form->getObject()->logs); ?>
                                                <?php if(isset($logs) && !empty($logs) && is_array($logs) && count($logs) > 0): ?>
                                                <?php foreach($logs as $log): ?>
                                                <?php //var_dump($log); ?> 
                                                <div class="wpjb-grid-row wpjb-manage-item">
 
                                                    <table class="wpjb-small-table">
                                                        <tr>
                                                            <td style="width: 30%; padding:0px;">
                                                                <?php echo esc_html($log['status']); ?>
                                                            </td>
                                                            <td style="width: 70%; padding:0px; text-align: right;">
                                                                <span class="wpjb-glyphs wpjb-icon-clock" title="Creation Date"></span>
                                                                <abbr title="Creation Date"><?php echo esc_html($log['date']); ?></abbr>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr>
                                                            <td style="width: 30%; padding:0px;">
                                                                <a href="#" class="wpjb-alert-show-log" data-id="<?php echo esc_html( $form->getObject()->id); ?>">
                                                                    <span class="wpjb-glyphs wpjb-icon-down-open"></span>
                                                                    <?php _e("Jobs List", "wpjobboard"); ?>               
                                                                </a> 
                                                            </td>
                                                            <td style="width: 70%; padding:0px; text-align: right;">
                                                                <span class="wpjb-glyphs wpjb-icon-briefcase" title="Jobs Count"></span>
                                                                <?php echo esc_html($log['jobs_count']); ?>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr id="wpjb-alert-log-<?php echo esc_html( $form->getObject()->id); ?>" class="wpjb-grid wpjb-manage-action wpjb-col-100" style="width: 100%; background-color: whitesmoke; display: none;"> 
                                                            <td colspan="2">
                                                                <?php if(count($log['jobs_list']) > 0): ?>
                                                                <?php foreach($log['jobs_list'] as $job_id): ?>
                                                                <?php $job = new Wpjb_Model_Job($job_id); ?>
                                                                <div class="wpjb-grid-row">
                                                                    <div class="wpjb-grid-col wpjb-col-100">
                                                                        <a href="<?php echo wpjb_link_to( 'job', $job ); ?>"><?php echo esc_html( $job->job_title ); ?></a>
                                                                    </div>
                                                                </div>
                                                                <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    
                                                </div>
                                                <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?php _e("No Logs Found For This Alert", "wpjobboard"); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div> <!-- end #postbox-container-1 -->
            </div> <!-- #post-body -->
        </div> <!-- end #poststuff -->
    </form>
</div>

<script type="text/html" id="tmpl-wpjb-single-alert-param">
    <tr valign="top" class="wpjb-alert-param">
        <th class="wpjb-td-first" valign="top">
            <select class="wpjb-new-alert-param-type">
                <option><?php _e("Select Param ...", "wpjobboard"); ?></option>
                <optgroup label="<?php _e("Default Fields", "wpjobboard"); ?>">
                <?php foreach($fields['default_fields'] as $name => $field): ?>
                    <option value="<?php echo esc_html($field['value']); ?>"><?php echo esc_html($field['label']); ?></option>
                <?php endforeach; ?>
                </optgroup>
                <optgroup label="<?php _e("Custom Fields", "wpjobboard"); ?>">
                <?php foreach($fields['custom_fields'] as $name => $field): ?>
                    <option value="<?php echo esc_html($field['value']); ?>"><?php echo esc_html($field['label']); ?></option>
                <?php endforeach; ?>
                </optgroup>
            </select>
        </th>
        <td class="wpjb-single-alert-param-input">
            <?php //echo $field->render(); ?>
        </td>
        <td style="width: 5%;">
            <a href="" class="button wpjb-remove-alert-param wpjb-glyphs wpjb-icon-trash">

            </a>
        </td>
    </tr>
</script>

<script type="text/html" id="tmpl-wpjb-single-alert-param-form">
    <# if ( data.input_type == "select" ) { #>
    <select name="{{ data.input_name }}" id="{{ data.input_name }}">
        <# if ( data.input_name != "job_country" ) { #>
            <# for ( var i in data.options) { #>
            <option value="{{ data.options[i].value }}">{{ data.options[i].desc }}</option>
            <# } #>
        <# } else { #>
            <# var def = "<?php echo wpjb_locale(); ?>"; #>
            <# for ( var i in data.options) { #>
                <# if( data.options[i].value == def) { #>
                    <option value="{{ data.options[i].value }}" selected="selected">{{ data.options[i].desc }}</option>    
                <# } else { #>
                    <option value="{{ data.options[i].value }}">{{ data.options[i].desc }}</option>    
                <# } #>
            <# } #>
        <# } #>
    </select>
    <# } else if ( data.input_type == "radio" || data.input_type == "checkbox" ) { #>
    <ul class="wpjb-options-list">
        <# for ( var i in data.options) { #>
        <li class='wpjb-input-cols wpjb-input-cols-1'><input type="{{ data.input_type }}" name="{{ data.input_name }}[]" id="{{ data.input_name }}" value="{{ data.options[i].value }}" /> {{ data.options[i].desc }} </li>
        <# } #>
    </ul>
    <# } else { #>
        <input type="{{ data.input_type }}" name="{{ data.input_name }}" id="{{ data.input_name }}" />
    <# } #>
</script>