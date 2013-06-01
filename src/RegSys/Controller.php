<?php

namespace RegSys;

abstract class Controller
{
	protected $container, $db, $debug, $options, $twig, $viewHelper;
	
	public function __construct(\Pimple $container)
	{
		$this->db = $container['db'];
		$this->options = $container['options'];
		$this->viewHelper = new \RegSys\ViewHelper();
		
		\RegSys\Entity::setDatabase($container['db']);
		\RegSys\Entity::setOptions($container['options']);
		
		unset($container['db'], $container['options']);
		$this->container = $container;
	}
	
	protected function loadTwig()
	{
		if (!isset($this->twig)) {
			$this->twig = new \Twig_Environment(
				new \Twig_Loader_Filesystem($this->container['viewsPath']),
				array('debug' => $this->container['debug'], 'strict_variables' => false));
			
			$this->twig->addExtension(new \Twig_Extension_Debug());
			$this->twig->addFunction(new \Twig_SimpleFunction('pluralize', function($single, $plural, $number) { return sprintf($number == 1 ? $single : $plural, $number); }));
			$this->twig->addFunction(new \Twig_SimpleFunction('getError', array($this->viewHelper, 'getError')));
			$this->twig->addFunction(new \Twig_SimpleFunction('hasErrors', array($this->viewHelper, 'hasErrors')));
			$this->twig->addFunction(new \Twig_SimpleFunction('getThingValue', array($this->viewHelper, 'getThingValue')));
		}
		
		return $this->twig;
	}
	
	protected function render($file, array $context = array())
	{
		$context['GET'] = $_GET;
		$context['POST'] = $_POST;
		$context['options'] = $this->options;
		
		return $this->loadTwig()->loadTemplate($file)->render($context);
	}
}
