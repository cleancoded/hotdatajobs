<div class="wrap wpjb">
    
<h1>
    <?php _e("Import", "wpjobboard") ?> 
    <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("import", "add")) ?>"><?php _e("Schedule New Import", "wpjobboard") ?></a> 
    <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("import", "xml")) ?>"><?php _e("XML Import", "wpjobboard") ?></a> 
    <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("import", "csv")) ?>"><?php _e("CSV Import", "wpjobboard") ?></a> 
    /
    <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("import", "exml")) ?>"><?php _e("XML Export", "wpjobboard") ?></a> 
</h1>
<?php $this->_include("flash.php"); ?>

<form method="post" action="<?php esc_attr_e(wpjb_admin_url("import", "redirect", null, array("noheader"=>1))) ?>" id="posts-filter">
<input type="hidden" name="filter" value="<?php esc_attr_e($filter) ?>" />

<div class="tablenav top">

<div class="alignleft actions">
    <select id="wpjb-action1" name="action">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
    </select>

    <input type="submit" class="button-secondary action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>" />

</div>

</div>
    
<div class="clear"/>&nbsp;</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <th class="column-primary" scope="col"><?php _e("Engine", "wpjobboard") ?></th>
            <th class="" scope="col"><?php _e("Keyword", "wpjobboard") ?></th>
            <th class="" scope="col"><?php _e("Location", "wpjobboard") ?></th>
            <th class="" scope="col"><?php _e("Last Run", "wpjobboard") ?></th>
            <th class="" scope="col"><?php _e("Status", "wpjobboard") ?></th>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
        <tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?> author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $item->id ?>" name="item[]"/>
            </th>
            <td class="column-title column-primary">
                <strong><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php esc_attr_e(wpjb_admin_url("import", "edit", $item->id)) ?>" class="wpjb-row-title"><?php esc_html_e(ucfirst($item->engine)." ID: ".$item->id) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a href="<?php esc_attr_e(wpjb_admin_url("import", "edit", $item->id)) ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class=""><a href="<?php esc_attr_e(wpjb_admin_url("import", "delete", $item->id, array("noheader"=>1))) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a> | </span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <td data-colname="<?php esc_attr_e("Keyword", "wpjobboard") ?>">
                <?php esc_html_e($item->keyword) ?>
            </td>
            <td data-colname="<?php esc_attr_e("Location", "wpjobboard") ?>">
                <?php esc_html_e($item->country) ?>
                <?php if($item->location): ?>, <?php esc_html_e($item->location) ?><?php endif; ?>
            </td>
            <td data-colname="<?php esc_attr_e("Last Run", "wpjobboard") ?>">
                <?php if($item->last_run == "0000-00-00 00:00:00"): ?>
                <?php _e("Never", "wpjobboard") ?>
                <?php else: ?>
                <?php esc_html_e($item->last_run) ?><br/>
                <?php echo daq_time_ago_in_words(strtotime($item->last_run))." ".__("ago", "wpjobboard") ?>
                <?php endif; ?>
            </td>
            <td data-colname="<?php esc_attr_e("Status", "wpjobboard") ?>">
                <?php if($item->last_run == "0000-00-00 00:00:00"): ?>
                -
                <?php elseif($item->success): ?>
                <?php _e("OK", "wpjobboard") ?><br/>
                <?php else: ?>
                <?php _e("Failed", "wpjobboard") ?>
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
                'base' => wpjb_admin_url("import", "index")."%_%",
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
        <select id="wpjb-action2" name="action2">
            <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
            <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>tion>
        </select>
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>"/>

        <br class="clear"/>
    </div>

    <br class="clear"/>
</div>


</form>

</div>


