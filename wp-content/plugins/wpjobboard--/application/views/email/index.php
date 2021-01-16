<div class="wrap wpjb">
    
    
        <h2 class="nav-tab-wrapper">
            <a href="<?php esc_attr_e(wpjb_admin_url("email")) ?>" class="nav-tab nav-tab-active">Emails</a>
            <a href="<?php esc_attr_e(wpjb_admin_url("email", "composer")) ?>" class="nav-tab">Live Composer</a>
            <a href="<?php esc_attr_e(wpjb_admin_url("email", "editor")) ?>" class="nav-tab">HTML Editor</a>
        </h2>
    
<a style="margin-top: 15px" class="button-secondary" href="<?php echo wpjb_admin_url("email", "add"); ?>"><?php _e("Add New", "wpjobboard") ?></a>
    
<?php $this->_include("flash.php"); ?>

<form method="post" action="" id="posts-filter">
<input type="hidden" name="action" id="wpjb-action-holder" value="-1" />

<?php wp_enqueue_style("wpjb-glyphs") ?>

<div class="clear"/>&nbsp;</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="width:0%">&nbsp;</th>
            <th style="width:25%" class="column-primary" scope="col"><?php _e("Mail Title", "wpjobboard") ?></th>
            <th style="width:30%" class="" scope="col"><?php _e("Name", "wpjobboard") ?></th>
            <th style="width:20%" class="column-icon" scope="col"><?php _e("Mail From", "wpjobboard") ?></th>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php $i = 0; ?>
        <?php foreach($data as $j => $group): ?>
        <tr valign="top" class="author-self status-publish iedit">
            <td colspan="3"><h3 style="font-weight:normal"><?php echo $desc[$j] ?></h3></td>
        </tr>
        <?php $i++; ?>
        <?php foreach($group as $item): ?>
	<tr valign="top" class="<?php if($i%2==0 || true): ?>alternate <?php endif; ?>  author-self status-publish iedit">
            <td>
                
            </td>
            <td class="post-title column-title column-primary">
                
                <strong><a title='<?php _e("Edit", "wpjobboard") ?>  "(<?php esc_attr_e($item->mail_title) ?>)"' href="<?php echo wpjb_admin_url("email", "edit", $item->getId()); ?>" class="row-title"><?php esc_html_e($item->mail_title) ?></a></strong>
                <?php if($item->sent_to == 5): ?>
                <span class="row-actions"><a href="<?php esc_attr_e(wpjb_admin_url("email", "delete", $item->id, array("noheader"=>1))) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a></span>
                <?php endif; ?>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <td data-colname="<?php esc_attr_e("Name", "wpjobboard") ?>" class=""><code><?php esc_html_e($item->name) ?></code></td>
            <td data-colname="<?php esc_attr_e("Mail From", "wpjobboard") ?>" class="author column-author"><?php esc_html_e($item->mail_from) ?></td>
        </tr>
        <?php $i++; ?>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>



</form>

</div>

