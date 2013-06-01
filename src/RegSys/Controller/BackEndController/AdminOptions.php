<?php

namespace RegSys\Controller\BackEndController;

class AdminOptions extends \RegSys\Controller\BackEndController
{
	public function getContext()
	{
		$events = array();
		$result = $this->db->fetchAll('SELECT eventID, name FROM regsys__events ORDER BY datePayPal DESC');
		
		foreach ($result as $event) {
			$events[] = array('label' => $event->name, 'value' => $event->eventID);
		}
		
		return array('events' => $events);
	}
	
	public function render(array $context)
	{
		$this->loadTwig();
		
		if (function_exists('settings_fields')) {
			# Required for WordPress
			$this->twig->addFunction('settings_fields', new \Twig_Function_Function('settings_fields', array('is_safe' => array('html'))));
		}
		else {
			# Testing w/o WordPress
			$this->twig->addFunction(new \Twig_SimpleFunction('settings_fields', function() { return ''; }));
		}
		
		$this->viewHelper->setThing(array('regsys' => $this->options));
		
		return parent::render($context);
	}
}
