<div class="wrap wpjb">
 
<h1>
    <?php printf(__("Edit Job '<strong>%s</strong>'", "wpjobboard"), $form->getObject()->job_title) ?> 
    <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("job", "add")) ?>"><?php _e("Add New", "wpjobboard") ?></a> 
    <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("job", "add")) ?>&clone_id=<?php echo esc_html($form->getObject()->id) ?>"><?php _e("Clone", "wpjobboard") ?></a> 
</h1>
<?php

    
?>
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    <?php $value = $form->getValues(); ?>
    Wpjb.JobState = "<?php echo($value['job_country'] == 840 ? '' : $form->getObject()->job_state); ?>";
    Wpjb.Id = <?php echo $form->getObject()->getId() ?>;
</script>

<form action="" method="post" enctype="multipart/form-data" class="wpjb-form">

<div id="edit-slug-box" class="hide-if-no-js" style="padding:0 0 1.5em 0; font-size:1.1em">
    
    <strong><?php _e("Permalink") ?>:</strong>
    <span id="sample-permalink" tabindex="-1"><?php echo $url ?></span>

    &lrm;
    
    <span id="edit-slug-buttons" class="wpjb-slug-buttons">
        <a class="save button button-small" href="#"><?php _e("OK") ?></a> 
        <a href="#" class="cancel button-small"><?php _e("Cancel") ?></a>

        <?php if(get_option('permalink_structure')): ?>
        <span id="edit-slug-btn"><a href="#post_name" class="edit-slug button button-small hide-if-no-js" title="<?php _e("Edit Permalink", "wpjobboard") ?>"><?php _e("Edit", "wpjobboard") ?></a></span>
        <?php endif; ?>
        <span id="view-slug-btn"><a href="<?php esc_attr_e($permalink) ?>" class="button button-small" title="<?php _e("View Job", "wpjobboard") ?>"><?php _e("View Job", "wpjobboard") ?></a></span>
        
        <?php if($form->getId()>0): ?>
        <input type="hidden" id="wpjb-current-object-id" value="<?php echo $form->getId() ?>" />
        <?php endif; ?>
        
        <?php if(Wpjb_Project::getInstance()->env("uses_cpt") && $shortlink): ?>
        <span id="gets-slug-btn"><a href="#" class="button button-small" onclick="prompt('URL:', jQuery('#shortlink').val()); return false;"><?php _e("Get Shortlink", "wpjobboard") ?></a></span>
        <input id="shortlink" type="hidden" value="<?php esc_attr_e($shortlink)  ?>" />
        <?php endif; ?>
    
    </span>
</div>   
    
