<div class="wrap wpjb">

<h1>    
    <?php _e("Categories", "wpjobboard") ?> 
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("category", "add"); ?>"><?php _e("Add New", "wpjobboard") ?></a> 
</h1>
    
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.DeleteType = "<?php _e("category", "wpjobboard") ?>";
</script>

<form method="post" action="<?php esc_attr_e(wpjb_admin_url("category", "redirect", null, array("noheader"=>1))) ?>" id="posts-filter">

<div class="tablenav top">

<div class="alignleft actions">
    <select name="action" id="wpjb-action1">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
         <?php do_action( "wpjb_bulk_actions", "category" ); ?>
    </select>

    <input type="submit" class="button-secondary action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>" />

</div>
    
</div>

<div class="clear"/>&nbsp;</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <th style="" class="column-primary" scope="col"><?php _e("Title", "wpjobboard") ?></th>
            <th style="" scope="col"><?php _e("Id", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Slug", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Total Jobs", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Total Resumes", "wpjobboard") ?></th>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
	<tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?> author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $item->getId() ?>" name="item[]" />
            </th>
            <td class="post-title column-title column-primary">
                <strong><a title='<?php _e("Edit", "wpjobboard") ?>  "(<?php echo esc_html($item->title) ?>)"' href="<?php echo wpjb_admin_url("category", "edit", $item->id) ?>" class="row-title"><?php echo esc_html($item->title) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("category", "edit", $item->id) ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class="view"><a rel="permalink" title="<?php _e("View", "wpjobboard") ?>" href="<?php echo wpjb_link_to("category", $item) ?>"><?php _e("View", "wpjobboard") ?></a> | </span>
                    <span class=""><a href="<?php echo wpjb_admin_url("category", "delete", $item->id,  array("noheader"=>1)) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a></span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <td data-colname="<?php esc_attr_e("ID") ?>" class=""><?php echo $item->getId() ?></td>
            <td data-colname="<?php esc_attr_e("Slug", "wpjobboard") ?>" class=""><?php echo esc_html($item->slug) ?></td>
            <td data-colname="<?php esc_attr_e("Total Jobs", "wpjobboard") ?>" class=""><?php echo isset($stat[$item->id]) ? (int)$stat[$item->id]->jobs_total : "0" ?></td>
            <td data-colname="<?php esc_attr_e("Total Resumes", "wpjobboard") ?>" class=""><?php echo isset($stat[$item->id]) ? (int)$stat[$item->id]->resumes_total : "0" ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="tablenav">
    <div class="tablenav-pages">
        <?php
            echo paginate_links( array(
                'base' => wpjb_admin_url("category", "index", null)."%_%",
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
            <?php do_action( "wpjb_bulk_actions", "category" ); ?>
        </select>
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>" />

        <br class="clear"/>
    </div>

    <br class="clear"/>
</div>


</form>

</div>
