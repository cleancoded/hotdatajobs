<div class="wrap wpjb">
    
<h1><?php _e("Promotions", "wpjobboard") ?> 
    <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("discount", "add")) ?>"><?php _e("Add New", "wpjobboard") ?></a> 
</h1>
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.DeleteType = "<?php _e("discount", "wpjobboard") ?>";
</script>

<form method="post" action="<?php esc_attr_e(wpjb_admin_url("discount", "redirect", null, array("noheader"=>1))) ?>" id="posts-filter">

<div class="tablenav top">

<div class="alignleft actions">
    <select name="action" id="wpjb-action1">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
        <option value="activate"><?php _e("Activate", "wpjobboard") ?></option>
        <option value="deactivate"><?php _e("Deactivate", "wpjobboard") ?></option>
        <?php do_action( "wpjb_bulk_actions", "discount" ); ?>
    </select>

    <input type="submit" class="button-secondary action" id="wpjb-doaction1" value="<?php esc_attr_e("Apply", "wpjobboard") ?>" />

</div>
    
</div>
    
<div class="clear"/>&nbsp;</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <th style="" class="column-primary" scope="col"><?php _e("Title", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("ID") ?></th>
            <th style="" class="" scope="col"><?php _e("Code", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Discount", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Expires At", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Usage", "wpjobboard") ?></th>
            <th style="" class="fixed column-icon" scope="col"><?php _e("Is Active", "wpjobboard") ?></th>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
	<tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?>  author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $item->getId() ?>" name="item[]"/>
            </th>
            
            <td class="post-title column-title column-primary">
                <strong><a title='<?php _e("Edit", "wpjobboard") ?>  "(<?php echo esc_html($item->title) ?>)"' href="<?php echo wpjb_admin_url("discount", "edit", $item->getId()); ?>" class="row-title"><?php echo esc_html($item->title) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("discount", "edit", $item->getId()); ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class=""><a href="<?php echo wpjb_admin_url("discount", "delete", $item->getId(), array("noheader"=>1)) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a> | </span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <td data-colname="<?php esc_attr_e("ID") ?>" class=""><?php echo $item->getId() ?></td>
            <td data-colname="<?php esc_attr_e("Code", "wpjobboard") ?>" class="author column-author"><?php echo esc_html($item->code) ?></td>
            <td data-colname="<?php esc_attr_e("Discount", "wpjobboard") ?>" class="author column-author"><?php echo esc_html($item->getTextDiscount()) ?></td>
            <td data-colname="<?php esc_attr_e("Expires At", "wpjobboard") ?>" class="categories column-categories"><?php echo esc_html($item->expires_at) ?></td>

            <td data-colname="<?php esc_attr_e("Usage", "wpjobboard") ?>" class="date column-date">
                <?php if($item->max_uses > 0): ?>
                <?php echo $item->used."/".$item->max_uses." (".round($item->used/$item->max_uses*100)."%)"; ?>
                <?php else: ?>
                -
                <?php endif; ?>
            </td>
            <td data-colname="<?php esc_attr_e("Is Active", "wpjobboard") ?>" class="date column-date">
                <?php echo ($item->is_active) ? __('Yes', "wpjobboard") : __('No', "wpjobboard") ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="tablenav">
    <div class="tablenav-pages">
        <?php
            echo paginate_links( array(
                'base' => wpjb_admin_url("discount", "index")."%_%",
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
            <option value="activate"><?php _e("Activate", "wpjobboard") ?></option>
            <option value="deactivate"><?php _e("Deactivate", "wpjobboard") ?></option>
            <?php do_action( "wpjb_bulk_actions", "discount" ); ?>
        </select>
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>" />

        <br class="clear"/>
    </div>

    <br class="clear"/>
</div>


</form>




</div>