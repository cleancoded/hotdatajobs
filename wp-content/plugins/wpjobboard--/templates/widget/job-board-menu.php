<?php echo $theme->before_widget ?>
<?php if($title) echo $theme->before_title.$title.$theme->after_title ?>

<ul id="wpjb_widget_jobboardmenu" class="wpjb_widget">
    <?php if($can_post): ?>
    <li>
        <a href="<?php echo wpjb_link_to("step_add", null, array(), wpjb_conf("urls_link_job_add")) ?>">
            <?php _e("Post a Job", "wpjobboard") ?>
        </a>
    </li>
    <?php endif; ?>
    <li>
        <a href="<?php echo wpjb_link_to("home") ?>">
            <?php _e("View Jobs", "wpjobboard") ?>
        </a>
    </li>
    <li>
        <a href="<?php echo wpjb_link_to("advsearch") ?>">
            <?php _e("Advanced Search", "wpjobboard") ?>
        </a>
    </li>

    <?php if(current_user_can("manage_jobs")): ?>
        <li>
            <a href="<?php echo wpjb_link_to("employer_home") ?>">
                <?php _e("Employer Dashboard", "wpjobboard") ?>
            </a>
        </li>
        <li>
            <a href="<?php echo wpjb_link_to("employer_panel") ?>">
                <?php _e("Company Jobs", "wpjobboard") ?>
            </a>
        </li>
        <li>
            <a href="<?php echo wpjb_link_to("employer_edit") ?>">
                <?php _e("Company Profile", "wpjobboard") ?>
            </a>
        </li>
        <li>
            <a href="<?php echo wpjb_link_to("membership") ?>">
                <?php _e("Membership", "wpjobboard") ?>
            </a>
        </li>
        <li>
            <a href="<?php echo wpjb_link_to("employer_logout") ?>">
                <?php _e("Logout", "wpjobboard") ?>
            </a>
        </li>
    <?php elseif(get_option('users_can_register')): ?>
        <li>
            <a href="<?php echo wpjb_link_to("employer_login") ?>">
                <?php _e("Employer Login", "wpjobboard") ?>
            </a>
        </li>
        <li>
            <a href="<?php echo wpjb_link_to("employer_new") ?>">
                <?php _e("Employer Registration", "wpjobboard") ?>
            </a>
        </li>
    <?php endif; ?>


</ul>

<?php echo $theme->after_widget ?>