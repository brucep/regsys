<?php
/*
Template Name: NSEvent Registration Form
*/

if (!isset($nsevent_plugin))
{
	@header('HTTP/1.1 500 Internal Server Error');
	exit(__('NSEvent plugin is not active.', 'nsevent'));
}

$nsevent_plugin->registration();
