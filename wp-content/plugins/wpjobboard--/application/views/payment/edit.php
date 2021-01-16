<?php $payment = $form->getObject() ?>
<div class="wrap wpjb">
    
<h1>
    <?php _e("Edit Payment", "wpjobboard"); ?> (ID: <?php echo $form->getId() ?>)
</h1>
    
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.Id = <?php echo $form->getObject()->getId() ?>;
</script>

<form action="" method="post" class="wpjb-form" enctype="multipart/form-data">
    <div class="" id="poststuff" >

        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                
                <div class="postbox wpjb-namediv" >
                    <h3><?php _e("Payment Information", "wpjobboard") ?></h3>
                    <div class="inside">
                        <div class="wpjb-column-third">
                            <strong><?php _e("Purchase Type", "wpjobboard") ?></strong>
                            <?php echo esc_html($listing["title"]) ?>
                        </div>
                        <div class="wpjb-column-third wpjb-inline-section">
                            
                            <strong>
                                <?php _e("Selected Pricing", "wpjobboard") ?>
                                <a class="wpjb-inline-edit hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                            </strong>
                            <div class="wpjb-inline-field wpjb-inline-select hide-if-js">
                                <?php echo $form->getElement("pricing_id")->render(); ?>
                                <a href="#" class="wpjb-inline-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                            </div>
                            <?php $pricing = new Wpjb_Model_Pricing($payment->pricing_id) ?>
                            <?php $listing = wpjb_get_pricing_listing( $pricing->price_for ); ?>
                            <span class="wpjb-inline-label">
                                <?php if($pricing->exists()): ?>
                                <a href="<?php echo wpjb_admin_url("pricing", "edit", $pricing->id, array("listing"=>$listing)) ?>">
                                    <?php esc_html_e($pricing->title) ?>
                                </a>
                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="wpjb-column-third">
                            <strong><?php _e("Purchased Item", "wpjobboard") ?></strong>
                            
                            <?php
                                switch($payment->object_type) {
                                    case Wpjb_Model_Payment::JOB: $object = new Wpjb_Model_Job($payment->object_id); break;
                                    case Wpjb_Model_Payment::RESUME: $object = new Wpjb_Model_Resume($payment->object_id); break;
                                    case Wpjb_Model_Payment::MEMBERSHIP: $object = new Wpjb_Model_Membership($payment->object_id); break;
                                    case Wpjb_Model_Payment::CAND_MEMBERSHIP: $object = new Wpjb_Model_Membership($payment->object_id); break;
                                    default: $object = null;
                                }
                            ?>
                            
                            <?php if(!$object || !$object->exists()): ?>
                            <?php _e("Deleted", "wpjobboard") ?>
                            <?php elseif($payment->object_type == Wpjb_Model_Payment::JOB): ?>
                            <a href="<?php esc_attr_e(wpjb_admin_url("job", "edit", $object->id)) ?>" title="<?php esc_attr_e($object->job_title . " (ID: ".$object->id.")") ?>"><?php esc_html_e($object->job_title) ?></a>
                            <?php elseif($payment->object_type == Wpjb_Model_Payment::RESUME): ?>
                            <a href="<?php echo wpjb_admin_url("resumes", "edit", $object->id) ?>" title="<?php esc_attr_e($object->getSearch(true)->fullname . " (ID: ".$object->id.")") ?>"><?php esc_html_e($object->getSearch(true)->fullname) ?></a>
                            <?php elseif($payment->object_type == Wpjb_Model_Payment::MEMBERSHIP): ?>
                            <a href="<?php echo wpjb_admin_url("memberships", "edit", $object->id) ?>" title="<?php esc_attr_e($object->getPricing(true)->title . " (ID: ".$object->id.")") ?>"><?php esc_html_e($object->getPricing(true)->title) ?></a>
                            <?php elseif($payment->object_type == Wpjb_Model_Payment::CAND_MEMBERSHIP): ?>
                            <a href="<?php echo wpjb_admin_url("memberships", "edit", $object->id) ?>" title="<?php esc_attr_e($object->getPricing(true)->title . " (ID: ".$object->id.")") ?>"><?php esc_html_e($object->getPricing(true)->title) ?></a>
                            <?php else: ?>
                            <?php do_action("wpjb_payment_for", $payment) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php echo daq_form_layout($form) ?>
                
                <div class="postbox wpjb-namediv" >
                    <h3><?php _e("Payment Log", "wpjobboard") ?></h3>
                    <div class="inside">
                    <?php foreach(explode("\r\n", $payment->message) as $log): ?>
                    <?php echo strip_tags($log, "<strong><em><i><b>") ?><br/>
                    <?php endforeach; ?>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" value="<?php _e("Save Payment", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
                </p>
            </div>
        
    
            <div id="postbox-container-1" class="postbox-container">
                <div class="wpjb-sticky" id="side-info-column" style="">
                <div class="meta-box-sortables ui-sortable" id="side-sortables">
                    <div class="postbox " id="submitdiv">
                        <div class="handlediv"><br></div>
                        <h3 class="hndle"><span><?php _e("Payment", "wpjobboard") ?></span></h3>

                        <div class="inside">
                            <div id="submitpost" class="submitbox wpjb-payment-sidebar">

                                <div id="minor-publishing">


                                    <div class="misc-pub-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("Created", "wpjobboard") ?>:</span>
                                            <b><?php echo wpjb_date($form->getObject()->created_at) ?></b>
                                        </span>
                                    </div>

                                    <div class="misc-pub-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("Paid", "wpjobboard") ?>:</span>
                                            <?php if($form->getObject()->paid_at == "0000-00-00 00:00:00"): ?>
                                            <b>—</b>
                                            <?php else: ?>
                                            <b><?php echo wpjb_date($form->getObject()->paid_at) ?></b>
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <div class="misc-pub-section wpjb-inline-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("Gateway", "wpjobboard") ?>:</span> 
                                            <b class="wpjb-inline-label">&nbsp;</b>
                                        </span>
                                        <a class="wpjb-inline-edit hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                                        <div class="wpjb-inline-field wpjb-inline-select hide-if-js">
                                            <?php echo $form->getElement("engine")->render(); ?>
                                            <a href="#" class="wpjb-inline-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                                        </div>
                                    </div>

                                    <div class="misc-pub-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("Status", "wpjobboard") ?>:</span> 
                                            <span class="wpjb-input-3-4">
                                                <?php echo $form->getElement("status")->render() ?>
                                            </span>

                                        </span>
                                    </div>

                                    <div class="misc-pub-section wpjb-inline-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("Currency", "wpjobboard") ?>:</span> 
                                            <b class="wpjb-inline-label">&nbsp;</b>
                                        </span>
                                        <a class="wpjb-inline-edit hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                                        <div class="wpjb-inline-field wpjb-inline-select hide-if-js">
                                            <?php echo $form->getElement("payment_currency")->render(); ?>
                                            <a href="#" class="wpjb-inline-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                                        </div>
                                    </div>

                                    <div class="misc-pub-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("To Pay", "wpjobboard") ?>:</span> 
                                            <span class="wpjb-input-3-4">
                                                <?php echo $form->getElement("payment_sum")->render() ?>
                                            </span>
                                        </span>
                                    </div>

                                    <div class="misc-pub-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("Discount", "wpjobboard") ?>:</span> 
                                            <span class="wpjb-input-3-4">
                                                <?php echo $form->getElement("payment_discount")->render() ?>
                                            </span>
                                        </span>
                                    </div>

                                    <div class="misc-pub-section">
                                        <span>
                                            <span class="wpjb-label-fourth"><?php _e("Paid", "wpjobboard") ?>:</span> 
                                            <span class="wpjb-input-3-4">
                                                <?php echo $form->getElement("payment_paid")->render() ?>
                                            </span>
                                        </span>
                                    </div>

                                </div>


                                <div id="major-publishing-actions">  
                                    <div id="delete-action">
                                        <a href="<?php esc_attr_e(wpjb_admin_url("payment", "delete", $form->getObject()->id, array("noheader"=>1))) ?>" class="submitdelete deletion wpjb-delete-item-confirm"><?php _e("Delete", "wpjobboard") ?></a>
                                    </div>
                                    <div id="publishing-action">
                                        <input type="submit" accesskey="p" tabindex="5" value="<?php _e("Update Payment", "wpjobboard") ?>" class="button-primary" id="publish" name="publish">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox" id="submitdiv">
                        <div class="handlediv"><br></div>
                        <h3 class="hndle"><span><?php _e("Payment URL", "wpjobboard") ?></span></h3>

                        <div class="inside">
                            <div class="submitbox wpjb-payment-sidebar">

                                <div>


                                    <div class="misc-pub-section">
                                        <span>
                                            <input type="text" value="<?php esc_attr_e($payment->url()) ?>" style="width:100%" />
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php do_action("wpja_minor_section_payment", $form) ?>

                </div> 

            </div> <!-- #postbox-container-1 -->
        </div> <!-- #postbody -->
    </div>
    
</form>


</div>