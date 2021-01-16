<div class="wrap wpjb">
    
<h1><?php _e("Add New Job", "wpjobboard"); ?>  <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("job", "add")) ?>"><?php _e("Add New", "wpjobboard") ?></a> </h1>

<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    <?php $value = $form->getValues(); ?>
    Wpjb.JobState = "<?php echo($value['job_country'] == 840 ? '' : $form->getObject()->job_state); ?>";
    Wpjb.Id = <?php echo $form->getObject()->getId() ?>;
</script>

<form action="" method="post" enctype="multipart/form-data" class="wpjb-form">
    
<div id="edit-slug-box" class="hide-if-no-js" style="padding:0 0 1.5em 0; font-size:1.1em; visibility:hidden">
    
    <strong>Permalink:</strong>
    <span id="sample-permalink" tabindex="-1"><?php echo $url ?></span>

    &lrm;
    
    <span id="edit-slug-buttons" class="wpjb-slug-buttons">
        <a class="save button button-small" href="#"><?php _e("OK") ?></a> 
        <a href="#" class="cancel button-small"><?php _e("Cancel") ?></a>

        <span id="edit-slug-btn"><a href="#post_name" class="edit-slug button button-small hide-if-no-js" title="<?php _e("Edit Permalink", "wpjobboard") ?>"><?php _e("Edit", "wpjobboard") ?></a></span>
    
    </span>
</div>  
    
<div class="wpjb-sticky-anchor"></div>
<div id="poststuff" >

    <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">

            <?php daq_form_layout($form, array("exclude_fields"=>"payment_method", "exclude_groups"=>"_internal")) ?>
            
            <p class="submit">
                <input type="submit" value="<?php _e("Publish Job", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
            </p>
        </div>

        <div id="postbox-container-1" class="postbox-container">

            <div class="meta-box-sortables ui-sortable" id="side-sortables">
                <div class="postbox " id="submitdiv">
                    <div class="handlediv"><br /></div>
                    <h3 class="hndle"><span><?php _e("Listing", "wpjobboard") ?></span></h3>
                    
                    <div class="inside">
                        <div id="submitpost" class="submitbox">

                        <div id="minor-publishing">

                            <div id="misc-publishing-actions">
                                <div class="misc-pub-section">
                                    <span id="timestamp"><?php _e("Post as", "wpjobboard") ?> <b class="company-edit-label">&nbsp;</b></span>
                                    <a class="employer-edit hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                                    <div class="employer hide-if-js">
                                        <?php echo $form->getElement("employer_id_text")->render(); ?>
                                        <a href="#" id="employer-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                                        <small class="wpjb-autosuggest-help" ><?php _e("start typing company name in the box above, some suggestions will appear.", "wpjobboard") ?></small>
                                    </div>
                                </div>

                            <div class="misc-pub-section">
                                <span><?php _e("Listing", "wpjobboard") ?> <b class="listing-type"><?php _e("Custom", "wpjobboard") ?></b></span>
                                <a id="listing-type-link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                                <div class="listing-type-change">
                                    <?php echo $form->getElement("listing_type")->render() ?>
                                    <a href="#" id="listing-type-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                                </div>

                            </div>
                            <div class="misc-pub-section curtime ">
                                <span id="created_at"><?php _e("Publish") ?> <b class="job_created_at">&nbsp;</b></span>
                                <a id="job_created_at_link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                                <input type="text" id="job_created_at" value="<?php esc_attr_e(wpjb_date($form->value("job_created_at"))) ?>" name="job_created_at" style="visibility:hidden;padding:0;width:1px" size="1" />
                            </div>
                            <div class="misc-pub-section curtime ">
                                <span id="expires_at"><?php _e("Expires", "wpjobboard") ?> <b class="job_expires_date">&nbsp;</b></span>
                                <a id="job_expires_at_link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                                <a id="job_expires_never" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Never Expires", "wpjobboard") ?></a>
                                <input type="text" id="job_expires_at" value="<?php esc_attr_e(wpjb_date($form->value("job_expires_at"))) ?>" name="job_expires_at" style="visibility:hidden;padding:0;width:1px" size="1" />
                            </div>


                            <div class="misc-pub-section misc-pub-section-last ">
                                <?php echo $form->getElement("is_active")->render(); ?><br/>
                                <?php echo $form->getElement("is_featured")->render(); ?><br/>
                                <?php echo $form->getElement("is_filled")->render(); ?><br/>
                            </div>

                            </div>
                        </div>

                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <img alt="" id="ajax-loading" class="ajax-loading" src="<?php esc_attr_e(admin_url("/images/wpspin_light.gif")) ?>" style="display:none">
                                <input type="submit" accesskey="p" tabindex="5" value="<?php _e("Publish") ?>" class="button-primary" id="publish" name="publish"></div>
                                <div class="clear"></div>
                            </div>
                        </div>

                    </div>
                </div>

                </div>

                <?php do_action("wpjb_page_sidebar", $form) ?>    

        </div> <!-- end #postbox-container-1 -->

    </div> <!-- end #post-body -->
    
</div> <!-- end #poststuff -->

</form>

</div>

<script type="text/javascript">
var Today = '<?php echo wpjb_date(date("Y-m-d")) ?>';
var Pricing = <?php echo json_encode($pricing) ?>;
</script>

