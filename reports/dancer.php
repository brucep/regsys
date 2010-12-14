<?php

if (!isset($_GET['dancer']))
	throw new Exception(__('Dancer ID not specified.', 'nsevent'));
elseif (!$dancer = NSEvent_Dancer::find($_GET['dancer']))
	throw new Exception(sprintf(__('Dancer ID not found: %d', 'nsevent'), $_GET['dancer']));

$options = get_option('nsevent');
$options = array_merge(self::$default_options, $options);

?>

<div class="wrap" id="nsevent">
<div id="dancer">
	<h2><?php $event->request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3>
	    <?php echo esc_html($dancer->name()); ?>
<?php if (current_user_can('administrator')): ?>
		<?php $event->request_link('dancer-edit', __('Edit Dancer', 'nsevent'), array('dancer' => (int) $dancer->id), 'button add-new-h3'); echo "\n"; ?>
		<?php $event->request_link('dancer-delete', __('Delete Dancer', 'nsevent'), array('dancer' => (int) $dancer->id), 'button add-new-h3'); echo "\n"; ?>
<?php endif; ?>
    </h3>

	<ul>
		<li><a href="mailto:<?php echo rawurlencode(sprintf('%s <%s>', $dancer->name(), $dancer->email)); ?>"><?php echo $dancer->email; ?></a></li>
		<li><strong><?php _e('Position:', 'nsevent'); ?></strong> <?php echo esc_html($dancer->position()); ?></li>
<?php if ($event->levels()): ?>
		<li><strong><?php _e('Level:', 'nsevent'); ?></strong> <?php echo esc_html($dancer->level()); ?></li>
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
		<a href="<?php $event->request_href('registration-add', array('dancer' => (int) $dancer->id)); ?>" class="button add-new-h4"><?php _e('Add Registration', 'nsevent'); ?></a>
<?php 		if ($dancer->payment_method == 'PayPal'): ?>
		<a href="<?php echo NSEvent::paypal_href($dancer, $options); ?>" class="button add-new-h4"><?php _e('PayPal Link', 'nsevent'); ?></a>
<?php 		endif; ?>
<?php 	endif; ?>
</h4>
<?php if ($dancer->registrations()): ?>
	<ul>
<?php 	foreach($dancer->registrations() as $reg): ?>
		<li><?php echo esc_html($reg->item()->name); if ($reg->item_meta) printf(' (%s)', esc_html(ucfirst($reg->item_meta))); ?></li>
<?php 	endforeach; ?>
	</ul>
<?php else: ?>
	<p><?php _e('There are no registrations for this dancer.', 'nsevent'); ?></p>
<?php endif; ?>

<?php if ($dancer->populate_housing_info()): ?>
	<h4>
		<?php echo esc_html($dancer->housing_type); ?>
<?php 	if (current_user_can('administrator')): ?>
		<a href="<?php $event->request_href('housing-delete', array('dancer' => (int) $dancer->id)); ?>" class="button add-new-h4"><?php _e('Delete Housing Info', 'nsevent'); ?></a>
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
<?php 		if ($dancer->car): ?>
		<li><?php _e('Has car', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->no_smoking): ?>
		<li><?php _e('Prefers no smoking', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 		if ($dancer->no_pets): ?>
		<li><?php _e('Prefers no pets', 'nsevent'); ?></li>
<?php 		endif; ?>
<?php 	endif; ?>
		<li><?php echo esc_html($dancer->housing_gender()); ?></li>
		<li><?php echo esc_html($dancer->housing_nights()); ?></li>
		<li><?php echo esc_html($dancer->comment); ?></li>
	</ul>
<?php endif; ?>
</div>
</div>
