<?php
# http://www.onextrapixel.com/2009/07/01/how-to-design-and-style-your-wordpress-plugin-admin-panel/
# http://www.onextrapixel.com/2009/06/22/how-to-add-pagination-into-list-of-records-or-wordpress-plugin/

require dirname(dirname(__FILE__)).'/includes/form-validation.php';
NSEvent_FormValidation::set_error_messages();

if (!empty($_POST)) {
	NSEvent_FormValidation::add_rules(array(
		'name' => 'trim|required'
		));

	if (NSEvent_FormValidation::validate()) {
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}nsevent_events (name, prereg_end) VALUES ( %s, %s )", trim($_POST['event_name']), strtotime($_POST['prereg_end'])));
		
		if (isset($_POST['set_current_event'])) {
			$options = array_merge(self::$default_options, get_option('nsevent'));
			$options['current_event_id'] = $wpdb->insert_id;
			update_option('nsevent', $options);
		}
		
		printf('<div class="wrap"><h2>%s</h2></div><a href="%s/wp-admin/admin.php?page=nsevent&amp;event_id=%d&amp;request=index-event">%s</a>', __('Add New Event', 'nsevent'), get_bloginfo('wpurl'), $wpdb->insert_id, __('Event Added', 'nsevent'));
		return;
	}
}
elseif (isset($event)) {
	# Put values into POST so that form is pre-populated.
	$reflection = new ReflectionObject($event);
	foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
		$_POST[$property->getName()] = $property->getValue($event);
	}
}

?>

<div class="wrap" id="nsevent">
	<h2><?php echo (isset($event)) ? __('Edit Event', 'nsevent') : __('Add New Event', 'nsevent'); ?></h2>

	<form action="<?php echo (isset($event)) ? $event->get_request_href('event-edit') : sprintf('%s/wp-admin/admin.php?page=nsevent&amp;event_id=add&amp;request=event-edit', get_bloginfo('wpurl')); ?>" method="post">
		<ul>			
			<li><?php NSEvent_FormInput::text('name', array('label' =>  __('Name', 'nsevent'))); ?></li>
			<li><?php NSEvent_FormInput::text('prereg_end', array('label' => __('Preregistration End Date', 'nsevent'))); ?></li>
			<li><?php NSEvent_FormInput::checkbox('set_current_event', array('label' => __('Use this event for the registration form.', 'nsevent'))); ?></li>
		</ul>

		<input type="submit" class="button-primary" value="<?php _e('Edit Event', 'nsevent'); ?>" />
	</form>
</div>
