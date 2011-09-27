<?php

if (isset($_POST['confirm_delete'])):
    $counts = $dancer->delete();

?>
<div class="wrap" id="nsevent">
    <h2><?php _e('Delete Dancer', 'nsevent'); ?></h2>
    
    <p><?php printf(__('Delete request for "%s" (database ID: %d):', 'nsevent'), $dancer->get_name(), $dancer->get_id()); ?></p>
    
    <ul>
        <li><?php printf(__('Deleted %d %s.', 'nsevent'), $counts['dancer'], _n('dancer', 'dancers', $counts['dancer'], 'nsevent')); ?></li>
        <li><?php printf(__('Deleted %d %s.', 'nsevent'), $counts['registrations'], _n('registration', 'registrations', $counts['dancer'], 'nsevent')); ?></li>
        <li><?php printf(__('Deleted %d %s.', 'nsevent'), $counts['housing'], _n('housing record', 'housing records', $counts['dancer'], 'nsevent')); ?></li>
    </ul>
    
    <p><a href="<?php echo $event->get_request_href('index-event'); ?>"><?php printf(__('Back to %s', 'nsevent'), $event->get_name()); ?></a></p>
</div>
<?php else: ?>
<div class="wrap" id="nsevent">
	<h2><?php _e('Delete Dancer', 'nsevent'); ?></h2>

	<form action="<?php echo $event->get_request_href('dancer-delete', array('dancer' => $dancer->get_id())); ?>" method="post">
	    <ul>
	        <li><?php echo esc_html($dancer->get_name()); ?></li>
	        <li><?php echo esc_html($dancer->get_position()); ?></li>
<?php if ($event->has_levels()): ?>
            <li><?php echo esc_html($event->get_level_for_index($dancer->get_level())); ?></li>
<?php endif; ?>
	        <li><?php echo 'Registered: ', $dancer->get_date_registered('Y-m-d, h:i:s A'); ?></li>
	        <li><?php printf(__('(Database ID: %d)', 'nsevent'), $dancer->get_id()); ?></li>
	    </ul>

	    <p><?php NSEvent_FormInput::checkbox('confirm_delete', array('label' => __('I\'m sure I want to delete this dancer.', 'nsevent'))); ?></p>
	    <input type="submit" class="button-primary" value="<?php _e('Delete Dancer', 'nsevent'); ?>" />
    </form>
</div>
<?php endif; ?>
