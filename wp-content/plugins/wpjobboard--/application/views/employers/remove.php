<div class="wrap wpjb">
    
<h1>
    <?php _e("Delete Employers", "wpjobboard"); ?>
</h1>

<?php $this->_include("flash.php"); ?>
    
    <form action="<?php esc_attr_e(wpjb_admin_url("employers", "remove", null, array("noheader"=>1))) ?>" method="post">
    <p><?php _e("You have specified these employers for deletion", "wpjobboard") ?>:</p>
    <ul>
        <?php foreach($list as $item): ?>
        <li class="<?php echo (get_current_user_id() == $item->user_id) ? "wpjb-myaccount-delete" : "" ?>">
            <?php $user = $item->getUser(true) ?>
            <input type="checkbox" name="users[]" value="<?php esc_attr_e($item->id) ?>" <?php checked(true) ?> />
            ID #<?php esc_attr_e($item->id) ?>:
            <strong><?php esc_html_e(trim($item->company_name)) ?></strong>
            <?php _e("linked user account", "wpjobboard") ?>
            <a href="<?php esc_attr_e(admin_url("user-edit.php?user_id={$user->ID}")) ?>"><strong><?php esc_html_e($user->user_nicename) ?></strong></a>
            <?php if(get_current_user_id() == $user->ID): ?>
            <span class="wpjb-none wpjb-myaccount-delete-warning" style="color:crimson; font-weight: bold"> &nbsp; <?php _e("(Cannot delete. This is your account.)", "wpjobboard") ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
	
    <fieldset>
        <p><legend><?php _e("What should be done with accounts owned by these users?", "wpjobboard") ?></legend></p>
	<ul style="list-style:none;">
            <li>
                <label for="delete_option0">
                    <input type="radio" id="delete_option0" name="delete_option" value="partial" checked="checked" />
                    <?php _e("Delete Employers only.", "wpjobboard") ?>
                </label>
            </li>
            <li>
                <label for="delete_option1">
                    <input type="radio" id="delete_option1" name="delete_option" value="full" /> 
                    <?php _e("Delete Employers AND their linked accounts.", "wpjobboard") ?>
                </label>
            </li>
	</ul>
    </fieldset>
    
    <fieldset>
        <p><legend><?php _e("What should be done with <em>jobs</em> owned by these users?", "wpjobboard") ?></legend></p>
	<ul style="list-style:none;">
            <li>
                <label for="jobs_option0">
                    <input type="radio" id="jobs_option0" name="jobs_option" value="delete" checked="checked" />
                    <?php _e("Delete Employers jobs.", "wpjobboard") ?>
                </label>
            </li>
            <li>
                <label for="jobs_option1">
                    <input type="radio" id="jobs_option1" name="jobs_option" value="unassign" /> 
                    <?php _e("Unassign Employers jobs (will be visible as posted by anonymous user).", "wpjobboard") ?>
                </label>
            </li>
	</ul>
    </fieldset>
	
    <p class="submit">
        <input type="submit" id="submit" class="button" value="<?php _e("Confirm Deletion", "wpjobboard") ?>" />
    </p>
    </form>

</div>

<script type="text/javascript">

jQuery(function($) {
    $("#delete_option0").click(function() {

        var acc = $(".wpjb-myaccount-delete");
        acc.find(".wpjb-myaccount-delete-warning").addClass("wpjb-none");
        
        var ckbox = acc.find("input[type=checkbox]");
        ckbox.attr("disabled", false);
        ckbox.attr("checked", ckbox.data("checked"));
    });
    
    $("#delete_option1").click(function() {

        var acc = $(".wpjb-myaccount-delete");
        acc.find(".wpjb-myaccount-delete-warning").removeClass("wpjb-none");
        
        var ckbox = acc.find("input[type=checkbox]");
        ckbox.data("checked", ckbox.is(":checked"));
        ckbox.attr("disabled", "disabled");
        ckbox.attr("checked", false);
    });
    
    $("input[name=delete_option]:checked").click();
});

</script>