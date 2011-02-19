<?php
/*
Template Name: NSEvent Registration Form
*/

if (!class_exists('NSEvent'))
{
	@header('HTTP/1.1 500 Internal Server Error');
	exit(__('NSEvent plugin is not active.', 'nsevent'));
}

NSEvent::registration_head();
if (have_posts()) { the_post(); }
?>
<?php NSEvent::registration_form(); ?>
