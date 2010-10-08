<?php

require dirname(dirname(__FILE__)).'/includes/form-validation.php';
NSEvent_FormValidation::set_error_messages();

$options = get_option('nsevent');
$options = array_merge($this->default_options, $options); # Make sure keys exists

$items = NSEvent_Item::find_all();

if (!empty($_POST)):
	NSEvent_FormValidation::add_rule('items', 'NSEvent::validate_items');

	if (NSEvent_FormValidation::validate()):
		foreach (NSEvent::$validated_items as $item)
			NSEvent_Registration::add(array(
				'dancer_id' => $dancer->id,
				'item_id'   => $item->id,
				'price'     => $item->get_price_for_discount(($dancer->is_vip() ? 'vip' : (int) $dancer->payment_discount), ($event->early_end and time() < $event->early_end)),
				'item_meta' => (!isset($_POST['item_meta'][$item->id]) ? '' : $_POST['item_meta'][$item->id]),
				));

?>
<div class="wrap" id="nsevent">
    <h2><?php _e('Add Registrations for Dancer', 'nsevent'); ?></h2>

	<p><?php _e('Registrations added:', 'nsevent'); ?></p>

	<ul>
<?php foreach (NSEvent::$validated_items as $item): ?>
		<li><?php echo esc_html($item->name); ?></li>
<?php endforeach; ?>
	</ul>

	<p><strong><a href="<?php echo NSEvent::paypal_href($dancer, $options, array_keys(NSEvent::$validated_items)); ?>"><?php _e('PayPal Link', 'nsevent'); ?></a><strong></p>

    <p><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=dancer&amp;parameter=<?php echo $dancer->id; ?>"><?php printf(__('Back to "%s"', 'nsevent'), $dancer->name()); ?></a></p>
</div>
<?php
	endif; # form validation
	exit;
endif;

$dancer_item_ids = array();
foreach ($dancer->registrations() as $reg)
	$dancer_item_ids[] = $reg->item_id;
?>
<div class="wrap" id="nsevent">
	<h2><?php _e('Add Registrations for Dancer', 'nsevent'); ?></h2>

	<form id="registration-add" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->id; ?>&amp;request=registration-add&amp;parameter=<?php echo $_GET['parameter'] ?>" method="post">
		<p><?php echo __('Dancer:', 'nsevent'), ' ', $dancer->name(); ?></p>
		<?php NSEvent_FormInput::hidden('position', array('value' => $dancer->position)); // Needed when $item->has_meta === 'position' ?>

<?php 	if ($items): ?>
<?php 		foreach ($items as $item): ?>
			<?php NSEvent_FormValidation::get_error('item_'.$item->id); echo "\n"; ?>
			<div class="field">
				<?php NSEvent_FormInput::checkbox(sprintf('items[%d]', $item->id), array('label' => $item->name, 'disabled' => (in_array($item->id, $dancer_item_ids) or !$item->openings()), 'checked' => in_array($item->id, $dancer_item_ids))); ?>

<?php  			if ($item->has_meta === 'position'): ?>
											<div class="meta"><?php _e('Register as:', 'nsevent'); ?>&nbsp;<?php NSEvent_FormInput::radio(sprintf('item_meta[%d]', $item->id), array('value' => 'lead', 'label' => __('Lead', 'nsevent'), 'disabled' => !$item->openings('lead'))); ?>&nbsp;<?php NSEvent_FormInput::radio(sprintf('item_meta[%d]', $item->id), array('value' => 'follow', 'label' => __('Follow', 'nsevent'), 'disabled' => !$item->openings('follow'))); ?></div>
<?php 			elseif ($item->has_meta === 'partner_name'): ?>
											<div class="meta"><?php NSEvent_FormInput::text(sprintf('item_meta[%d]', $item->id), array('label' => 'Partner:&nbsp;', 'placeholder' => __('Partner\'s name', 'nsevent'))); ?></div>
<?php 			elseif ($item->has_meta === 'team_members'): ?>
											<div class="meta"><?php echo $item->description; ?><br />
											<?php NSEvent_FormInput::textarea(sprintf('item_meta[%d]', $item->id), array('rows' => 4)); echo "\n"; ?></div>
<?php 			endif; # has_meta ?>
			</div>
<?php 		endforeach; ?>
<?php 	else: ?>
<p><?php _e('There are no items for this event&hellip;', 'nsevent'); ?></p>
<?php 	endif; ?>

	    <input type="submit" class="button-primary" value="<?php _e('Add Registrations', 'nsevent'); ?>" />
    </form>
</div>
 