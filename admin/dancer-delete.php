<?php

if (isset($_POST['confirm_delete'])):
    $counts = $dancer->delete();

?>
<div class="wrap" id="nsevent">
    <h2><?php _e('Delete Dancer', 'nsevent'); ?></h2>
    
    <p><?php printf(__('Delete request for "%s" (database ID: %d):', 'nsevent'), $dancer->get_name(), $dancer->get_id()); ?></p>
    
    <ul>
        <li><?php printf(__('Deleted %d dancer(s).', 'nsevent'), $counts['dancer']); ?></li>
        <li><?php printf(__('Deleted %d registration(s).', 'nsevent'), $counts['registrations']); ?></li>
        <li><?php printf(__('Deleted %d "housing needed" record(s).', 'nsevent'), $counts['housing_needed']); ?></li>
        <li><?php printf(__('Deleted %d "housing provider" record(s).', 'nsevent'), $counts['housing_provider']); ?></li>
    </ul>
    
    <p><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->get_id(); ?>&amp;request=index-event"><?php printf(__('Back to %s', 'nsevent'), $event->get_name()); ?></a></p>
</div>
<?php else: ?>
<div class="wrap" id="nsevent">
	<h2><?php _e('Delete Dancer', 'nsevent'); ?></h2>

	<form action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->get_id(); ?>&amp;request=dancer-delete&amp;parameter=<?php echo $_GET['parameter'] ?>" method="post">
	    <ul>
	        <li><?php echo esc_html($dancer->get_name()); ?></li>
	        <li><?php echo esc_html($dancer->position()); ?></li>
<?php if ($event->levels): ?>
            <li><?php echo esc_html($dancer->level()); ?></li>
<?php endif; ?>
	        <li><?php echo $dancer->get_date_registered('Y-m-d, h:i:s A'); ?></li>
	        <li><?php printf(__('(Database ID: %d)', 'nsevent'), $dancer->get_id()); ?></li>
	    </ul>

	    <p><?php NSEvent_FormInput::checkbox('confirm_delete', array('label' => __('I\'m sure I want to delete this dancer.', 'nsevent'))); ?></p>
	    <input type="submit" class="button-primary" value="<?php _e('Delete Dancer', 'nsevent'); ?>" />
    </form>
</div>
<?php endif; ?>
