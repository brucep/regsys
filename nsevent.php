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
	static private $event, $options, $vip, $validated_package_id = 0, $validated_items = array();
	static private $default_options = array(
		'current_event_id'           => '',
		'registration_testing'       => false,
		'paypal_business'            => '',
		'paypal_fee'                 => 0,
		'paypal_sandbox'             => false,
		'confirmation_email_address' => '',
		'confirmation_email_bcc'     => '',
		'mailing_address'            => '',
		'payable_to'                 => '',
		'postmark_within'            => 7,
		);
	
	private function __clone() {}
	private function __construct() {}
	
	static public function admin_init()
	{
		load_plugin_textdomain('nsevent', false, basename(__FILE__, '.php').'/translations');
		register_setting('nsevent', 'nsevent', 'NSEvent::admin_validate_options');
	}
	
	static public function admin_menu()
	{
		$hookname = add_menu_page('Event Reports', 'Event Reports', 'edit_pages', 'nsevent', 'NSEvent::page_request');
		add_submenu_page('nsevent', 'NSEvent Options', 'Options', 'administrator', 'nsevent-options', 'NSEvent::page_options');
		add_action('admin_print_styles-'.$hookname, 'NSEvent::admin_print_styles');
	}

	static public function admin_print_styles()
	{
	    if (!isset($_GET['style']) or !in_array($_GET['style'], array('iPhone', 'print'))) {
    		wp_enqueue_style('nsevent-admin',        sprintf('%s/%s/css/admin-screen.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), false, false, 'screen and (min-device-width: 481px)');
    		wp_enqueue_style('nsevent-admin-iphone', sprintf('%s/%s/css/admin-iphone.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), false, false, 'only screen and (max-device-width: 480px)');
		}
        elseif ($_GET['style'] === 'iPhone') {
            wp_enqueue_style('nsevent-admin-iphone', sprintf('%s/%s/css/admin-iphone.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), false, false, 'screen');
		}
		elseif ($_GET['style'] === 'print') {
			wp_enqueue_style('nsevent-admin-iphone', sprintf('%s/%s/css/admin-print.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), false, false, 'screen');
		}
		
        
		wp_enqueue_style('nsevent-admin-print',      sprintf('%s/%s/css/admin-print.css',  WP_PLUGIN_URL, basename(__FILE__, '.php')), false, false, 'print');
		wp_enqueue_style('nsevent-admin-despise-ie', sprintf('%s/%s/css/admin-screen.css', WP_PLUGIN_URL, basename(__FILE__, '.php')), false, false, 'screen');
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
		
		if (isset($input['current_event_id'])) {
			// TODO: Check if id exists
			$options['current_event_id'] = (int) $input['current_event_id'];
		}
		
		if (isset($input['paypal_business'])) {
			$options['paypal_business'] = trim($input['paypal_business']);
		}
		
		if (isset($input['paypal_fee'])) {
			$options['paypal_fee'] = (int) $input['paypal_fee'];
		}
		
		if (isset($input['confirmation_email_address'])) {
			$options['confirmation_email_address'] = trim($input['confirmation_email_address']);
		}
		else {
			$options['confirmation_email_address'] = get_option('admin_email');
		}
		
		if (isset($input['confirmation_email_bcc'])) {
			$options['confirmation_email_bcc'] = trim($input['confirmation_email_bcc']);
		}
		else {
			$options['confirmation_email_bcc'] = '';
		}
		
		if (isset($input['mailing_address'])) {
			$options['mailing_address'] = trim($input['mailing_address']);
		}
		
		if (isset($input['payable_to'])) {
			$options['payable_to'] = trim($input['payable_to']);
		}
		
		if (isset($input['postmark_within'])) {
			$options['postmark_within'] = (int) $input['postmark_within'];
		}
		else {
			$options['postmark_within'] = 7;
		}
		
		$options['registration_testing']   = isset($input['registration_testing']);
		
		return $options;
	}
	
	static public function get_database_connection()
	{
		global $wpdb;
		
		require_once dirname(__FILE__).'/includes/database.php';
		
		return new NSEvent_Database(array(
			'host'     => DB_HOST,
			'port'     => defined('DB_HOST_PORT') ? DB_HOST_PORT : false,
			'name'     => DB_NAME,
			'user'     => DB_USER,
			'password' => DB_PASSWORD,
			'prefix'   => $wpdb->prefix.'nsevent',
			));
	}
	
	static public function load_models()
	{
		require dirname(__FILE__).'/includes/model.php';
		require dirname(__FILE__).'/includes/model-event.php';
		require dirname(__FILE__).'/includes/model-item.php';
		require dirname(__FILE__).'/includes/model-dancer.php';
	}
	
	static public function page_options()
	{
		if (!current_user_can('administrator')) {
			throw new Exception(__('Cheatin&#8217; uh?'));
		}
		
		$options = array_merge(self::$default_options, get_option('nsevent', array()));
		
		self::load_models();
		NSEvent_Model::set_database(self::get_database_connection());
		NSEvent_Model::set_options($options);
		
		$events = NSEvent_Model_Event::get_events();
		
		require dirname(__FILE__).'/admin/options.php';
	}
	
	static public function page_request()
	{
		global $wpdb;
		
		try {
			@date_default_timezone_set(get_option('timezone_string'));
			
			if (!current_user_can('edit_pages')) {
				throw new Exception(__('Cheatin&#8217; uh?'));
			}
			
			if (empty($_GET['request'])) {
				$_GET['request'] = 'index';
			}
			
			$options = array_merge(self::$default_options, get_option('nsevent', array()));
			
			self::load_models();
			NSEvent_Model::set_database(self::get_database_connection());
			NSEvent_Model::set_options($options);
			
			switch ($_GET['request']) {
				# List of events
				case 'index':
					$file = 'reports/index.php';
					break;
				
				# Admin forms
				case 'dancer-delete':
				case 'dancer-edit':
				case 'housing-delete':
				case 'registration-add':
				case 'resend-confirmation-email':
					if (!current_user_can('administrator')) {
						throw new Exception(__('Cheatin&#8217; uh?'));
					}
					if (empty($_GET['event_id'])) {
						throw new Exception(__('Event ID not specified.', 'nsevent'));
					}
				    if ($_GET['event_id'] !== 'add' and (!$event = NSEvent_Model_Event::get_event_by_id($_GET['event_id']))) {
						throw new Exception(sprintf(__('Event ID not found: %d', 'nsevent'), $_GET['event_id']));
					}
					if (empty($_GET['dancer'])) {
						throw new Exception(__('Dancer ID not specified.', 'nsevent'));
					}
					if (!$dancer = $event->get_dancer_by_id($_GET['dancer'])) {
						throw new Exception(sprintf(__('Dancer ID not found: %d', 'nsevent'), $_GET['parameter']));
					}
					require dirname(__FILE__).'/includes/form-input.php';
					$file = sprintf('admin/%s.php', $_GET['request']);
					break;
				
				case 'event-edit':
					if (!current_user_can('administrator')) {
						throw new Exception(__('Cheatin&#8217; uh?'));
					}
					if (empty($_GET['event_id'])) {
						throw new Exception(__('Event ID not specified.', 'nsevent'));
					}
				    if ($_GET['event_id'] !== 'add' and (!$event = NSEvent_Model_Event::get_event_by_id($_GET['event_id']))) {
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
					if (!$event = NSEvent_Model_Event::get_event_by_id($_GET['event_id'])) {
						throw new Exception(sprintf(__('Event ID not found: %d', 'nsevent'), $_GET['event_id']));
					}
					if (!isset($file)) {
						$file = sprintf('reports/%s.php', $_GET['request']);
					}
					break;
				
				default:
					throw new Exception(sprintf(__('Unable to handle page request: %s', 'nsevent'), esc_html($_GET['request'])));
			}
			
			require dirname(__FILE__)."/$file";
		}
		catch (Exception $e) {
			printf('<div id="nsevent-exception">%s</div>', $e->getMessage());
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
			'housing');
		
		foreach ($tables as $table) {
			$table_name = sprintf('%snsevent_%s', $wpdb->prefix, $table);
			
			# Create new database tables
			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				switch ($table):
					case 'events':
						$query = sprintf("CREATE TABLE `%s` (
							`id`                     int(10) unsigned NOT NULL auto_increment,
							`name`                   varchar(255) NOT NULL,
							`date_mail_prereg_end`   int(10) unsigned NOT NULL default '0',
							`date_paypal_prereg_end` int(10) unsigned NOT NULL default '0',
							`date_refund_end`        int(10) unsigned NOT NULL default '0',
							`has_discount`           tinyint(1) unsigned NOT NULL DEFAULT '0',
							`has_vip`                tinyint(1) unsigned NOT NULL default '0',
							`has_volunteers`         tinyint(1) unsigned NOT NULL default '0',
							`has_housing`            tinyint(1) unsigned NOT NULL default '0',
							`housing_nights`         tinyint(2) unsigned NOT NULL default '1',
							`limit_discount`         tinyint(3) unsigned NOT NULL default '0',
							`discount_org_name`      varchar(255) NOT NULL DEFAULT '',
							`levels`                 varchar(255) NOT NULL,
							`shirt_description`      text NOT NULL,
							PRIMARY KEY  (`id`)
							);", $table_name);
						break;
					
					case 'items':
						$query = sprintf("CREATE TABLE `%s` (
							`event_id`               int(10) unsigned NOT NULL,
							`id`                     int(10) unsigned NOT NULL AUTO_INCREMENT,
							`name`                   varchar(200) NOT NULL,
							`type`                   varchar(11) NOT NULL,
							`preregistration`        tinyint(1) unsigned NOT NULL DEFAULT '1',
							`price_prereg`           tinyint(3) unsigned NOT NULL DEFAULT '0',
							`price_door`             tinyint(3) unsigned NOT NULL DEFAULT '0',
							`price_discount`         tinyint(3) unsigned NOT NULL DEFAULT '0',
							`price_vip`              tinyint(3) unsigned NOT NULL DEFAULT '0',
							`limit_total`            smallint(5) unsigned NOT NULL DEFAULT '0',
							`limit_per_position`     smallint(5) unsigned NOT NULL DEFAULT '0',
							`date_expires`           int(10) unsigned NOT NULL DEFAULT '0',
							`meta`                   varchar(20) NOT NULL DEFAULT '',
							`description`            varchar(255) NOT NULL DEFAULT '',
							PRIMARY KEY (`id`)
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
							`payment_owed`      smallint(5) unsigned NOT NULL default '0',
							`mobile_phone`      varchar(30) NOT NULL DEFAULT '',
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
					
					case 'housing':
						$query = sprintf("CREATE TABLE `%s` (
							`event_id`                int(10) UNSIGNED NOT NULL,
							`dancer_id`               int(10) UNSIGNED NOT NULL,
							`housing_type`            tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
							`housing_spots_available` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
							`housing_nights`          tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
							`housing_gender`          tinyint(1) UNSIGNED NOT NULL DEFAULT '3',
							`housing_bedtime`         tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
							`housing_pets`            tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
							`housing_smoke`           tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
							`housing_from_scene`      varchar(255) NOT NULL DEFAULT '',
							`housing_comment`         text NOT NULL,
							PRIMARY KEY (`event_id`,`dancer_id`)
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
		
		try {
			# Define a constant for themes to use
			define('NSEVENT_REGISTRATION_FORM', true);
			
			# Stop the `WP Super Cache` plugin from caching registration pages
			define('DONOTCACHEPAGE', true);
			
			@date_default_timezone_set(get_option('timezone_string'));
			
			$options = self::$options = array_merge(self::$default_options, get_option('nsevent', array()));
			
			require dirname(__FILE__).'/includes/form-input.php';
			require dirname(__FILE__).'/includes/form-validation.php';
			NSEvent_FormValidation::set_error_messages();
			
			self::load_models();
			NSEvent_Model::set_database(self::get_database_connection());
			NSEvent_Model::set_options($options);
			
			# Find current event
			$event = self::$event = NSEvent_Model_Event::get_event_by_id($options['current_event_id']);
			
			if (!$event) {
				throw new Exception(sprintf(__('Event ID not found: %d', 'nsevent'), $options['current_event_id']));
			}
			
			$vip = self::$vip = ($event->has_vip() and isset($_GET['vip']));
			
			
			# Display page content when registration is not available.
			if ((time() > $event->get_date_paypal_prereg_end() and time() > $event->get_date_mail_prereg_end() and !$vip) or ($options['registration_testing'] and !current_user_can('edit_pages'))) {
				get_template_part('page');
				return;
			}
			
			
			# Setup validation rules
			if (!empty($_POST)) {
				NSEvent_FormValidation::add_rules(array(
					'first_name'      => 'trim|required|max_length[100]|ucfirst',
					'last_name'       => 'trim|required|max_length[100]|ucfirst',
					'email'           => 'trim|valid_email|max_length[100]|NSEvent::validate_email_address',
					'confirm_email'   => 'trim|valid_email|max_length[100]',
					'mobile_phone'    => 'trim|required|max_length[30]',
					'position'        => 'intval|in[1,2]',
					'status'          => 'NSEvent::validate_status',
					'package'         => 'intval|NSEvent::validate_package',
					'items'           => 'NSEvent::validate_items',
					'payment_method'  => 'in[Mail,PayPal]',
					));
				
				# Level
				if ($event->has_levels()) {
					NSEvent_FormValidation::add_rule('level', sprintf('intval|in[%s]',
						implode(',', array_keys($event->get_levels()))));
				}
				else {
					$_POST['level'] = 1;
				}
				
				# Discount
				if ($event->has_discount()) {
					NSEvent_FormValidation::add_rule('payment_discount', 'intval|in[0,1]|NSEvent::validate_discount');
				}
				else {
					$_POST['payment_discount'] = 0;
				}
				
				# Housing
				if ($event->has_housing_enabled()) {
					NSEvent_FormValidation::add_rules(array(
						'housing_provider[housing_spots_available]' => 'if_set[housing_type_provider]|intval|greater_than[0]',
						'housing_provider[housing_smoke]'           => 'if_set[housing_type_provider]|intval|in[0,1]',
						'housing_provider[housing_pets]'            => 'if_set[housing_type_provider]|intval|in[0,1]',
						'housing_provider[housing_gender]'          => 'if_set[housing_type_provider]|intval|in[1,2,3]',
						'housing_provider[housing_bedtime]'         => 'if_set[housing_type_provider]|intval|in[0,1,2]',
						'housing_provider[housing_nights]'          => 'if_set[housing_type_provider]|NSEvent::validate_housing_nights',
						'housing_provider[housing_comment]'         => 'if_set[housing_type_provider]|trim|max_length[65536]',
						'housing_needed[housing_from_scene]'        => 'if_set[housing_type_needed]|trim|required|max_length[255]|ucwords',
						'housing_needed[housing_smoke]'             => 'if_set[housing_type_needed]|intval|in[0,1]',
						'housing_needed[housing_pets]'              => 'if_set[housing_type_needed]|intval|in[0,1]',
						'housing_needed[housing_gender]'            => 'if_set[housing_type_needed]|intval|in[1,2,3]',
						'housing_needed[housing_bedtime]'           => 'if_set[housing_type_needed]|intval|in[0,1,2]',
						'housing_needed[housing_nights]'            => 'if_set[housing_type_needed]|NSEvent::validate_housing_nights',
						'housing_needed[housing_comment]'           => 'if_set[housing_type_needed]|trim|max_length[65536]',
						));
				}
			}
			
			# Determine appropriate file for current step
			if (empty($_POST) or !NSEvent_FormValidation::validate()) {
				$file = 'form-reg-info';
			}
			else {
				# Used for confirmation page and email
				$package_cost      = 0;
				$competitions      = array();
				$competitions_cost = 0;
				$shirts            = array();
				$shirts_cost       = 0;
				$total_cost        = 0;
				
				if ($vip) {
					if (self::$validated_package_id !== 0) {
						$package_cost = self::$validated_items[self::$validated_package_id]->get_price_for_vip();
					}
					
					foreach (self::$validated_items as $item) {
						$total_cost += $item->get_price_for_vip();
					}
				}
				else {
					if (self::$validated_package_id !== 0) {
						$package_cost = self::$validated_items[self::$validated_package_id]->get_price_for_prereg($_POST['payment_discount']);
					}
					
					foreach (self::$validated_items as $item) {
						$total_cost += $item->get_price_for_prereg($_POST['payment_discount']);
					}
				}
				
				if ($total_cost == 0) {
					$_POST['payment_method'] = 'Mail';
				}
				
				
				# Prep info before creating new dancer object
				$dancer_data = $_POST;
				unset($dancer_data['items'], $dancer_data['item_meta'], $dancer_data['confirmed'], $dancer_data['confirm_email']);
				
				$dancer_data['payment_owed'] = $total_cost;
				$dancer_data['payment_confirmed'] = ($total_cost == 0) ? 1 : 0;
				
				if ($options['registration_testing']) {
					$dancer_data['note'] = __('TEST', 'nsevent');
				}
				
				if ($event->has_housing_enabled()) {
					if (isset($dancer_data['housing_type_needed'])) {
						$dancer_data = array_merge($dancer_data, $dancer_data['housing_needed']);
						$dancer_data['housing_type'] = 1;
					}
					elseif (isset($dancer_data['housing_type_provider'])) {
						$dancer_data = array_merge($dancer_data, $dancer_data['housing_provider']);
						$dancer_data['housing_type'] = 2;
					}
					
					unset($dancer_data['housing_type_needed'],$dancer_data['housing_needed'], $dancer_data['housing_type_provider'], $dancer_data['housing_provider']);
				}
				
				$dancer = new NSEvent_Model_Dancer($dancer_data);
				
				
				if (!isset($_POST['confirmed'])) {
					$file = 'form-confirm';
				}
				else {
					# Add dancer
					$dancer->add($event->get_id());
					
					if (!$dancer) {
						throw new Exception(__('Unable to add dancer to database.', 'nsevent'));
					}
					
					# Add housing
					if ($event->has_housing_enabled() and ($dancer->needs_housing() or $dancer->is_housing_provider())) {
						$dancer->add_housing();
					}
					
					# Add registrations				
					foreach (self::$validated_items as $item) {
						if ($vip) {
							$item_price = $item->get_price_for_vip();
						}
						else {
							$item_price = $item->get_price_for_prereg($_POST['payment_discount']);
						}
						
						if ($item->get_type() == 'competition') {
							$competitions[$item->get_id()] = $item;
							$competitions_cost += $item_price;
						}
						elseif ($item->get_type() == 'shirt') {
							$shirts[$item->get_id()] = $item;
							$shirts_cost += $item_price;
						}
						
						$event->add_registration(array(
							'dancer_id' => $dancer->get_id(),
							'item_id'   => $item->get_id(),
							'price'     => $item_price,
							'item_meta' => (!isset($_POST['item_meta'][$item->get_id()]) ? '' : $_POST['item_meta'][$item->get_id()]),
							));
					}
					
					
					// TODO: For VIPs, force payment_method to "mail" if their total cost is 0?
					
					
					# Confirmation email
					if (!$options['registration_testing']) {
						$confirmation_email = array(
							'to_email' => $dancer->get_email(),
							'to_name'  => $dancer->get_name(),
							'subject'  => sprintf(__('Registration for %s: %s', 'nsevent'), $event->get_name(), $dancer->get_name())
							);
						
						# Get body of email message
						ob_start();
						require dirname(__FILE__).'/registration/confirmation-email.php';
						$confirmation_email['body'] = preg_replace("/^(- .+\n)\n+-/m", '$1-', ob_get_contents());
						ob_end_clean();
						
						try {
							self::send_confirmation_email($confirmation_email);
						}
						catch (Exception $e) {
							$confirmation_email_failed_to_send = $e;
							error_log('Error sending confirmation email: ' . $e->getMessage());
						}
					}
					
					
					if (isset($_POST['payment_method']) and $_POST['payment_method'] == 'PayPal') {
						$file = 'form-accepted-paypal';
					}
					else {
						$file = 'form-accepted-mail';
					}
				}
			}
			
			# Allow themes to provide their own files
			# Otherwise, load appropriate file for current step
			if (file_exists(sprintf('%s/%s/nsevent/%s.php', get_theme_root(), get_stylesheet(), $file))) {
				require sprintf('%s/%s/nsevent/%s.php', get_theme_root(), get_stylesheet(), $file);
			}
			else {
				require sprintf('%s/registration/%s.php', dirname(__FILE__), $file);
			}
		}
		catch (Exception $e) {
			if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_header(); }
			printf('<div id="nsevent-exception">%s</div>'."\n", $e->getMessage());
			if (!get_post_meta($post->ID, 'nsevent_registration_form', true)) { get_footer(); }
		}
	}
	
	static public function registration_head()
	{
		add_action('wp_head', 'NSEvent::registration_wp_head');
		wp_enqueue_style('nsevent-registration', sprintf('%s/%s/css/registration.css', WP_PLUGIN_URL, basename(__FILE__, '.php')));
		
		# Check if the current theme has a stylesheet for the registration
		$theme_stylesheet = sprintf('%s/%s/nsevent/registration.css', get_theme_root(), get_stylesheet());
		if (file_exists($theme_stylesheet)) {
			wp_enqueue_style('nsevent-registration-theme', sprintf('%s/nsevent/registration.css', get_bloginfo('stylesheet_directory')));
		}
		
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
		
		if (class_exists('SwiftMailerWP')) {
			$message = Swift_Message::newInstance()
							->setSubject($parameters['subject'])
							->setFrom($options['confirmation_email_address'])
							->setReplyTo($options['confirmation_email_address'])
							->addTo($parameters['to_email'], $parameters['to_name'])
							->setBody($parameters['body']);
			
			if ($options['confirmation_email_bcc']) {
				$message->setBcc($options['confirmation_email_bcc']);
			}
			
			$headers = $message->getHeaders();
			$headers->addTextHeader('X-Mailer', 'NSEvent Mailer');
			
			return SwiftMailerWP::get_instance()->get_mailer()->send($message);
		}
		else {
			$headers = sprintf('From: %1$s'."\r\n".'Reply-To: %1$s'."\r\n".'X-Mailer: NSEvent Mailer'."\r\n", $options['confirmation_email_address']);
			
			if ($options['confirmation_email_bcc']) {
				$headers .= sprintf('Bcc: %s'."\r\n", $options['confirmation_email_bcc']);
			}
			
			return wp_mail(
				$parameters['to_email'],
				$parameters['subject'],
				$parameters['body'],
				$headers);
		}
	}
	
	static public function validate_discount($payment_discount)
	{
		if ($payment_discount == 1 and !self::$event->has_discount_openings()) {
			$_POST['payment_discount'] = 0; // Change the value so that the checkbox won't appear checked.
			NSEvent_FormValidation::set_error('payment_discount', 'There are no more discount openings available. Review the prices before continuing with your registration.');
			return false;
		}
		else {
			return true;
		}
	}
	
	static public function validate_email_address($email)
	{
		if (isset($_POST['confirm_email']) and $_POST['confirm_email'] == $email) {
			if (self::$options['registration_testing'] or empty($_POST['first_name']) or empty($_POST['last_name'])) {
				return true;
			}
			elseif (!self::$event->get_dancers_where(array(':first_name' => $_POST['first_name'], ':last_name' => $_POST['last_name'], ':email' => $email))) {
				return true;
			}
			else {
				NSEvent_FormValidation::set_error('email', sprintf(__('Someone has already registered with this information. If you have already registered and need to change your information, then please reply to your confirmation email. For any other concerns, email <a href="mailto:%1$s">%1$s</a>.', 'nsevent'), self::$options['confirmation_email_address']));
				return false;
			}
		}
		else {
			NSEvent_FormValidation::set_error('email', __('Your email addresses do not match.', 'nsevent'));
			return false;
		}
	}
	
	static public function validate_package($package_id)
	{
		if ($package_id === 0) {
			return true;
		}
		elseif (self::validate_items(array($package_id => $package_id))) {
			$item = self::$event->get_item_by_id($package_id);
			
			if (isset($_POST['package_tier'][$package_id]) and $_POST['package_tier'][$package_id] != $item->get_price_tier()) {
				NSEvent_FormValidation::set_error('package', 'The price has changed on this package. Review the price before continuing with your registration.');
				return false;
			}
			else {
				self::$validated_package_id = $package_id;
				return true;
			}
		}
		else {
			return false;
		}
	}
	
	static public function validate_items($items)
	{
		if (empty($items)) {
			return true; # skip
		}
		elseif (!is_array($items)) {
			return false;
		}
		
		if (empty($_POST['item_meta']) or !is_array($_POST['item_meta'])) {
			$_POST['item_meta'] = array();
		}
		
		$items_did_validate = true;
		
		foreach ($items as $key => $value) {
			$item = self::$event->get_item_by_id($key);
			
			if (!$item) {
				continue;
			}
			
			switch ($item->get_meta()) {
				# If position wasn't specified specifically for item, use dancer's position.
				case 'position':
					if (!isset($_POST['item_meta'][$item->get_id()]) or !in_array($_POST['item_meta'][$item->get_id()], array('lead', 'follow'))) {
						if (!NSEvent_FormValidation::get_error('position')) {
							$_POST['item_meta'][$item->get_id()] = ($_POST['position'] == 1) ? 'lead' : 'follow';
						}
					}
					break;
				
				case 'partner_name':
					if (empty($_POST['item_meta'][$item->get_id()])) {
						NSEvent_FormValidation::set_error('item_'.$item->get_id(), sprintf(__('Your partner\'s name must be specified for %s.', 'nsevent'), $item->get_name()));
						$items_did_validate = false;
						continue 2;
					}
					else {
						$_POST['item_meta'][$item->get_id()] = trim($_POST['item_meta'][$item->get_id()]);
						// TODO: Check if partner has already registered for this item.
					}
					break;
				
				case 'team_members':
					if (empty($_POST['item_meta'][$item->get_id()])) {
						NSEvent_FormValidation::set_error('item_'.$item->get_id(), sprintf(__('Team members must be specified for %s.', 'nsevent'), $item->get_name()));
						$items_did_validate = false;
						continue 2;
					}
					else {
						# Standarize formatting
						$_POST['item_meta'][$item->get_id()] = ucwords(preg_replace(array("/[\r\n]+/", "/\n+/", "/\r+/", '/,([^ ])/', '/, , /'), ', $1', trim($_POST['item_meta'][$item->get_id()])));
						
						if (strlen($_POST['item_meta'][$item->get_id()]) > 65536) {
							NSEvent_FormValidation::set_error('item_'.$item->get_id(), sprintf(__('%s is too long.', 'nsevent'), sprintf(__('Team members list for %s', 'nsevent'), $item->get_name())));
							$items_did_validate = false;
							continue 2;
						}
					}
					break;
				
				case 'size':
					if (!in_array($value, array_merge(array('none'), explode(',', $item->get_description())))) {
						NSEvent_FormValidation::set_error('item_'.$item->get_id(), sprintf(__('An invalid size was choosen for %s.', 'nsevent'), $item->get_name()));
						$items_did_validate = false;
						continue 2;
					}
					elseif ($value === 'none') {
						continue 2; # No size selected;
					}
					$_POST['item_meta'][$item->get_id()] = $value; # Populate `item_meta` for the confirmation and PayPal page
					break;
			}
			
			# Check openings again, in case they have filled since the form was first displayed to the user
			if (($item->get_meta() != 'position' and !$item->count_openings()) or ($item->get_meta() == 'position' and !$item->count_openings($_POST['item_meta'][$item->get_id()]))) {
				NSEvent_FormValidation::set_error('item_'.$item->get_id(), sprintf(__('There are no longer any openings for %s.', 'nsevent'), $item->name));
				$items_did_validate = false;
				continue;
			}
			
			self::$validated_items[$item->get_id()] = $item;
		}
		
		return $items_did_validate;
	}
	
	static public function validate_status($status)
	{
		if (self::$vip === true) {
			return 2;
		}
		elseif (self::$event->has_volunteers() and isset($_POST['status']) and $_POST['status'] == '1') {
			return 1;
		}
		else {
			return 0;
		}
	}
	
	static public function validate_housing_nights($nights)
	{
		if (is_array($nights)) {
			$nights = array_sum($nights);
		}
		else {
			$nights = (int) $nights;
		}
		
		if ($nights > 0) {
			return $nights;
		}
		else {
			$key = isset($_POST['housing_type_needed']) ? 'housing_needed[housing_nights]' : 'housing_provider[housing_nights]';
			NSEvent_FormValidation::set_error($key, __('You must specify nights for housing.', 'nsevent'));
			return false;
		}
	}
	
	static public function paypal_href($dancer, array $items, array $options, $include_paypal_fee = true)
	{
		$href = sprintf('https://www.paypal.com/cgi-bin/webscr?cmd=_cart&amp;upload=1&amp;no_shipping=1&amp;business=%1$s&amp;custom=%2$d',
			rawurlencode($options['paypal_business']),
			$dancer->get_id());
		
		if ($include_paypal_fee and !empty($options['paypal_fee'])) {
			$href .= sprintf('&amp;item_name_1=%1$s&amp;amount_1=%2$s', 'Processing%20Fee', $options['paypal_fee']);
			$i = 2;
		}
		else {
			$i = 1;
		}
		
		foreach ($items as $item) {
			if ($item->get_registered_price() == 0) {
				continue;
			}
			
			$href .= sprintf('&amp;item_name_%1$d=%2$s&amp;amount_%1$d=%3$s', $i, rawurlencode($item->get_name()), rawurlencode($item->get_registered_price()));
			
			if ($item->get_meta() == 'size') {
				$href .= sprintf('&amp;on0_%1$d=%2$s&amp;os0_%1$d=%3$s', $i, $item->get_meta_label(), ucfirst($item->get_registered_meta()));
			}
			
			$i++;
		}
		
		return $href;
	}
}

add_action('admin_init', 'NSEvent::admin_init');
add_action('admin_menu', 'NSEvent::admin_menu');
register_activation_hook(__FILE__, 'NSEvent::plugin_activate');
endif;
