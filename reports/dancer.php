<?php

if (!isset($_GET['dancer'])) {
	throw new Exception(__('Dancer ID not specified.', 'nsevent'));
}
elseif (!$dancer = $event->get_dancer_by_id_with_housing_info($_GET['dancer'])) {
	throw new Exception(sprintf(__('Dancer ID not found: %d', 'nsevent'), $_GET['dancer']));
}

$options = get_option('nsevent');
$options = array_merge(self::$default_options, $options);

?>

<div class="wrap" id="nsevent">
<div id="dancer">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

	<h3>
	    <?php echo esc_html($dancer->get_name()); ?>
<?php if (current_user_can('administrator')): ?>
		<?php echo $event->get_request_link('dancer-edit', __('Edit Dancer', 'nsevent'), array('dancer' => (int) $dancer->get_id()), 'button add-new-h3'); echo "\n"; ?>
		<?php echo $event->get_request_link('dancer-delete', __('Delete Dancer', 'nsevent'), array('dancer' => (int) $dancer->get_id()), 'button add-new-h3'); echo "\n"; ?>
<?php endif; ?>
    </h3>

	<ul>
		<li><a href="mailto:<?php echo rawurlencode(sprintf('%s <%s>', $dancer->get_name(), $dancer->get_email())); ?>"><?php echo $dancer->get_email(); ?></a></li>
		<li><strong><?php _e('Position:', 'nsevent'); ?></strong> <?php echo esc_html($dancer->get_position()); ?></li>
<?php if ($event->has_levels()): ?>
		<li><strong><?php _e('Level:', 'nsevent'); ?></strong> <?php echo esc_html($event->get_level_for_index($dancer->get_level())); ?></li>
<?php endif; ?>
<?php if ($dancer->is_vip()): ?>
		<li><strong><?php _e('VIP', 'nsevent'); ?></strong></li>
<?php elseif ($dancer->is_volunteer()): ?>
		<li><strong><?php _e('Volunteer', 'nsevent'); ?></strong></li>
<?php endif; ?>
	</ul>

	<h4>
<?php _e('Registrations', 'nsevent'); ?>
<?php 	if (current_user_can('administrator')): ?>
		<a href="<?php echo $event->get_request_href('registration-add', array('dancer' => (int) $dancer->get_id())); ?>" class="button add-new-h4"><?php _e('Add Registration', 'nsevent'); ?></a>
<?php 		if ($dancer->get_payment_method() == 'PayPal'): ?>
		<a href="<?php echo NSEvent::paypal_href($dancer, $options); ?>" class="button add-new-h4"><?php _e('PayPal Link', 'nsevent'); ?></a>
<?php 		endif; ?>
<?php 	endif; ?>
</h4>
<?php if ($dancer->get_registered_items()): ?>
	<ul>
<?php 	foreach($dancer->get_registered_items() as $item): ?>
		<li><?php echo esc_html($item->get_name()); if ($item->get_registered_meta()) printf(' (%s)', esc_html(ucfirst($item->get_registered_meta()))); ?></li>
<?php 	endforeach; ?>
	</ul>
<?php else: ?>
	<p><?php _e('There are no registrations for this dancer.', 'nsevent'); ?></p>
<?php endif; ?>

<?php if ($dancer->get_housing_type()): ?>
	<h4>
		<?php echo esc_html($dancer->get_housing_type()); ?>
<?php 	if (current_user_can('administrator')): ?>
		<a href="<?php echo $event->get_request_href('housing-delete', array('dancer' => (int) $dancer->get_id())); ?>" class="button add-new-h4"><?php _e('Delete Housing Info', 'nsevent'); ?></a>
<?php 	endif; ?>
	</h4>
	
	<ul>
<?php 	if (isset($dancer->available)): ?>
		<li><?php printf('Available: %d', $dancer->available); ?></li>
<?php 		if ($dancer->smoking): ?>
		<li><?php _e('Smokes', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->pets): ?>
		<li><?php _e('Has pets', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 	else: ?>
<?php 		if ($dancer->no_smoking): ?>
		<li><?php _e('Prefers no smoking', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->no_pets): ?>
		<li><?php _e('Prefers no pets', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 	endif; ?>
		<li><?php echo esc_html($dancer->get_housing_gender()); ?></li>
		<li><?php echo esc_html($dancer->get_housing_nights($event->get_housing_nights())); ?></li>
		<li><?php echo esc_html($dancer->comment); ?></li>
	</ul>
<?php endif; ?>
</div>
</div>
