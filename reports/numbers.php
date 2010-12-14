<?php

# Dancers
$lists['Dancers']['Total']   = NSEvent_Dancer::count();
$lists['Dancers']['Leads']   = NSEvent_Dancer::count('position', 1);
$lists['Dancers']['Follows'] = NSEvent_Dancer::count('position', 2);
$lists['Dancers']['Ratio']   = @round($lists['Dancers']['Follows'] / $lists['Dancers']['Leads'], 2);

# Levels
if ($event->levels())
{
	foreach ($event->levels() as $index => $level)
	{
		$lists['Levels (Total)'][$level] = self::$database->query('SELECT COUNT(id) FROM %1$s_dancers WHERE event_id = :event_id AND level = :level', array(':event_id' => $event->id, ':level' => $index))->fetchColumn();
		// TODO: Only show when there are items with meta of `count_classes`
		$lists['Levels (Class Balance)'][$level] = sprintf('%d leads, %d follows',
			self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers ON %1$s_registrations.`dancer_id` = %1$s_dancers.`id` LEFT JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_dancers.`level` = :level AND %1$s_dancers.`position` = :position AND %1$s_items.`has_meta` = "count_for_classes"', array(':event_id' => $event->id, ':level' => $index, ':position' => 1))->fetchColumn(),
			self::$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers ON %1$s_registrations.`dancer_id` = %1$s_dancers.`id` LEFT JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_dancers.`level` = :level AND %1$s_dancers.`position` = :position AND %1$s_items.`has_meta` = "count_for_classes"', array(':event_id' => $event->id, ':level' => $index, ':position' => 2))->fetchColumn());
	}
	
	$lists['Levels (Total)'] = array_filter($lists['Levels (Total)']);
}

# Packages
$packages = NSEvent_Item::find_by(array(':preregistration' => 1, ':type' => 'package'));
foreach ($packages as $item)
	$lists['Packages'][$item->name] = NSEvent_Registration::count_for_item($item->id);
$lists['Packages'] = array_filter($lists['Packages']);

# Shirts
$shirts = NSEvent_Item::find_by(array(':preregistration' => 1, ':type' => 'shirt'));
foreach ($shirts as $item)
{
	$header_key = sprintf('%s (%d)', $item->name, NSEvent_Registration::count_for_item($item->id));
	
	foreach (explode(',', $item->description) as $size)
		$lists[$header_key][ucfirst($size)] = NSEvent_Registration::count_for_item($item->id, $size);
	
	$lists[$header_key] = array_filter($lists[$header_key]);
}

?>

<div class="wrap" id="nsevent">
	<h2><?php $event->request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->name)); ?></h2>

	<h3><?php _e('Attendance&nbsp;/&nbsp;Numbers', 'nsevent'); ?></h3>
	
<?php foreach ($lists as $list_key => $list): ?>
	<h4><?php echo esc_html(__($list_key, 'nsevent')); ?></h4>
	<ul>
<?php 	foreach ($list as $key => $value): ?>
		<li><?php echo esc_html("$key: $value"); ?></li>
<?php 	endforeach; ?>
	</ul>
<?php endforeach; ?>
</div>
