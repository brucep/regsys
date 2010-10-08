<?php

if (!$dancer->populate_housing_info())
	throw new Exception('Housing information not found for Dancer ID: %d', $dancer->id);


if (isset($_POST['confirm_delete'])):
    $nsevent_database = NSEvent_Database::get_instance();

	if (isset($dancer->available))
		$statement = $nsevent_database->query('DELETE FROM %1$s_housing_providers WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => $event->id, ':dancer_id' => $dancer->id));
	else
		$statement = $nsevent_database->query('DELETE FROM %1$s_housing_needed WHERE event_id = :event_id AND dancer_id = :dancer_id LIMIT 1', array(':event_id' => $event->id, ':dancer_id' => $dancer->id));

?>
<div class="wrap" id="nsevent">
	<h2><?php _e('Delete Housing Information', 'nsevent'); ?></h2>
    
	<p><?php printf('<strong>%s (%d)</strong>: <em>%s</em>', $dancer->name(), $dancer->id, $dancer->housing_type); ?></p>
	<p><?php printf('Deleted %d row(s).', $statement->rowCount()); ?></p>

	<p><a href="?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=dancer&amp;parameter=<?php echo $dancer->id; ?>"><?php printf(__('Back to "%s"', 'nsevent'), $dancer->name()); ?></a></p>
</div>
<?php else: ?>
<div class="wrap" id="nsevent">
	<h2><?php _e('Delete Housing Information', 'nsevent'); ?></h2>

	<form action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=housing-delete&amp;parameter=<?php echo $_GET['parameter'] ?>" method="post">
		<p><?php printf('<strong>%s (%d)</strong>: <em>%s</em>', $dancer->name(), $dancer->id, $dancer->housing_type); ?></p>
	    <p><?php NSEvent_FormInput::checkbox('confirm_delete', array('label' => __('I\'m sure I want to delete housing information for this dancer.', 'nsevent'))); ?></p>
	    <input type="submit" class="button-primary" value="<?php _e('Delete Housing Information', 'nsevent'); ?>" />
    </form>
</div>
<?php endif; ?>
