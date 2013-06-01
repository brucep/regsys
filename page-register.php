<?php

if (class_exists('RegSys')) {
	RegSys::registrationHead();
	RegSys::registrationForm();
}
else {
	@header('HTTP/1.0 500 Internal Server Error');
	exit('RegSys plugin is not active.');
}
