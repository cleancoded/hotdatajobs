<div class="wrap wpjb">
    
<h1>    
    <?php _e("Memberships", "wpjobboard") ?> 
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("memberships", "add"); ?>"><?php _e("Add New", "wpjobboard") ?></a> 
</h1>
    
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.DeleteType = "<?php _e("membership", "wpjobboard") ?>";
</script>

<?php if(!empty($browsing)): ?>
<div class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
    <p>
        <?php printf(__("You are browsing memberships %s.", "wpjobboard"), join(" ".__("and", "wpjobboard")." ", $browsing)) ?>&nbsp;
        <?php _e("Click here to", "wpjobboard") ?>&nbsp;<a href="<?php esc_attr_e(wpjb_admin_url("memberships", "index")); ?>"><?php _e("browse all memberships", "wpjobboard") ?></a>.
    </p>
</div>
<?php endif; ?>

<form method="post" action="<?php esc_attr_e(wpjb_admin_url("memberships", "redirect", null, array("noheader"=>1))) ?>" id="posts-filter">

<div class="tablenav top">

<div class="alignleft actions">
    <select name="action" id="wpjb-action1">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
        <?php do_action( "wpjb_bulk_actions", "membership" ); ?>
    </select>

    <input type="submit" class="button-secondary action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>" />

</div>
    
</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <th style="" class="column-primary" scope="col"><?php _e("Membership ID", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Package", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("User", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Started", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Expires", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Status", "wpjobboard") ?></th>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
        <?php $package = new Wpjb_Model_Pricing($item->package_id) ?>
        <?php $user = new Wpjb_Model_User($item->user_id); ?>
        <?php $days_left = $item->daysLeft() ?>
	<tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?> author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $item->getId() ?>" name="item[]" />
            </th>
            <td class="post-title column-title column-primary">
                <strong><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("memberships", "edit", $item->id) ?>" class="row-title"><?php printf(__("Membership #%s", "wpjobboard"), str_pad($item->id, 4, 0, STR_PAD_LEFT)) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("memberships", "edit", $item->id) ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class=""><a href="<?php echo wpjb_admin_url("memberships", "delete", $item->id,  array("noheader"=>1)) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a></span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <td data-colname="<?php esc_attr_e("Package", "wpjobboard") ?>" class="">
                <a href="<?php esc_attr_e(wpjb_admin_url("memberships", "index", null, array("package_id"=>$package->id))) ?>"><?php esc_html_e($package->title) ?></a>
                <a style="" class="row-actions" href="<?php esc_attr_e(wpjb_admin_url("pricing", "edit", $package->id, array("listing"=>"employer-membership"))) ?>" title="<?php _e("Edit pricing package ...", "wpjobboard") ?>"><img style="vertical-align: middle" src="<?php esc_attr_e(plugins_url("wpjobboard/application/public/symbolic-link.png")) ?>" alt="<?php _e("link", "wpjobboard") ?>" /></a>
            </td>
            <td data-colname="<?php esc_attr_e("User", "wpjobboard") ?>" class="">
                <a href="<?php esc_attr_e(wpjb_admin_url("memberships", "index", null, array("user_id"=>$user->ID))) ?>"><?php esc_html_e($user->display_name) ?></a>
                <a style="" class="row-actions" href="<?php esc_attr_e(admin_url("user-edit.php?user_id={$user->ID}")) ?>" title="<?php _e("Edit user account ...", "wpjobboard") ?>"><img style="vertical-align: middle" src="<?php esc_attr_e(plugins_url("wpjobboard/application/public/symbolic-link.png")) ?>" alt="<?php _e("link", "wpjobboard") ?>" /></a>
            </td>
            <td data-colname="<?php esc_attr_e("Started", "wpjobboard") ?>" class="">
                <?php echo wpjb_date($item->started_at) ?>
            </td>
            <td data-colname="<?php esc_attr_e("Expires", "wpjobboard") ?>" class="">
                <?php if($item->expires_at == WPJB_MAX_DATE): ?>
                <?php _e("Never", "wpjobboard") ?>
                <?php else: ?>
                <?php echo wpjb_date($item->expires_at) ?>
                <br/>
                <small>
                <?php if($days_left < 0): ?>
                <?php _e("(expired)", "wpjobboard") ?>
                <?php elseif($days_left == 0): ?>
                <?php _e("(expires today)", "wpjobboard") ?>
                <?php else: ?>
                <?php echo sprintf( _n( '(1 day left)', '(%s days left)', $days_left, 'wpjobboard' ), $days_left ) ?>
                <?php endif; ?>
                </small>
                <?php endif; ?>
            </td>
            <td data-colname="<?php esc_attr_e("Status", "wpjobboard") ?>">
                <?php if($days_left < 0): ?>
                <span class="wpjb-bulb wpjb-bulb-expired"><?php _e("Expired", "wpjobboard") ?></span>
                <?php elseif($item->time->started_at > time()): ?>
                <span class="wpjb-bulb wpjb-bulb-inactive"><?php _e("Upcoming", "wpjobboard") ?></span>
                <?php else: ?>
                <span class="wpjb-bulb wpjb-bulb-active"><?php _e("Active", "wpjobboard") ?></span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="tablenav">
    <div class="tablenav-pages">
        <?php
            echo paginate_links( array(
                'base' => wpjb_admin_url("memberships", "index", null)."%_%",
                'format' => '&p=%#%',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total,
                'current' => $current,
                'add_args' => false
            ));
        ?>
    </div>


    <div class="alignleft actions">
        <select name="action2" id="wpjb-action2">
            <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
            <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
            <?php do_action( "wpjb_bulk_actions", "membership" ); ?>
        </select>
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>" />

        <br class="clear"/>
    </div>

    <br class="clear"/>
</div>


</form>

</div>