<div class="wpjb-sticky-anchor"></div>
<div id="poststuff" >

    <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">

            <?php daq_form_layout($form, array("exclude_fields"=>"payment_method", "exclude_groups"=>"_internal")) ?>

            <p class="submit">
                <input type="submit" value="<?php _e("Update Job", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
            </p>

        </div>

        <div id="postbox-container-1" class="postbox-container">


            <div class="wpjb-sticky" id="side-info-column">
                <div class="meta-box-sortables ui-sortable">
                <div class="postbox " id="submitdiv">
                <div class="handlediv"><br /></div><h3 class="hndle"><span><?php _e("Listing", "wpjobboard") ?></span></h3>
                <div class="inside">
                <div id="submitpost" class="submitbox">

                <div id="minor-publishing">

                <div id="misc-publishing-actions">
                <div class="misc-pub-section">
                    <span id="timestamp"><?php _e("Posted by", "wpjobboard") ?> <b class="company-edit-label">&nbsp;</b></span>
                    <a class="employer-edit hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                    <div class="employer hide-if-js">

                        <?php echo $form->getElement("employer_id_text")->render(); ?>
                        <a href="#" id="employer-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                        <small class="wpjb-autosuggest-help" ><?php _e("start typing company name in the box above, some suggestions will appear.", "wpjobboard") ?></small>
                    </div>
                </div>

                <div class="misc-pub-section curtime ">
                    <span id="created_at"><?php _e("Published") ?> <b class="job_created_date">&nbsp;</b></span>
                    <a id="job_created_at_link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                    <input type="text" id="job_created_at" value="<?php echo wpjb_date($form->getElement("job_created_at")->getValue()) ?>" name="job_created_at" style="visibility:hidden;padding:0;width:1px" size="1" />
                </div>
                <div class="misc-pub-section curtime ">
                    <span id="expires_at"><?php _e("Expires", "wpjobboard") ?> <b class="job_expires_date">&nbsp;</b></span>
                    <a id="job_expires_at_link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                    <a id="job_expires_never" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Never Expires", "wpjobboard") ?></a>
                    <input type="text" id="job_expires_at" value="<?php echo wpjb_date($form->getElement("job_expires_at")->getValue()) ?>" name="job_expires_at" style="visibility:hidden;padding:0;width:1px" size="1" />
                </div>


                <div class="misc-pub-section misc-pub-section-last ">
                    <?php echo $form->getElement("is_active")->render(); ?><br/>
                    <?php echo $form->getElement("is_featured")->render(); ?><br/>
                    <?php echo $form->getElement("is_filled")->render(); ?><br/>
                </div>

                </div>
                </div>

                <div id="major-publishing-actions">
                    <div id="delete-action">
                        <a href="<?php esc_attr_e(wpjb_admin_url("job", "delete", $form->getId(), array("noheader"=>1))) ?>" class="submitdelete deletion wpjb-delete-item-confirm"><?php _e("Delete") ?></a>
                    </div>
                    <div id="publishing-action">
                        <?php if(Wpjb_Project::getInstance()->env("uses_cpt") && $form->getObject()->post_id): ?>
                        <a href="<?php esc_attr_e(admin_url(sprintf('post.php?post=%d&action=edit', $form->getObject()->post_id))) ?>" class="button-secondary button"><?php _e("Advanced Options", "wpjobboard") ?></a>
                        <?php endif; ?>
                        <input type="submit" accesskey="p" tabindex="5" value="<?php _e("Update", "wpjobboard") ?>" class="button-primary" id="publish" name="publish"></div>
                        <div class="clear"></div>
                    </div>
                </div>

                </div>
                </div>



                </div>

                <?php if($payment && $payment->exists()): ?> 

                    <style type="text/css">
                        .wpjb-payment-details .wpjb-payment-amount {
                            display:inline-block; 
                            float:right; 
                            font-weight: bold
                        }
                        .wpjb-major-publishing-actions {
                            padding: 10px; 
                            clear: both; 
                            border-top: 1px solid #ddd; 
                            background: #f5f5f5;
                        }
                        .wpjb-publishing-action {
                            text-align: right;
                            float: right;
                            line-height: 23px;
                        }
                    </style>

                <div class="postbox " id="submitdiv">
                    <div class="handlediv"><br /></div>
                    <h3 class="hndle">
                        <span>
                            <span class="dashicons dashicons-cart" style="vertical-align: text-bottom;"></span> 
                            <?php _e("Payment", "wpjobboard") ?>
                        </span>
                    </h3>

                    <div class="inside">
                        <div class="submitbox">

                            <?php $color = ($payment->payment_paid >= $payment->payment_sum) ? "green" : "crimson" ?>

                            <div class="misc-pub-section wpjb-payment-details">
                                <span><?php _e("To Pay", "wpjobboard") ?></span>
                                <span class="wpjb-payment-amount"><?php echo wpjb_price($payment->payment_sum, $payment->payment_currency) ?></span>
                            </div>

                            <div class="misc-pub-section wpjb-payment-details">
                                <span><?php _e("Paid", "wpjobboard") ?></span>
                                <span class="wpjb-payment-amount" style="color:<?php echo $color ?>"><?php echo wpjb_price($payment->payment_paid, $payment->payment_currency) ?></span>
                            </div>

                            <div class="misc-pub-section wpjb-major-publishing-actions" style="">
                                <div class="wpjb-publishing-action">

                                    <?php if($payment->payment_sum > $payment->payment_paid): ?>
                                    <a href="<?php esc_attr_e(wpjb_admin_url("job", "markaspaid", $form->getId(), array("noheader"=>1))) ?>" class="button secondary"><?php _e("Mark as Paid", "wpjobboard") ?></a>
                                    <?php endif; ?>

                                    <a href="<?php esc_attr_e(wpjb_admin_url("payment", "edit", $payment->id)) ?>" class="button">
                                        <?php _e("View Order", "wpjobboard") ?>
                                    </a>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div> 
                    </div>
                </div>  
                <?php endif; ?>

                <?php if(false): ?> 
                <div class="postbox " id="submitdiv">
                    <div class="handlediv"><br /></div>
                    <h3 class="hndle"><span><?php _e("Payment", "wpjobboard") ?></span></h3>

                    <div class="inside">
                        <div id="submitpost" class="submitbox">

                        <!--div class="misc-pub-section wpjb-payment-method">
                            <?php _e("Payment method", "wpjobboard") ?> <b class="payment_method">&nbsp;</b>
                            <a id="payment_method_link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                            <div class="payment-method">
                                <?php echo $form->getElement("payment_method")->render(); ?>
                                <a href="#" id="payment-method-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                            </div>
                        </div-->

                        <?php $pay = $form->getPayment() ?>
                        <?php $pay = null; ?>

                        <div class="misc-pub-section">
                            <a href="<?php esc_attr_e(wpjb_admin_url("payment", "edit", $payment->id)) ?>" class="button-secondary">
                                <span class="dashicons dashicons-cart" style="vertical-align: text-bottom"></span>
                                <?php _e("View Order ...", "wpjobboard") ?>
                            </a>
                        </div>

                        <div class="misc-pub-section <?php if($pay->id<1): ?>misc-pub-section-last<?php endif;?> wpjb-payment-details">
                            <?php _e("To pay", "wpjobboard") ?> 
                            <?php echo $form->getElement("payment_sum")->render() ?>
                            <?php echo $form->getElement("payment_currency")->render() ?>


                        </div>

                        <?php if($pay->id>0): ?>
                        <div class="misc-pub-section <?php if(!$pay->message): ?>misc-pub-section-last<?php endif;?>" style="line-height:28px">
                            <?php _e("Total Paid", "wpjobboard") ?>
                            <span style="color:<?php if($pay->payment_sum>$pay->payment_paid): ?><?php else: ?>green<?php endif; ?>">
                                <strong><?php echo wpjb_price($pay->payment_paid, $pay->payment_currency) ?></strong>

                                <?php if($pay->payment_sum>$pay->payment_paid): ?>
                                <a href="<?php esc_attr_e(wpjb_admin_url("job", "markaspaid", $form->getId(), array("noheader"=>1))) ?>" class="button secondary"><?php _e("Mark as Paid", "wpjobboard") ?></a>
                                <?php endif; ?>
                            </span>

                        </div>

                        <?php if($pay->message): ?>
                        <div class="misc-pub-section misc-pub-section-last">
                            <div class="updated fade below-h2">
                                <small><strong><?php _e("Payment Error", "wpjobboard") ?>:</strong><?php esc_html_e($pay->message) ?></small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php endif; ?>

                        </div>

                    </div>
                </div>  
                <?php endif; ?>
                    
                <?php $gfjc = wpjb_conf("google_for_jobs") ?>
                <?php if(!isset($gfjc["is_disabled"]) || $gfjc["is_disabled"] != "1"): ?>
                <div class="postbox ">
                    <h3 class="hndle"><span><?php _e("Google Jobs", "wpjobboard") ?></span></h3>
                    <div class="inside">
                        <div class="submitbox">

        
                            <?php

                                $job = $form->getObject();
                                $gfj = new Wpjb_Service_GoogleForJobs();
                                add_filter("wpjb_google_for_jobs_jsonld", array($gfj, "mapFromConfig"), 10, 2);
                                $json = $gfj->getJson($job);
                                $v = $gfj->validateJson($json);
                                
                            ?>

                            <div class="wpjb-gfj-job">
                                <span class="wpjb-gfj-job-brief">
                                    <?php $err = count($v->missing->required); ?>
                                    <?php $warn = count($v->missing->recommended); ?>
                                    
                                    <?php if($err > 0): ?>
                                        <span class="wpjb-gfj-job-required">
                                        <?php echo sprintf(_n("%d Error", "%d Errors", $err, "wpjobboard"), $err) ?>   
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if($warn > 0): ?>
                                    <span class="wpjb-gfj-job-recommended">
                                        <?php echo sprintf(_n("%d Warning", "%d Warnings", $warn, "wpjobboard"), $warn) ?>  
                                    </span>
                                    <?php endif; ?>

                                    <?php if($warn + $err == 0): ?>
                                    <span class="wpjb-gfj-job-ok">
                                        <span class="dashicons dashicons-yes"></span> OK
                                    </span>
                                    <?php endif; ?>
                                    
                                </span>
                                
                                <span class="wpjb-gfj-job-more-show">
                                    <a href="#" class="button wpjb-gfj-job-more-button"><?php _e("More", "wpjobboard") ?> <span class="dashicons dashicons-arrow-down-alt2"></span></a>
                                </span>
                                
                                <div class="wpjb-gfj-job-more wpjb-none">
                                    
                                    <?php if($err > 0): ?>
                                    <div class="wpjb-gfj-job-required wpjb-gfj-job-block">
                                        <span class="wpjb-gfj-job-block-text">
                                        <?php _e("The below fields are <strong>required</strong>.", "wpjobboard") ?>
                                        </span>
                                        <ul class="wpjb-gfj-job-block-list">
                                            <?php foreach($v->missing->required as $r): ?>
                                            <li><?php echo esc_html($r) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if($warn > 0): ?>
                                    <div class="wpjb-gfj-job-recommended wpjb-gfj-job-block">
                                        <span class="wpjb-gfj-job-block-text">
                                        <?php _e("The below fields are <strong>recommended</strong>. Fill them if possible.", "wpjobboard") ?>
                                        </span>
                                        <ul class="wpjb-gfj-job-block-list">
                                            <?php foreach($v->missing->recommended as $r): ?>
                                            <li><?php echo esc_html($r) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="wpjb-gfj-job-validate">
                                        <a href="<?php echo admin_url("admin.php?page=wpjb-config&action=googleforjobs&form=google-for-jobs&forced-id=".$job->id) ?>" class="button"><?php _e("Configure", "wpjobboard") ?></a>
                                        <a href="#" class="wpjb-gfj-job-validate-button button"><?php _e( "Validate ...", "wpjobboard") ?></a>
                                    </div>

                                </div>

                            </div>
                            


                        </div>

                    </div>
                </div>
                <?php endif; ?>
                    
                <?php do_action("wpjb_page_sidebar", $form) ?>

            </div> <!-- end #site-info-column -->

        </div> <!-- end #postbox-container-1 -->
    </div> <!-- #post-body -->
</div> <!-- end #poststuff -->

</form>


<script type="text/javascript">
var Today = '<?php echo wpjb_date(date("Y-m-d")) ?>';
var Pricing = <?php echo json_encode($pricing) ?>;
</script>

</div>

<?php if(isset($gfj) && is_object($gfj)): ?>
<form action="https://search.google.com/structured-data/testing-tool" method="post" class="wpjb-gfj-job-submit wpjb-none" target="_blank">
    <input type="hidden" name="code" value="<?php echo esc_attr($gfj->getHtml($job)) ?>" />
    <input type="submit" value="" class="" />
</form>
<?php endif; ?>