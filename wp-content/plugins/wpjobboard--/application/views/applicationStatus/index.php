<div class="wrap wpjb">

<h1>    
    <?php _e("Application Statuses", "wpjobboard") ?> 
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("applicationStatus", "add"); ?>"><?php _e("Add New", "wpjobboard") ?></a> 
</h1>
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.DeleteType = "<?php _e("application status", "wpjobboard") ?>";
</script>

<form method="post" action="<?php esc_attr_e(wpjb_admin_url("applicationStatus", "redirect", null, array("noheader"=>1))) ?>" id="posts-filter">

<div class="tablenav top">

<div class="alignleft actions">
    <select name="action" id="wpjb-action1">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
         <?php do_action( "wpjb_bulk_actions", "applicationStatus" ); ?>
    </select>

    <input type="submit" class="button action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>" />

</div>
    
</div>

<div class="clear"/>&nbsp;</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <th style="" class="column-primary" scope="col"><?php _e("Title", "wpjobboard") ?></th>
            <!--th style="" class="" scope="col"><?php _e("Description", "wpjobboard") ?></th-->
            <th style="" class="" scope="col"><?php _e("E-mail Template", "wpjobboard") ?></th>
            <!--th style="" class="" scope="col"><?php _e("Order", "wpjobboard") ?></th-->
            <th style="" class="" scope="col"><?php _e("Example", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Total Applications", "wpjobboard") ?></th>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
	<tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?>  author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $i ?>" name="item[]" />
            </th>
            <td class="post-title column-title column-primary">
                <strong><a title='<?php _e("Edit", "wpjobboard") ?>  "(<?php echo esc_html($item["label"]) ?>)"' href="<?php echo wpjb_admin_url("applicationStatus", "edit", $i); ?>" class="row-title"><?php echo esc_html($item["label"]) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("applicationStatus", "edit", $i); ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class=""><a href="<?php echo wpjb_admin_url("applicationStatus", "delete", $i,  array("noheader"=>1)) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a></span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <!--td data-colname="<?php esc_attr_e("Description", "wpjobboard") ?>" class=""><?php echo esc_html($item["description"]) ?></td-->
            <td data-colname="<?php esc_attr_e("E-mail Template", "wpjobboard") ?>" class="">
                <?php if( $item["email_template"] > 0 ): ?>
                <?php $email_template = new Wpjb_Model_Email( $item["email_template"] ); ?>
                <a href="<?php echo wpjb_admin_url( "email", "edit", $item["email_template"] ); ?>"><?php echo esc_html( $email_template->name); ?></a>
                <?php endif; ?>
            </td>
            <!--td data-colname="<?php esc_attr_e("Order", "wpjobboard") ?>" class=""><?php echo esc_html($item["order"]) ?></td-->
            <td data-colname="<?php esc_attr_e("Example", "wpjobboard") ?>" class=""><span class="wpjb-bulb" style="background-color: <?php echo $item["color"]; ?>; color: <?php echo $item["tcolor"]; ?>;"><?php echo esc_html( $item["label"] ) ?></span></td>
            <td data-colname="<?php esc_attr_e("Total Applications", "wpjobboard") ?>" class="">
                <a href="<?php echo esc_html( wpjb_admin_url( "application", "index", null, array( "filter" => (int)$item['id'] ) ) ); ?>"><?php $c = wpjb_count_applications( $item['id'] ); echo sprintf(_n( "1 Application", "%d Applications", $c, "wpjobboard" ), $c ); ?></a>
            </td>
            
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="tablenav">
    <div class="tablenav-pages">
        <?php
            echo paginate_links( array(
                'base' => wpjb_admin_url("applicationStatus", "index", null)."%_%",
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
             <?php do_action( "wpjb_bulk_actions", "applicationStatus" ); ?>
        </select>
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>" />

        <br class="clear"/>
    </div>

    <br class="clear"/>
</div>


</form>

</div>

