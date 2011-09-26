<?php

require dirname(dirname(__FILE__)).'/includes/form-validation.php';
NSEvent_FormValidation::set_error_messages();

NSEvent::$event = $event; // Needed for validation
$items = $event->get_items();

if (!empty($_POST)):
	NSEvent_FormValidation::add_rule('items', 'NSEvent::validate_items');
	
	if (NSEvent_FormValidation::validate()):
		$additional_owed = 0;
		
		foreach (NSEvent::$validated_items as $item) {
			$price = $dancer->is_vip() ? $item->get_price_for_vip() : $item->get_price_for_prereg($dancer->get_payment_discount());
			$additional_owed += $price;
			
			$event->add_registration(array(
				'dancer_id' => $dancer->get_id(),
				'item_id'   => $item->get_id(),
				'price'     => $price,
				'item_meta' => (!isset($_POST['item_meta'][$item->get_id()]) ? '' : $_POST['item_meta'][$item->get_id()]),
				));
		}
		
		$dancer->update_payment_confirmation(false, $dancer->get_payment_owed() + $additional_owed);
		
?>
<div class="wrap" id="nsevent">
    <h2><?php _e('Add Registrations for Dancer', 'nsevent'); ?></h2>

	<p><?php _e('Registrations added:', 'nsevent'); ?></p>

	<ul>
<?php foreach (NSEvent::$validated_items as $item): ?>
		<li><?php echo esc_html($item->get_name()); ?></li>
<?php endforeach; ?>
	</ul>

	<p><strong><a href="<?php echo NSEvent::paypal_href($dancer, $dancer->get_registered_items(array_keys(NSEvent::$validated_items)), $options, false); ?>"><?php _e('PayPal Link', 'nsevent'); ?></a><strong></p>

    <p><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->get_id(); ?>&amp;request=dancer&amp;dancer=<?php echo $dancer->get_id(); ?>"><?php printf(__('Back to "%s"', 'nsevent'), $dancer->get_name()); ?></a></p>
</div>
<?php
	endif; # form validation
	exit;
endif;

?>
<div class="wrap" id="nsevent">
	<h2><?php _e('Add Registrations for Dancer', 'nsevent'); ?></h2>

	<form id="registration-add" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=nsevent&amp;event_id=<?php echo $event->get_id(); ?>&amp;request=registration-add&amp;dancer=<?php echo (int) $_GET['dancer']; ?>" method="post">
		<p><?php echo __('Dancer:', 'nsevent'), ' ', $dancer->get_name(); ?></p>
		<?php NSEvent_FormInput::hidden('position', array('value' => $dancer->get_position())); // Needed when $item->get_meta() === 'position' ?>

<?php 	if ($items): ?>
<?php 		foreach ($items as $item): if ($item->is_expired()) { continue; } ?>
			<?php NSEvent_FormValidation::get_error('item_'.$item->get_id()); echo "\n"; ?>
			<div class="field">
<?php if ($item->get_meta() != 'size'): ?>
				<?php NSEvent_FormInput::checkbox(sprintf('items[%d]', $item->get_id()), array('label' => $item->get_name(), 'disabled' => ((bool) $dancer->get_registered_items($item->get_id()) or !$item->count_openings()), 'checked' => (bool) $dancer->get_registered_items($item->get_id()))); echo "\n"; ?>
<?php else: ?>
				<?php echo esc_html($item->get_name()), "\n"; ?>
<?php endif; ?>

<?php  			if ($item->get_meta() == 'position'): ?>
				<div class="meta"><?php _e('Register as:', 'nsevent'); ?>&nbsp;<?php NSEvent_FormInput::radio(sprintf('item_meta[%d]', $item->get_id()), array('value' => 'lead', 'label' => __('Lead', 'nsevent'), 'disabled' => !$item->count_openings('lead'))); ?>&nbsp;<?php NSEvent_FormInput::radio(sprintf('item_meta[%d]', $item->get_id()), array('value' => 'follow', 'label' => __('Follow', 'nsevent'), 'disabled' => !$item->count_openings('follow'))); ?></div>
<?php 			elseif ($item->get_meta() == 'partner_name'): ?>
				<div class="meta"><?php NSEvent_FormInput::text(sprintf('item_meta[%d]', $item->get_id()), array('label' => 'Partner:&nbsp;', 'placeholder' => __('Partner\'s name', 'nsevent'))); ?></div>
<?php 			elseif ($item->get_meta() == 'team_members'): ?>
				<div class="meta"><?php echo esc_html($item->get_description()); ?><br />
				<?php NSEvent_FormInput::textarea(sprintf('item_meta[%d]', $item->get_id()), array('rows' => 4)); echo "\n"; ?></div>
<?php 			elseif ($item->get_meta() == 'size'): ?>
				<div class="meta">
					<select name="<?php printf('items[%d]', $item->get_id()); ?>">
						<option value="none"<?php echo NSEvent_FormInput::_set_select(sprintf('items[%d]', $item->get_id()), 'none', true); ?>><?php _e('None', 'nsevent'); ?></option>
						<optgroup label="<?php _e('Shirt Size', 'nsevent'); ?>">
<?php 		foreach (explode(',', $item->get_description()) as $size): ?>
							<option value="<?php echo esc_attr($size); ?>"<?php echo NSEvent_FormInput::_set_select(sprintf('items[%d]', $item->get_id()), esc_attr($size)); ?>><?php echo esc_html(ucfirst($size)); ?></option>
<?php 		endforeach; ?>
						</optgroup>
					</select>
				</div>
<?php 			endif; # meta ?>
			</div>
<?php 		endforeach; ?>
<?php 	else: ?>
<p><?php _e('There are no items for this event&hellip;', 'nsevent'); ?></p>
<?php 	endif; ?>

	    <input type="submit" class="button-primary" value="<?php _e('Add Registrations', 'nsevent'); ?>" />
    </form>
</div>
 