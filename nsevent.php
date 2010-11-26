<?php
/*
Plugin Name: NSEvent
Plugin URI: http://github.com/brucep/nsevent
Description: An event registration and reporting system for dance organizations.
Version: 1.0
Author: Bruce Phillips
License: X11
Note: Requires PHP 5.2.3 or later.
*/
/*
Copyright (C) 2010 Bruce Phillips

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of the author not be used in advertising or otherwise to promote the sale, use or other dealings in this Software without prior written authorization from the author.
*/

if (!class_exists('NSEvent')):
class NSEvent
{
	static public $event, $vip, $validated_package_id = 0, $validated_items;
	static private $database;
	static private $default_options = array(
		'current_event_id'           => '',
		'registration_testing'       => False,
		'paypal_business'            => '',
		'paypal_fee'                 => 0,
		'paypal_sandbox'             => False,
		'confirmation_email_address' => '',
		'confirmation_email_bcc'     => '',
		);
	
	private function __clone() {}
	private function __construct() {}
	
	static private function database_connect()
	{
		global $wpdb;
		
		if (!isset(self::$database))
		{
			require dirname(__FILE__).'/includes/database.php';
			self::$database = NSEvent_Database::get_instance();
			self::$database->connect();
			self::$database->prefix = $wpdb->prefix.'nsevent';
		}
	}
	
	static public function admin_init()
	{
		load_plugin_textdomain('nsevent', False, basename(__FILE__, '.php').'/translations');
		register_setting('nsevent', 'nsevent', 'NSEvent::admin_validate_options');
	}
	
	static public function admin_menu()
	{
		$hookname = add_menu_page('Events', 'Events', 'edit_pages', 'nsevent', 'NSEvent::page_request');
		add_submenu_page('nsevent', 'NSEvent Options', 'Options', 'administrator', 'nsevent-options', 'NSEvent::page_options');
		add_action('admin_print_styles-'.$hookname, 'NSEvent::admin_print_styles');
	}

