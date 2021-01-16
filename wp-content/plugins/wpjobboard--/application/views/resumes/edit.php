<div class="wrap wpjb">

<h1>
    <?php if(empty($part)): ?>
    <?php printf(__("Edit Candidate '<strong>%s</strong>'", "wpjobboard"), ($user->first_name || $user->last_name) ? esc_html(trim($user->first_name." ".$user->last_name)) : esc_html($resume->id)); ?>

    <?php else: ?>
    <?php _e("Edit Candidate", "wpjobboard"); ?>
    &raquo; <a href="<?php esc_attr_e(wpjb_admin_url("resumes", "edit", $resume->id)) ?>"><?php echo ($user->first_name || $user->last_name) ? esc_html(trim($user->first_name." ".$user->last_name)) : esc_html("ID: ".$resume->id) ?></a>
    <?php endif; ?>
</h1>

<?php $this->_include("flash.php"); ?>

<style type="text/css">
    @media print {
        #adminmenuback,
        #adminmenumain,
        #wpfooter {
            display: none;
        }
        #wpcontent {
            margin-left: 0px;
        }
    }
</style>
    
<form action="" method="post" enctype="multipart/form-data" class="wpjb-form">

    <script type="text/javascript">wpjb_slug_ui();</script>
    
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
            <span id="view-slug-btn"><a href="<?php esc_attr_e($permalink) ?>" class="button button-small" title="<?php _e("View Resume", "wpjobboard") ?>"><?php _e("View Resume", "wpjobboard") ?></a></span>

            <?php if($form->getId()>0): ?>
            <input type="hidden" id="wpjb-current-object-id" value="<?php echo $form->getId() ?>" />
            <?php endif; ?>

            <?php if(Wpjb_Project::getInstance()->env("uses_cpt") && $shortlink): ?>
            <span id="gets-slug-btn"><a href="#" class="button button-small" onclick="prompt('URL:', jQuery('#shortlink').val()); return false;"><?php _e("Get Shortlink", "wpjobboard") ?></a></span>
            <input id="shortlink" type="hidden" value="<?php esc_attr_e($shortlink)  ?>" />
            <?php endif; ?>

        </span>
    </div>  
     
    <div id="poststuff" >
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">

                <?php if(!empty($part)): ?>
                <?php daq_form_layout($form) ?>
                <?php else: ?>
                <?php wpjb_form_resume_preview($form) ?>
                <?php endif; ?>
                
                <br class="clear" />

                <?php if(!empty($part)): ?>
                <p class="submit">
                    <input type="submit" value="<?php _e("Update", "wpjobboard") ?>" class="button-primary button" name="Save"/>
                    <input type="submit" value="<?php _e("Update and Go back", "wpjobboard") ?>" class="button" name="SaveClose"/>
                </p>
                <?php endif; ?>
            </div>
            <?php if(empty($part)): ?>
            <div id="postbox-container-1" class="postbox-container">
                <div class="wpjb-sticky" id="side-info-column" style="padding-top:10px">
                <div class="meta-box-sortables ui-sortable" id="side-sortables"><div class="postbox " id="submitdiv">
                <div class="handlediv"><br></div><h3 class="hndle"><span><?php _e("Candidate", "wpjobboard") ?></span></h3>
                <div class="inside">
                <div id="submitpost" class="submitbox">

                <div id="minor-publishing">

                <div class="misc-pub-section wpjb-mini-profile">
                    <div class="wpjb-avatar">
                    <?php echo get_avatar($resume->user_id) ?>
                    </div>
                    <strong><?php esc_html_e($user->display_name) ?></strong><br/>
                    <p><?php _e("Login", "wpjobboard") ?>: <b><?php esc_html_e($user->user_login) ?></b></p>
                    <p><?php _e("ID", "wpjobboard") ?>: <b><?php echo $user->ID ?></b></p>


                    <br class="clear" />

                    <p><a href="<?php esc_attr_e(admin_url("user-edit.php?user_id={$user->ID}")) ?>" class="button"><?php _e("view linked user account", "wpjobboard") ?></a></p>
                    <p><a href="<?php esc_attr_e(wpjr_link_to("resume", $resume)) ?>" class="button"><?php _e("view resume") ?></a></p>
                </div>

                <div class="misc-pub-section curtime">
                    <span><?php _e("Created", "wpjobboard") ?> <b class="resume_created_date"><?php echo wpjb_date($form->getElement("created_at")->getValue()) ?></b></span>
                    <a id="resume_created_at_link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                    <input type="text" id="resume_created_at" value="<?php echo wpjb_date($form->getElement("created_at")->getValue()) ?>" name="created_at" style="visibility:hidden;padding:0;width:1px" size="1" />
                </div>
                <div class="misc-pub-section curtime wpjb-inline-section">
                    <span><?php _e("Modified", "wpjobboard") ?> <b class="resume_modified_date"><?php echo wpjb_date($form->getElement("modified_at")->getValue()) ?></b></span>
                    <a id="resume_modified_at_link" class="edit-timestamp hide-if-no-js" href="#"><?php _e("Edit") ?></a>
                    <input type="text" id="resume_modified_at" value="<?php echo wpjb_date($form->getElement("modified_at")->getValue()) ?>" name="modified_at" style="visibility:hidden;padding:0;width:1px" size="1" />
                </div>

                <div class="misc-pub-section misc-pub-section-last ">
                    <input type="hidden" name="part" value="_internal" />
                    <?php echo $form->getElement("is_active")->render(); ?><br/>
                </div>

                </div>


                <div id="major-publishing-actions">    
                    <div id="delete-action">
                        <a href="<?php esc_attr_e(wpjb_admin_url("resumes", "remove")."&".http_build_query(array("users"=>array($form->getId())))) ?>" class="submitdelete deletion wpjb-delete-item-confirm"><?php _e("Delete", "wpjobboard") ?></a>
                    </div>
                    <div id="publishing-action">
                        <?php if(Wpjb_Project::getInstance()->env("uses_cpt") && $form->getObject()->post_id): ?>
                        <a href="<?php esc_attr_e(admin_url(sprintf('post.php?post=%d&action=edit', $form->getObject()->post_id))) ?>" class="button"><?php _e("Advanced Options", "wpjobboard") ?></a>
                        <?php endif; ?>
                        <input type="submit" accesskey="p" tabindex="5" value="<?php _e("Update", "wpjobboard") ?>" class="button-primary" id="publish" name="publish">
                    </div>
                    <div class="clear"></div>
                </div>
                </div>

                </div>
                </div>
                </div>

                </div> 
            </div>
            <?php endif; ?>
            
        </div> <!-- #post-body -->


        
    </div> <!-- #poststuff -->

</div>
