<?php

# Dancers
$lists['Dancers']['Total']   = $event->count_dancers();
$lists['Dancers']['Leads']   = $event->count_dancers(array(':position' => 1));
$lists['Dancers']['Follows'] = $event->count_dancers(array(':position' => 2));
$lists['Dancers']['Ratio']   = @round($lists['Dancers']['Follows'] / $lists['Dancers']['Leads'], 2);

# Levels
if ($event->has_levels())
{
	$database = self::get_database_connection();
	
	foreach ($event->get_levels() as $index => $level)
	{
		$lists['Levels (Total)'][$level] = $event->count_dancers(array(':level' => $index));
		
		$lists['Levels (Class Balance)'][$level] = sprintf('%d leads, %d follows',
			$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers ON %1$s_registrations.`dancer_id` = %1$s_dancers.`id` LEFT JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_dancers.`level` = :level AND %1$s_dancers.`position` = :position AND %1$s_items.`meta` = "count_for_classes"', array(':event_id' => $event->get_id(), ':level' => $index, ':position' => 1))->fetchColumn(),
			$database->query('SELECT COUNT(dancer_id) FROM %1$s_registrations LEFT JOIN %1$s_dancers ON %1$s_registrations.`dancer_id` = %1$s_dancers.`id` LEFT JOIN %1$s_items ON %1$s_registrations.`item_id` = %1$s_items.`id` WHERE %1$s_registrations.`event_id` = :event_id AND %1$s_dancers.`level` = :level AND %1$s_dancers.`position` = :position AND %1$s_items.`meta` = "count_for_classes"', array(':event_id' => $event->get_id(), ':level' => $index, ':position' => 2))->fetchColumn());
	}
	
	$lists['Levels (Total)'] = array_filter($lists['Levels (Total)']);
	unset($database);
}

# Packages
$lists['Packages'] = array();
$packages = $event->get_items_where(array(':preregistration' => 1, ':type' => 'package'));
foreach ($packages as $item)
{
	$lists['Packages'][$item->get_name()] = $event->count_registrations_where(array(':item_id' => $item->get_id()));
}
$lists['Packages'] = array_filter($lists['Packages']);

# Shirts
$shirts = $event->get_items_where(array(':preregistration' => 1, ':type' => 'shirt'));
foreach ($shirts as $item)
{
	$header_key = sprintf('%s (%d)', $item->get_name(), $event->count_registrations_where(array(':item_id' => $item->get_id())));
	
	foreach (explode(',', $item->get_description()) as $size)
		$lists[$header_key][ucfirst($size)] = $event->count_registrations_where(array(':item_id' => $item->get_id(), ':item_meta' => $size));
	
	$lists[$header_key] = array_filter($lists[$header_key]);
}

?>

<div class="wrap" id="nsevent">
	<h2><?php echo $event->get_request_link('index-event', sprintf(__('Reports for %s', 'nsevent'), $event->get_name())); ?></h2>

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