	static public function admin_print_styles()
	{
	    if (!isset($_GET['style']) or !in_array($_GET['style'], array('iPhone', 'print'))) {
    		wp_enqueue_style('nsevent-admin',        sprintf('%s/%s/css/admin-screen.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), False, False, 'screen and (min-device-width: 481px)');
    		wp_enqueue_style('nsevent-admin-iphone', sprintf('%s/%s/css/admin-iphone.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), False, False, 'only screen and (max-device-width: 480px)');
		}
        elseif ($_GET['style'] === 'iPhone') {
            wp_enqueue_style('nsevent-admin-iphone', sprintf('%s/%s/css/admin-iphone.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), False, False, 'screen');
		}
		elseif ($_GET['style'] === 'print') {
			wp_enqueue_style('nsevent-admin-iphone', sprintf('%s/%s/css/admin-print.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), False, False, 'screen');
		}
			
        
		wp_enqueue_style('nsevent-admin-print',      sprintf('%s/%s/css/admin-print.css',  WP_PLUGIN_URL, basename(__FILE__, '.php')), False, False, 'print');
		wp_enqueue_style('nsevent-admin-despise-ie', sprintf('%s/%s/css/admin-screen.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), False, False, 'screen');
		# http://iamzed.com/2010/01/07/using-wordpress-wp_enqueue_style-with-conditionals/
		global $wp_styles;
		$wp_styles->add_data('nsevent-admin-despise-ie', 'conditional', 'lte IE 8');
		
		wp_enqueue_script('nsevent-tablesorter',      sprintf('%s/%s/js/jquery.tablesorter.min.js',  WP_PLUGIN_URL, basename(__FILE__, '.php')), array('jquery'));
		wp_enqueue_script('nsevent-tablesorter-init', sprintf('%s/%s/js/tablesorter-init.js',        WP_PLUGIN_URL, basename(__FILE__, '.php')), array('nsevent-tablesorter'));
		echo '<meta name="viewport" content="initial-scale=1.0;" />';
	}
		
	static public function admin_validate_options($input)
	{
		$options = get_option('nsevent', array());
		
		if (isset($input['current_event_id'])) // TODO: Check if id exists
			$options['current_event_id'] = (int) $input['current_event_id'];
		
		if (isset($input['paypal_business']))
			$options['paypal_business'] = trim($input['paypal_business']);
		
		if (isset($input['paypal_fee']))
			$options['paypal_fee'] = (int) $input['paypal_fee'];
		
		if (isset($input['confirmation_email_address']))
			$options['confirmation_email_address'] = trim($input['confirmation_email_address']);
		else
			$options['confirmation_email_address'] = get_option('admin_email');
		
		if (isset($input['confirmation_email_bcc']))
			$options['confirmation_email_bcc'] = trim($input['confirmation_email_bcc']);
		else
			$options['confirmation_email_bcc'] = '';
		
		$options['registration_testing']   = isset($input['registration_testing']);
		
		return $options;
	}
	
	static public function page_options()
	{
		if (!current_user_can('administrator'))
			return;
		
		self::database_connect();
		
		$events = NSEvent_Event::find_all();
		$options = get_option('nsevent', array());
		
		require dirname(__FILE__).'/admin/options.php';
	}
	
	static public function page_request()
	{
		global $wpdb;
		
		try
		{
			if (!current_user_can('edit_pages'))
			{
				throw new Exception(__('Cheatin&#8217; uh?'));
			}
			
			if (empty($_GET['request']))
			{
				$_GET['request'] = 'index';
			}
			
			self::database_connect();
			
			switch ($_GET['request'])
			{
				# List of events
				case 'index':
					$file = 'reports/index.php';
					break;
				
				# Admin forms
				case 'dancer-delete':
				case 'dancer-edit':
				case 'housing-delete':
				case 'registration-add':
					if (empty($_GET['dancer']))
					{
						throw new Exception(__('Dancer ID not specified.', 'nsevent'));
					}
					if (!$dancer = NSEvent_Dancer::find($_GET['dancer']))
					{
						throw new Exception(sprintf(__('Dancer ID not found: %d', 'nsevent'), $_GET['parameter']));
					}
				case 'event-edit':
					if (!current_user_can('administrator'))
					{
						throw new Exception(__('Cheatin&#8217; uh?'));
					}
					if (empty($_GET['event_id']))
					{
						throw new Exception(__('Event ID not specified.', 'nsevent'));
					}
				    if ($_GET['event_id'] !== 'add' and (!$event = NSEvent_Model::$event = NSEvent_Event::find($_GET['event_id'])))
					{
						throw new Exception(sprintf(__('Event ID not found: %d', 'nsevent'), $_GET['event_id']));
					}
					require dirname(__FILE__).'/includes/form-input.php';
					$file = sprintf('admin/%s.php', $_GET['request']);
					break;
				
				# Reports
				case 'housing-needed':
				case 'housing-providers':
					$file = 'reports/housing.php';
				case 'index-event':
				case 'competitions':
				case 'dancer':
				case 'dancers':
				case 'money':
				case 'numbers':
				case 'packet-printout':
				case 'reg-list':
				case 'volunteers':
					if (!$event = NSEvent_Event::find($_GET['event_id']))
						throw new Exception(sprintf(__('Event ID not found: %d', 'nsevent'), $_GET['event_id']));
					NSEvent_Model::$event = $event;
					if (!isset($file))
						$file = sprintf('reports/%s.php', $_GET['request']);
					break;
				
				default:
					throw new Exception(sprintf(__('Unable to handle page request: %s', 'nsevent'), esc_html($_GET['request'])));
			}
			
			require dirname(__FILE__)."/$file";
		}
		catch (Exception $e)
		{
			printf('<div class="nsevent-exception">%s</div>', $e->getMessage());
		}
	}
	
	static public function plugin_activate()
	{
		global $wpdb;
		
		# Include `dbDelta` function
		require_once ABSPATH.'wp-admin/includes/upgrade.php';
		
		$tables = array(
			'events',
			'items',
			'dancers',
			'registrations',
			'housing_needed',
			'housing_providers');
		
		foreach($tables as $table)
		{
			$table_name = sprintf('%snsevent_%s', $wpdb->prefix, $table);
			
			# Create new database tables
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
			{
				switch ($table):
					case 'events':
						$query = sprintf("CREATE TABLE `%s` (
							`id`                 int(10) unsigned NOT NULL auto_increment,
							`name`               varchar(255) NOT NULL,
							`early_end`          int(10) unsigned NOT NULL default '0',
							`prereg_end`         int(10) unsigned NOT NULL default '0',
							`refund_end`         int(10) unsigned NOT NULL default '0',
							`discount1`          varchar(255) NOT NULL,
							`discount2`          varchar(255) NOT NULL,
							`discount_label`     varchar(255) NOT NULL,
							`discount_note`      varchar(255) NOT NULL,
							`has_vip`            tinyint(1) unsigned NOT NULL default '0',
							`has_volunteers`     tinyint(1) unsigned NOT NULL default '0',
							`has_housing`        tinyint(1) unsigned NOT NULL default '0',
							`nights`             tinyint(2) unsigned NOT NULL default '1',
							`limit_per_position` smallint(5) unsigned NOT NULL default '0',
							`levels`             varchar(255) NOT NULL,
							`shirt_description`  text NOT NULL,
							PRIMARY KEY  (`id`)
							);", $table_name);
						break;
				
					case 'items':
						$query = sprintf("CREATE TABLE `%s` (
							`event_id`               int(10) unsigned NOT NULL,
							`id`                     int(10) unsigned NOT NULL auto_increment,
							`name`                   varchar(200) NOT NULL,
							`type`                   varchar(11) NOT NULL,
							`preregistration`        tinyint(1) unsigned NOT NULL default '1',
							`price_early`            tinyint(3) unsigned NOT NULL default '0',
							`price_early_discount1`  tinyint(3) unsigned NOT NULL default '0',
							`price_early_discount2`  tinyint(3) unsigned NOT NULL default '0',
							`price_prereg`           tinyint(3) unsigned NOT NULL default '0',
							`price_prereg_discount1` tinyint(3) unsigned NOT NULL default '0',
							`price_prereg_discount2` tinyint(3) unsigned NOT NULL default '0',
							`price_door`             tinyint(3) unsigned NOT NULL default '0',
							`price_door_discount1`   tinyint(3) unsigned NOT NULL default '0',
							`price_door_discount2`   tinyint(3) unsigned NOT NULL default '0',
							`price_vip`              tinyint(3) unsigned NOT NULL default '0',
							`limit_total`            smallint(5) unsigned NOT NULL default '0',
							`limit_per_position`     smallint(5) unsigned NOT NULL default '0',
							`expiration_date`        int(10) unsigned NOT NULL default '0',
							`has_meta`               varchar(20) NOT NULL,
							`description`            varchar(255) NOT NULL,
							`note`                   varchar(255) NOT NULL,
							PRIMARY KEY  (`id`)
							);", $table_name);
						break;
					
					case 'dancers':
						$query = sprintf("CREATE TABLE `%s` (
							`event_id`          int(10) unsigned NOT NULL,
							`id`                int(10) unsigned NOT NULL auto_increment,
							`first_name`        varchar(100) NOT NULL,
							`last_name`         varchar(100) NOT NULL,
							`email`             varchar(100) NOT NULL,
							`position`          tinyint(1) NOT NULL,
							`level`             tinyint(1) unsigned NOT NULL default '1',
							`status`            tinyint(1) unsigned NOT NULL default '0',
							`date_registered`   int(10) unsigned NOT NULL default '0',
							`payment_method`    varchar(6) NOT NULL,
							`payment_discount`  varchar(3) NOT NULL default '0',
							`payment_confirmed` tinyint(1) unsigned NOT NULL default '0',
							`amount_owed`       smallint(5) unsigned NOT NULL default '0',
							`note`              varchar(255) NOT NULL,
							PRIMARY KEY  (`id`)
							);", $table_name);
						break;
					
					case 'registrations':
						$query = sprintf("CREATE TABLE `%s` (
							`event_id`  int(10) unsigned NOT NULL,
							`dancer_id` int(10) unsigned NOT NULL,
							`item_id`   int(10) unsigned NOT NULL,
							`price`     tinyint(3) unsigned NOT NULL,
							`item_meta` text NOT NULL,
							PRIMARY KEY  (`dancer_id`,`item_id`)
							);", $table_name);
						break;
					
					case 'housing_needed':
						$query = sprintf("CREATE TABLE `%s` (
							`event_id`   int(10) unsigned NOT NULL,
							`dancer_id`  int(10) unsigned NOT NULL,
							`car`        tinyint(1) unsigned NOT NULL default '0',
							`no_smoking` tinyint(1) unsigned NOT NULL default '0',
							`no_pets`    tinyint(1) unsigned NOT NULL default '0',
							`gender`     tinyint(1) unsigned NOT NULL default '3',
							`nights`     tinyint(2) unsigned NOT NULL default '1',
							`comment`    text NOT NULL,
							PRIMARY KEY  (`dancer_id`)
							);", $table_name);
						break;
					
					case 'housing_providers':
						$query = sprintf("CREATE TABLE `%s` (
							`event_id`  int(10) unsigned NOT NULL,
							`dancer_id` int(10) unsigned NOT NULL,
							`available` tinyint(2) unsigned NOT NULL default '0',
							`smoking`   tinyint(1) unsigned NOT NULL default '0',
							`pets`      tinyint(1) unsigned NOT NULL default '0',
							`gender`    tinyint(1) unsigned NOT NULL default '3',
							`nights`    tinyint(2) unsigned NOT NULL default '1',
							`comment`   text NOT NULL,
							PRIMARY KEY  (`dancer_id`)
							);", $table_name);
						break;
				endswitch;
				
				dbDelta($query);
			}
			
			self::$default_options['confirmation_email_address'] = get_option('admin_email');
			add_option('nsevent', self::$default_options, '', 'no');
		}
			
	}
	
	static public function registration_form()
	{
		global $post;
		
		try
		{
			# Define a constant for themes to use
			define('NSEVENT_REGISTRATION_FORM', True);
			
			# Stop the `WP Super Cache` plugin from caching registration pages
			define('DONOTCACHEPAGE', True);
			
			$options = get_option('nsevent');
			$options = array_merge(self::$default_options, $options); # Make sure keys exists
			
			require dirname(__FILE__).'/includes/form-input.php';
			require dirname(__FILE__).'/includes/form-validation.php';
			NSEvent_FormValidation::set_error_messages();
			self::database_connect();
			
			# Find current event
			$event = self::$event = NSEvent_Event::find($options['current_event_id']);
			if (!$event)
				throw new Exception(sprintf(__('Event ID not found: %d', 'nsevent'), $options['current_event_id']));
			NSEvent_Model::$event = $event;
			
			$vip = self::$vip = ($event->has_vip and (isset($_GET['vip']) or isset($_POST['vip'])));
			$early_bird = ($event->early_end and time() < $event->early_end);
			$early_bird_class = $early_bird ? 'early-bird' : 'not-early-bird';
			
			
			// TODO: How to handle dates? Specify times as a date and assume end of that day, or specify specific time in field?
			// Idea: Specify specific time, and if (during event edit) time is 00:00:00, then automatically change to 11:59:59?
			
			# Don't allow registration for certain conditions
			if (time() > $event->prereg_end and !$vip) {
				require dirname(__FILE__).'/registration/at-the-door.php';
				return;
			}
			elseif ($options['registration_testing'] and !current_user_can('edit_pages'))
				throw new Exception(__('Preregistration is currently unavailable; Check back soon&hellip;', 'nsevent'));
			
			
			# Setup validation rules
			if (!empty($_POST)) {
				NSEvent_FormValidation::add_rules(array(
					'first_name'      => 'trim|required|max_length[100]|ucfirst',
					'last_name'       => 'trim|required|max_length[100]|ucfirst',
					'email'           => 'trim|valid_email|max_length[100]',
					'position'        => 'intval|in[1,2]|NSEvent::validate_position',
					'status'          => 'NSEvent::validate_status',
					'volunteer_phone' => 'if[status]|trim|NSEvent::validate_volunteer_phone',
					'package'         => 'intval|NSEvent::validate_package',
					'items'           => 'NSEvent::validate_items',
					'payment_method'  => 'in[Mail,PayPal]',
					));
				
				# Level
				if ($event->levels()) {
					NSEvent_FormValidation::add_rule('level', sprintf('intval|in[%s]',
						implode(',', array_keys($event->levels()))));
				}
				else {
					$_POST['level'] = 1;
				}
				
				# Discount
				if ($event->has_vip and $vip === True) {
					$_POST['discount'] = 'vip';
				}
				elseif ($event->discount1 or $event->discount2) {
					NSEvent_FormValidation::add_rule('discount', sprintf('intval|in[0%s%s]',
						$event->discount1 ? ',1' : '',
						$event->discount2 ? ',2' : ''));
				}
				else {
					$_POST['discount'] = 0;
				}
				
				# Housing
				if ($event->has_housing)
				{
					NSEvent_FormValidation::add_rules(array(
						'housing_provider_available' => 'if[housing_provider]|intval|greater_than[0]',
						'housing_provider_smoking'   => 'if[housing_provider]|intval|in[0,1]',
						'housing_provider_pets'      => 'if[housing_provider]|intval|in[0,1]',
						'housing_provider_gender'    => 'if[housing_provider]|intval|in[1,2,3]',
						'housing_provider_nights'    => 'if[housing_provider]|NSEvent::validate_housing_nights',
						'housing_provider_comment'   => 'if[housing_provider]|trim|max_length[65536]',
						'housing_needed_car'         => 'if[housing_needed]|intval|in[0,1]',
						'housing_needed_no_smoking'  => 'if[housing_needed]|intval|in[0,1]',
						'housing_needed_no_pets'     => 'if[housing_needed]|intval|in[0,1]',
						'housing_needed_gender'      => 'if[housing_needed]|intval|in[1,2,3]',
						'housing_needed_nights'      => 'if[housing_needed]|NSEvent::validate_housing_nights',
						'housing_needed_comment'     => 'if[housing_needed]|trim|max_length[65536]|stripslashes',
						));
				}
			}
			
			# Determine appropriate file for current step
			if (empty($_POST) or !NSEvent_FormValidation::validate())
			{
				$file = 'form-reg-info';
			}
			else
			{
				# Used for confirmation page and email
				$package_cost      = (self::$validated_package_id === 0) ? 0 : self::$validated_items[self::$validated_package_id]->get_price_for_discount($_POST['discount'], $early_bird);;
				$competitions      = array();
				$competitions_cost = 0;
				$shirts            = array();
				$shirts_cost       = 0;
				$total_cost        = 0;
				
				if (!isset($_POST['confirmed']))
				{
					$dancer = new NSEvent_Dancer($_POST);
					$file = 'form-confirm';
				}
				else
				{
					if ($options['registration_testing']) {
						$_POST['payment_meta'] = __('TEST', 'nsevent');
					}
					
					if ($_POST['status'] == '1') {
						$_POST['note'] = $_POST['volunteer_phone'];
					}
					
					# Add dancer
					$dancer = NSEvent_Dancer::add($_POST);
					if (!$dancer) {
						throw new Exception('Unable to add dancer to database.');
					}
					
					# Add registrations				
					foreach (self::$validated_items as $item)
					{
						$item_price = $item->get_price_for_discount($_POST['discount'], $early_bird);
						
						if ($item->type == 'competition') {
							$competitions[$item->id] = $item;
							$competitions_cost += $item_price;
						}
						elseif ($item->type == 'shirt') {
							$shirts[$item->id] = $item;
							$shirts_cost += $item_price;
						}
						
						$total_cost += $item_price;
						
						NSEvent_Registration::add(array(
							'dancer_id' => $dancer->id,
							'item_id'   => $item->id,
							'price'     => $item_price,
							'item_meta' => (!isset($_POST['item_meta'][$item->id]) ? '' : $_POST['item_meta'][$item->id]),
							));
					}
					
					# Add housing info
					if (isset($_POST['housing_needed']))
						$dancer->add_housing_needed($_POST);
					elseif (isset($_POST['housing_provider']))
						$dancer->add_housing_provider($_POST);
					
					
					// TODO: For VIPs, force payment_method to "mail" if their total cost is 0?
					
					
					# Confirmation email
					if (!$options['registration_testing'])
					{
						$confirmation_email = array(
							'to_email' => $dancer->email,
							'to_name'  => $dancer->name(),
							'subject'  => sprintf(__('Registration for %s: %s', 'nsevent'), $event->name, $dancer->name())
							);
						
						# Get body of email message
						ob_start();
						require dirname(__FILE__).'/registration/confirmation-email.php';
						$confirmation_email['body'] = preg_replace("/^(- .+\n)\n+-/m", '$1-', ob_get_contents());
						ob_end_clean();
						
						self::send_confirmation_email($confirmation_email);
					}
					
					
					if (isset($_POST['payment_method']) and $_POST['payment_method'] == 'PayPal')
						$file = 'form-accepted-paypal';
					else
						$file = 'form-accepted-mail';
				}
			}
			
			# Allow themes to provide their own files
			# Otherwise, load appropriate file for current step
			if (file_exists(sprintf('%s/%s/nsevent/%s.php', get_theme_root(), get_stylesheet(), $file)))
				require sprintf('%s/%s/nsevent/%s.php', get_theme_root(), get_stylesheet(), $file);
			else
				require sprintf('%s/registration/%s.php', dirname(__FILE__), $file);
		}
		catch (Exception $e)
		{
			if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_header(); }
			printf('<div class="nsevent-exception">%s</div>'."\n", $e->getMessage());
			if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_footer(); }
		}
	}
	
	// static public function registration_send_email($event, $dancer)
	// {
	// 	
	// }
	
	static public function registration_head()
	{
		add_action('wp_head', 'NSEvent::registration_wp_head');
		wp_enqueue_style('nsevent-registration', sprintf('%s/%s/css/registration.css', WP_PLUGIN_URL, basename(__FILE__, '.php')));
		
		# Check if the current theme has a stylesheet for the registration
		$theme_stylesheet = sprintf('%s/%s/nsevent/registration.css', get_theme_root(), get_stylesheet());
		if (file_exists($theme_stylesheet))
			wp_enqueue_style('nsevent-registration-theme', sprintf('%s/nsevent/registration.css', get_bloginfo('stylesheet_directory')));
		
		wp_enqueue_script('nsevent-reg-info', sprintf('%s/%s/js/reg-info.js', WP_PLUGIN_URL, basename(__FILE__, '.php')), array('jquery'));
	}
	
	static public function registration_wp_head()
	{
		# Block search engines for this page if they are not blocked already
		if (get_option('blog_public')) {
			echo '<meta name=\'robots\' content=\'noindex,nofollow\' />'."\n";
		}
	}
	
	static public function send_confirmation_email(array $parameters)
	{
		$options = get_option('nsevent');
		
		$parameters = array_merge(array(
			'to_email' => '',
			'to_name'  => '',
			'subject'  => '',
			'body'     => '',
			), $parameters);
		
		if (class_exists('SwiftMailerWP'))
		{
			$message = Swift_Message::newInstance()
							->setSubject($parameters['subject'])
							->setFrom($options['confirmation_email_address'])
							->setReplyTo($options['confirmation_email_address'])
							->addTo($parameters['to_email'], $parameters['to_name'])
							->setBody($parameters['body']);
			
			if ($options['confirmation_email_bcc'])
				$message->setBcc($options['confirmation_email_bcc']);
			
			$headers = $message->getHeaders();
			$headers->addTextHeader('X-Mailer', 'NSEvent Mailer');
			
			return SwiftMailerWP::get_instance()->get_mailer()->send($message);
		}
		else
		{
			$headers = sprintf('From: %1$s'."\r\n".'Reply-To: %1$s'."\r\n".'X-Mailer: NSEvent Mailer'."\r\n", $options['confirmation_email_address']);
			
			if ($options['confirmation_email_bcc'])
				$headers .= sprintf('Bcc: %s'."\r\n", $options['confirmation_email_bcc']);
			
			return wp_mail(
				$parameters['to_email'],
				$parameters['subject'],
				$parameters['body'],
				$headers);
		}
	}
	
	static public function validate_package($package_id)
	{
		if ($package_id === 0)
			return True;
		elseif (self::validate_items(array($package_id => $package_id)))
		{
			self::$validated_package_id = $package_id;
			return True;
		}
		else
			return False;
	}
	
	static public function validate_items($items)
	{
		if (empty($items))
			return True; # skip
		elseif (!is_array($items))
			return False;
		
		if (empty($_POST['item_meta']) or !is_array($_POST['item_meta']))
			$_POST['item_meta'] = array();
		
		$items_did_validate = True;
		foreach($items as $key => $value)
		{
			$item = NSEvent_Item::find($key);
			if (!$item)
				continue;
			
			if ($item->has_meta)
			{
				switch ($item->has_meta)
				{
					# If position wasn't specified specifically for item, use dancer's position.
					case 'position':
						if (!isset($_POST['item_meta'][$item->id]) or !in_array($_POST['item_meta'][$item->id], array('lead', 'follow')))
							if (!NSEvent_FormValidation::get_error('position'))
								$_POST['item_meta'][$item->id] = ($_POST['position'] == 1) ? 'lead' : 'follow';
						break;
					
					case 'partner_name':
						if (empty($_POST['item_meta'][$item->id]))
						{
							NSEvent_FormValidation::set_error('item_'.$item->id, sprintf(__('Your partner\'s name must be specified for %s.', 'nsevent'), $item->name));
							$items_did_validate = False;
							continue 2;
						}
						else
						{
							$_POST['item_meta'][$item->id] = trim($_POST['item_meta'][$item->id]);
							// TODO: Check if partner has already registered for this item.
						}
						break;
					
					case 'team_members':
						if (empty($_POST['item_meta'][$item->id]))
						{
							NSEvent_FormValidation::set_error('item_'.$item->id, sprintf(__('Team members must be specified for %s.', 'nsevent'), $item->name));
							$items_did_validate = False;
							continue 2;
						}
						else
						{
							# Standarize formatting
							$_POST['item_meta'][$item->id] = ucwords(preg_replace(array("/[\r\n]+/", "/\n+/", "/\r+/", '/,([^ ])/', '/, , /'), ', $1', trim($_POST['item_meta'][$item->id])));
							
							if (strlen($_POST['item_meta'][$item->id]) > 65536)
							{
								NSEvent_FormValidation::set_error('item_'.$item->id, sprintf(__('%s is too long.', 'nsevent'), sprintf(__('Team members list for %s', 'nsevent'), $item->name)));
								$items_did_validate = False;
								continue 2;
							}
						}
						break;
					
					case 'size':
						if (!in_array($value, array_merge(array('none'), explode(',', $item->description))))
						{
							NSEvent_FormValidation::set_error('item_'.$item->id, sprintf(__('An invalid size was choosen for %s.', 'nsevent'), $item->name));
							$items_did_validate = False;
							continue 2;
						}
						elseif ($value === 'none')
							continue 2; # No size selected;
						$_POST['item_meta'][$item->id] = $value; # Populate `item_meta` for the confirmation and PayPal page
						break;
				}
			}
			
			# Check openings again, in case they have filled since the form was first displayed to the user
			if (($item->has_meta != 'position' and !$item->openings()) or ($item->has_meta == 'position' and !$item->openings($_POST['item_meta'][$item->id])))
			{
				NSEvent_FormValidation::set_error('item_'.$item->id, sprintf(__('There are no longer any openings for %s.', 'nsevent'), $item->name));
				$items_did_validate = False;
				continue;
			}
			
			self::$validated_items[$item->id] = $item;
		}
		
		return $items_did_validate;
	}
	
	static public function validate_position($position)
	{
		if (self::$event->limit_per_position and self::$event->limit_per_position <= NSEvent_Dancer::count('position', $position))
		{
			NSEvent_FormValidation::set_error('position', __('Registrations are no longer being accepted for that position.', 'nsevent'));
			return False;
		}
		else
			return True;
	}
	
	static public function validate_status($status)
	{
		if (self::$vip === True)
			return 2;
		else if (self::$event->has_volunteers and $_POST['status'] == '1')
		{
			return 1;
		}
		else
			return 0;
	}

	static public function validate_housing_nights($nights)
	{
		// TODO: This could be a stricter check…
		if (is_array($nights))
			return array_sum($nights);
		else
		 	return is_numeric($nights);
	}
	
	static public function validate_volunteer_phone($phone_number)
	{
		if (self::$vip)
			return True;
		elseif (empty($phone_number))
			return False;
		
		preg_match('/^(?:\(?([0-9]{3})\)?)?[- \.]?([0-9]{3})[- \.]?([0-9]{4})/', $phone_number, $matches);
		unset($matches[0]);
		
		return (!empty($matches)) ? implode('-', array_filter($matches)) : True;
	}
	
	static public function paypal_href($dancer, $options, array $item_ids = array(), $notify_url = '')
	{
		$href = sprintf('https://www.paypal.com/cgi-bin/webscr?cmd=_cart&amp;upload=1&amp;no_shipping=1&amp;%1$sbusiness=%2$s&amp;custom=%3$d&amp;item_name_1=%4$s&amp;amount_1=%5$d',
			!empty($notify_url) ? sprintf('notify_url=%1$s&amp;', rawurlencode($notify_url)) : '',
			rawurlencode($options['paypal_business']),
			$dancer->id,
			__('Processing Fee', 'nsevent'),
			2);
				
		$i = 2;
		foreach ($dancer->registrations($item_ids) as $reg)
		{
			$href .= sprintf('&amp;item_name_%1$d=%2$s&amp;amount_%1$d=%3$s', $i, rawurlencode($reg->item()->name), rawurlencode($reg->price));

			if (!empty($reg->item_meta))
				$href .= sprintf('&amp;on0_%1$d=%2$s&amp;os0_%1$d=%3$s', $i, $reg->item()->meta_label(), ucfirst($reg->item_meta));

			$i++;
		}

		return $href;
	}
}

add_action('admin_init', 'NSEvent::admin_init');
add_action('admin_menu', 'NSEvent::admin_menu');
register_activation_hook(__FILE__, 'NSEvent::plugin_activate');
endif;
