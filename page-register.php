<?php
if (!class_exists('RegistrationSystem')) {
	@header('HTTP/1.1 500 Internal Server Error');
	exit('Registration System plugin is not active.');
}
RegistrationSystem::registration_head();
RegistrationSystem::registration_form();
?>