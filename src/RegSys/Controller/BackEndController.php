<?php

namespace RegSys\Controller;

abstract class BackEndController extends \RegSys\Controller
{
	protected $event, $requestHref;
	
	public function __construct(\Pimple $container)
	{
		parent::__construct($container);
		
		if (isset($container['eventID']) and $container['eventID'] != null) {
			$this->event = \RegSys\Entity\Event::eventByID($container['eventID']);
			
			if (!$this->event) {
				throw new \Exception('Event ID not found: ' . $container['eventID']);
			}
		}
		
		if (isset($container['requestHref'])) {
			if ($this->event) {
				$this->requestHref = $container['requestHref'] . '&eventID=' . $container['eventID'] . '&request=';
			}
			else {
				$this->requestHref = $container['requestHref'] . '&request=';
			}
		}
		
		unset($this->container['eventID'], $this->container['requestHref']);
	}
	
	public function render(array $context, $view = null)
	{
		$context['event'] = $this->event;
		$context['admin']   = isset($this->container['isAdmin']) ? $this->container['isAdmin'] : null;
		$context['request'] = isset($this->container['request']) ? $this->container['request'] : null;
		$context['requestHref'] = isset($this->requestHref) ? $this->requestHref : null;
		
		$this->loadTwig();
		$this->twig->getExtension('core')->setDateFormat('Y-m-d, h:i A');
		
		if ($view == null) {
			$reflection = new \ReflectionClass(get_class($this));
			$view = $reflection->getShortName() . '.html';
		}
		
		return parent::render($view, $context);
	}
	
	protected function getRequestedDancer()
	{
		$dancer = $this->event->dancerByID($_GET['dancerID']);
		
		if (!$dancer) {
			throw new \Exception('Dancer ID not found: ' . $_GET['dancerID']);
		}
		
		return $dancer;
	}
	
	protected function getRequestedItem()
	{
		$item = $this->event->itemByID($_GET['itemID']);
		
		if (!$item) {
			throw new \Exception('Item ID not found: ' . $_GET['itemID']);
		}
		
		return $item;
	}
}
