<?php

namespace RegSys\Controller;

class FrontEndController extends \RegSys\Controller
{	
	public function registrationForm()
	{
		$event = \RegSys\Entity\Event::eventByID($this->options['currentEventID']);
		
		if (!$event) {
			throw new \Exception('Event for registration form not found. Check currentEventID in options.');
		}
		
		# Display page content when registration is not available.
		if ((time() > $event->datePayPal() and time() > $event->dateMail()) or ($this->options['registrationTesting'] and !$this->container['isTester'])) {
			return array('FormPageContent.html', array('event' => $event, 'wordpressContent' => $this->container['wordpressContent']));
		}
		
		# Validate input
		if (!empty($_POST)) {
			unset($_POST['housingType'], $_POST['note']); # Don't allow values to be set via POST
			$dancerData = $_POST;
			
			if ($this->options['registrationTesting']) {
				$dancerData['note'] = 'TEST';
			}
			
			if (isset($dancerData['housingTypeProvider'])) {
				$dancerData = array_merge($dancerData, $dancerData['housingProvider']);
				$dancerData['housingType'] = 1;
			}
			elseif (isset($dancerData['housingTypeNeeded'])) {
				$dancerData = array_merge($dancerData, $dancerData['housingNeeded']);
				$dancerData['housingType'] = 0;
			}
			
			$dancer = new \RegSys\Entity\Dancer($dancerData);
			unset($dancerData);
			
			$validationErrors = array_merge(
				$dancer->validate($event),
				$dancer->validateHousing($event),
				$dancer->validatePackage(
					$event,
					isset($_POST['package']) ? $_POST['package'] : 0,
					isset($_POST['packageTier']) ? $_POST['packageTier'] : null),
				$dancer->validateItems(
					$event,
					isset($_POST['items']) ? $_POST['items'] : array(),
					isset($_POST['itemMeta']) ? $_POST['itemMeta'] : array()));
			
			if ($validationErrors) {
				$this->viewHelper->setErrors($validationErrors);
			}
		}
		else {
			$dancer = new \RegSys\Entity\Dancer();
		}
		
		# Determine appropriate file for current step
		if (empty($_POST) or !empty($validationErrors)) {
			$view = 'FormRegister.html';
			
			$context = array(
				'dancer' => $dancer,
				'packages'     => $event->itemsForRegistrationByType('package'),
				'competitions' => $event->itemsForRegistrationByType('competition'),
				'shirts'       => $event->itemsForRegistrationByType('shirt'),
				'shirtDescription' => $this->container['shirtDescription'],
				'wordpressContent' => $this->container['wordpressContent'],
				'permalink' => $this->container['permalink'],	
				);
			
			# Only used for testing
			if (!empty($validationErrors) and $this->container['debug']) {
				$context['validationErrors'] = $validationErrors;
			}
		}
		elseif (!isset($_POST['confirmed'])) {
			$view = 'FormConfirm.html';
			
			$context = array(
				'dancer' => $dancer,
				'discountAmount' => $dancer->discountCode() ? $event->discountByCode($dancer->discountCode())->discountAmount : null,
				'permalink' => $this->container['permalink'],
				);
		}
		else {
			$dancer->add($event->id());
			
			if ($dancer->needsHousing() or $dancer->isHousingProvider()) {
				$dancer->addHousing();
			}
			
			foreach ($dancer->registeredItems() as $item) {
				$item->addRegistration($dancer->id());
			}
									
			# Confirmation email
			if (!$this->options['registrationTesting'] or ($this->options['registrationTesting'] and $this->options['emailTesting'])) {
				try {
					if (!$dancer->sendConfirmationEmail($event, $this->loadTwig(), $this->container['notifyUrl'])) {
						throw new \Exception(sprintf('Email could not be sent to %s.', $dancer->email()));
					}
				}
				catch (\Exception $e) {
					# $e is included in $context below
				}
			}
			
			$view = 'FormAccepted.html';
			
			$context = array(
				'dancer' => $dancer,
				'confirmationEmailFailed' => isset($e) ? $e : null,
				'notifyUrl' => $this->container['notifyUrl'],
				);
		}
		
		$context['event'] = $event;
		return array($view, $context);
	}
	
	public function render($file, array $context)
	{
		$this->loadTwig();
		
		if ($file == 'FormRegister.html') {
			# Only required for registration form. Will throw an exception on accepted page due to Twig alreayd being loaded for confirmation email.
			$this->twig->addFunction(new \Twig_SimpleFunction('setThing', array($this->viewHelper, 'setThing')));
		}
		
		return parent::render($file, $context);
	}
}
