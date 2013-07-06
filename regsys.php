<?php
/*
Plugin Name: RegSys
Description: An event registration system for dance organizations.
Version: 2.0
Author: Bruce Phillips
License: X11
*/
/*
Copyright (C) 2010 Bruce Phillips

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of the author not be used in advertising or otherwise to promote the sale, use or other dealings in this Software without prior written authorization from the author.
*/

if (!class_exists('RegSys')):
class RegSys
{
	static private $db;
	const version = '2.0';
	
	static public function getDatabaseConnection()
	{
		if (!self::$db) {
			$container = new \Pimple();
			$container['host'] = DB_HOST;
			$container['port'] = defined('DB_PORT') ? DB_PORT : null;
			$container['name'] = DB_NAME;
			$container['user'] = DB_USER;
			$container['pass'] = DB_PASSWORD;
			$container['debug'] = WP_DEBUG;
			
			self::$db = new \RegSys\Database($container);
		}
		
		return self::$db;
	}
	
	static public function getOptions()
	{
		return array_merge(array(
			'currentEventID'      => '',
			'emailBcc'            => '',
			'emailFrom'           => '',
			'emailReplyTo'        => '',
			'emailHost'           => 'smtp.gmail.com',
			'emailPort'           => '465',
			'emailUsername'       => '',
			'emailPassword'       => '',
			'emailEncryption'     => 'ssl',
			'emailTesting'        => false,
			'emailTransport'      => 'mail',
			'mailingAddress'      => '',
			'payableTo'           => '',
			'paypalBusiness'      => '',
			'paypalFee'           => 0,
			'paypalSandbox'       => false,
			'paypalSandboxEmail'  => '',
			'postmarkWithin'      => 7,
			'registrationTesting' => false,
			),
			get_option('regsys', array()));
	}
	
	static public function handleMenuOptionsPage()
	{
		try {
			if (!current_user_can('manage_options')) {
				throw new Exception(__('Cheatin&#8217; uh?'));
			}
			
			$container = new Pimple();
			
			$container['db'] = $container->share(function () { return RegSys::getDatabaseConnection(); });
			$container['debug'] = WP_DEBUG;
			$container['options'] = RegSys::getOptions();
			$container['viewsPath'] = __DIR__ . '/views';
			
			$controller = new \RegSys\Controller\BackEndController\AdminOptions($container);
			echo $controller->render($controller->getContext());
		}
		catch (\Exception $e) {
			printf('<div id="regsys-exception">%s</div>'."\n", $e->getMessage());
		}
	}
	
	static public function handleMenuReportsPage()
	{
		try {
			@date_default_timezone_set(get_option('timezone_string'));
			
			if (empty($_GET['request'])) {
				 $_GET['request'] = 'ReportIndex';
			}
			
			if (!current_user_can('edit_pages') or (!current_user_can('administrator') and substr($_GET['request'], 0, 5) == 'Admin')) {
				throw new Exception(__('Cheatin&#8217; uh?'));
			}
			
			$container = new Pimple();
			
			$container['db'] = $container->share(function () { return RegSys::getDatabaseConnection(); });
			$container['debug'] = WP_DEBUG;
			$container['options'] = RegSys::getOptions();
			$container['viewsPath'] = __DIR__ . '/views';
			$container['notifyUrl'] = function () { return plugins_url('confirm-paypal.php', __FILE__); };
			
			$container['eventID'] = isset($_GET['eventID']) ? (int) $_GET['eventID'] : null;
			$container['isAdmin'] = current_user_can('administrator');
			$container['request'] = $_GET['request'];
			$container['requestHref'] = site_url('wp-admin/admin.php') . '?page=regsys';
			
			$class = '\\RegSys\\Controller\\BackEndController\\' . $container['request'];
			$controller = new $class($container);
			$context = $controller->getContext();
			
			if (is_string($context)) {
				wp_redirect($context);
				exit();
			}
			else {
				if (isset($_GET['noheader'])) {
					require_once ABSPATH . 'wp-admin/admin-header.php';
				}
				echo $controller->render($context);
			}
		}
		catch (\Exception $e) {
			if (isset($_GET['noheader'])) {
				require_once ABSPATH . 'wp-admin/admin-header.php';
			}
			
			printf('<div id="regsys-exception">%s</div>'."\n", $e->getMessage());
		}
	}
	
	static public function registrationForm()
	{
		global $post;
		
		try {			
			# Don't mess with my timezone WordPress!
			@date_default_timezone_set(get_option('timezone_string'));
			
			$container = new \Pimple();
			
			$container['db'] = $container->share(function () { return RegSys::getDatabaseConnection(); });
			$container['debug'] = WP_DEBUG;
			$container['options'] = RegSys::getOptions();
			$container['viewsPath'] = __DIR__ . '/views';
			$container['notifyUrl'] = function () { return plugins_url('confirm-paypal.php', __FILE__); };
			
			$container['isTester'] = current_user_can('edit_pages');
			$container['permalink'] = function () { return get_permalink(); };
			$container['wordpressContent'] = function () use ($post) { return apply_filters('the_content', $post->post_content); };
			$container['shirtDescription'] = function () { return @file_get_contents(sprintf('%s/%s/regsys-shirt-description.html', get_theme_root(), get_stylesheet())); };
			
			$controller = new \RegSys\Controller\FrontEndController($container);
			list($view, $context) = $controller->registrationForm();
			$output = $controller->render($view, $context);
		}
		catch (\Exception $e) {
			$output = printf('<div id="regsys-exception">%s</div>'."\n", $e->getMessage());
		}
		
		# Include default header and footer, unless post meta is used to flag form when not using the default (e.g., a WordPress Page Template for multiple pages).
		if (!get_post_meta($post->ID, 'regsysRegistrationForm', true)) {
			get_header();
			echo $output;
			get_footer();
		}
		else {
			echo $output;
		}
	}
	
	static public function registrationHead()
	{
		add_action('wp_head', 'RegSys::wpHead');
		
		if (file_exists(sprintf('%s/%s/style-regsys-override.css', get_theme_root(), get_stylesheet()))) {
			wp_enqueue_style('regsys-css-theme-override', get_stylesheet_directory_uri() . '/style-regsys-override.css');
		}
		else {
			wp_enqueue_style('regsys-css', plugins_url('static/regsys-register.css', __FILE__));
			
			if (file_exists(sprintf('%s/%s/style-regsys.css', get_theme_root(), get_stylesheet()))) {
				wp_enqueue_style('regsys-css-theme', get_stylesheet_directory_uri() . '/style-regsys.css');
			}
		}
		
		wp_enqueue_script('regsys-script', plugins_url('static/regsys-register.js', __FILE__), array('jquery'));
	}
		
	static public function wpAdminInit()
	{
		register_setting('regsys', 'regsys', 'RegSys::wpAdminValidateOptions');
	}
	
	static public function wpAdminMenu()
	{
		$reportsHook = add_menu_page('Registration Reports', 'Registration Reports', 'edit_pages', 'regsys', 'RegSys::handleMenuReportsPage');
		$optionsHook = add_submenu_page('regsys', 'Registration Options', 'Registration Options', 'manage_options', 'regsys-options', 'RegSys::handleMenuOptionsPage');
		
		add_action('admin_print_scripts-' . $reportsHook, 'RegSys::wpAdminPrintScripts');
		add_action('admin_print_styles-'  . $reportsHook, 'RegSys::wpAdminPrintStyles');
		add_action('admin_print_styles-'  . $optionsHook, 'RegSys::wpAdminPrintStyles');
		add_action('admin_print_styles', 'RegSys::wpAdminMenuHideIcon');
	}
	
	static public function wpAdminMenuHideIcon()
	{
		echo '<!-- RegSys: Hide Menu Icon --><style type="text/css">li#toplevel_page_regsys div.wp-menu-image { display: none; } body.folded li#toplevel_page_regsys div.wp-menu-image { display: inherit; }</style>';
	}
	
	static public function wpAdminPrintScripts()
	{
		wp_enqueue_script('regsys-tablesorter',      plugins_url('static/jquery.tablesorter.min.js',  __FILE__), array('jquery'));
		wp_enqueue_script('regsys-tablesorter-init', plugins_url('static/jquery.tablesorter-init.js', __FILE__), array('regsys-tablesorter'));
	}
	
	static public function wpAdminPrintStyles()
	{
		wp_enqueue_style('regsys-admin-style', plugins_url('static/regsys-admin.css', __FILE__));
	}
	
	static public function wpAdminValidateOptions($input)
	{
		$options = self::getOptions();
		
		\RegSys\Entity::setDatabase(self::getDatabaseConnection());
		
		if (isset($input['currentEventID']) and \RegSys\Entity\Event::eventByID($input['currentEventID'])) {
			$options['currentEventID'] = (int) $input['currentEventID'];
		}
		
		$options['registrationTesting'] = isset($input['registrationTesting']);
		
		if (isset($input['postmarkWithin'])) {
			$options['postmarkWithin'] = (int) $input['postmarkWithin'];
		}
		else {
			$options['postmarkWithin'] = 7;
		}
		
		if (isset($input['payableTo'])) {
			$options['payableTo'] = trim($input['payableTo']);
		}
		
		if (isset($input['mailingAddress'])) {
			$options['mailingAddress'] = trim($input['mailingAddress']);
		}
		
		if (isset($input['paypalBusiness'])) {
			$options['paypalBusiness'] = trim($input['paypalBusiness']);
		}
		
		if (isset($input['paypalFee'])) {
			$options['paypalFee'] = (int) $input['paypalFee'];
		}
		
		$options['paypalSandbox'] = isset($input['paypalSandbox']);
		
		if (isset($input['paypalSandboxEmail'])) {
			$options['paypalSandboxEmail'] = trim($input['paypalSandboxEmail']);
		}
		
		if (isset($input['emailFrom'])) {
			$options['emailFrom'] = trim($input['emailFrom']);
		}
		else {
			$options['emailFrom'] = get_option('adminEmail');
		}
		
		if (isset($input['emailReplyTo'])) {
			$options['emailReplyTo'] = trim($input['emailReplyTo']);
		}
		
		if (isset($input['emailBcc'])) {
			$options['emailBcc'] = trim($input['emailBcc']);
		}
		
		$options['emailTesting'] = isset($input['emailTesting']);
		
		if (isset($input['emailTransport']) and in_array($input['emailTransport'], array('smtp', 'mail'))) {
			$options['emailTransport'] = $input['emailTransport'];
		}
		else {
			$options['emailTransport'] = 'mail';
		}
		
		if (isset($input['emailPort']) and is_numeric($input['emailPort'])) {
			$options['emailPort'] = (int) $input['emailPort'];
		}
		
		if (isset($input['emailUsername'])) {
			$options['emailUsername'] = trim($input['emailUsername']);
		}
		
		if (isset($input['emailPassword'])) {
			$options['emailPassword'] = $input['emailPassword'];
		}
		
		if (isset($input['emailEncryption']) and in_array($input['emailEncryption'], array('ssl', 'tsl', 'none'))) {
			$options['emailEncryption'] = $input['emailEncryption'];
		}
		
		return $options;
	}
	
	static public function wpHead()
	{
		# Block search engines for registration page if they are not blocked already
		if (get_option('blog_public')) {
			echo "<meta name='robots' content='noindex,nofollow' />\n";
		}
	}
	
	static public function wpPluginActivate()
	{
		if (version_compare(get_option('regsysVersion'), self::version, '<')) {
			$query = "" .
			"CREATE TABLE regsys__dancers (
				eventID int(11) unsigned NOT NULL,
				dancerID int(11) unsigned NOT NULL AUTO_INCREMENT,
				firstName varchar(100) NOT NULL,
				lastName varchar(100) NOT NULL,
				email varchar(254) NOT NULL,
				position tinyint(1) NOT NULL,
				levelID tinyint(1) unsigned NOT NULL DEFAULT '1',
				volunteer tinyint(1) NOT NULL DEFAULT '0',
				dateRegistered int(11) unsigned NOT NULL DEFAULT '0',
				discountCode varchar(255) DEFAULT NULL,
				paymentMethod enum('Mail','PayPal') DEFAULT NULL,
				paymentConfirmed tinyint(1) NOT NULL DEFAULT '0',
				paymentOwed smallint(5) unsigned NOT NULL DEFAULT '0',
				paypalFee decimal(4,2) DEFAULT NULL,
				phone varchar(50) DEFAULT NULL,
				note varchar(255) DEFAULT NULL,
				PRIMARY KEY  (dancerID)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;".
			"CREATE TABLE regsys__events (
				eventID int(11) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				dateMail int(11) unsigned NOT NULL DEFAULT '0',
				datePayPal int(11) unsigned NOT NULL DEFAULT '0',
				dateRefund int(11) unsigned NOT NULL DEFAULT '0',
				hasLevels tinyint(1) NOT NULL DEFAULT '0',
				hasVolunteers tinyint(1) NOT NULL DEFAULT '0',
				hasHousing tinyint(1) NOT NULL DEFAULT '0',
				housingNights set('Friday','Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday') NOT NULL,
				visualization tinyint(1) NOT NULL DEFAULT '1',
				visualizationColor char(7) NOT NULL DEFAULT '#333',
				volunteerDescription varchar(255) NOT NULL DEFAULT '',
				PRIMARY KEY  (eventID),
				UNIQUE KEY  `name` (`name`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;".
			"CREATE TABLE regsys__event_discounts (
				eventID int(11) unsigned NOT NULL,
				discountCode varchar(255) NOT NULL,
				discountAmount smallint(5) NOT NULL,
				discountLimit smallint(5) unsigned NOT NULL DEFAULT '0',
				discountExpires int(11) unsigned NOT NULL DEFAULT '0',
				PRIMARY KEY  (eventID,discountCode),
				UNIQUE KEY  discount_code (eventID,discountCode)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;".
			"CREATE TABLE regsys__event_levels (
				eventID int(11) unsigned NOT NULL,
				levelID tinyint(1) unsigned NOT NULL,
				levelLabel varchar(255) NOT NULL,
				hasTryouts tinyint(1) NOT NULL DEFAULT '0',
				PRIMARY KEY  (eventID,levelID),
				UNIQUE KEY  levelLabel (eventID,levelLabel)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;".
			"CREATE TABLE regsys__housing (
				eventID int(11) unsigned NOT NULL,
				dancerID int(11) unsigned NOT NULL,
				housingType tinyint(1) NOT NULL,
				housingSpotsAvailable tinyint(3) unsigned DEFAULT NULL,
				housingNights set('Friday','Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday') NOT NULL,
				housingGender tinyint(1) NOT NULL DEFAULT '3',
				housingBedtime tinyint(1) NOT NULL DEFAULT '0',
				housingPets tinyint(1) NOT NULL DEFAULT '0',
				housingSmoke tinyint(1) NOT NULL DEFAULT '0',
				housingFromScene varchar(255) DEFAULT NULL,
				housingComment text,
				PRIMARY KEY  (eventID,dancerID)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;".
			"CREATE TABLE regsys__items (
				eventID int(11) unsigned NOT NULL,
				itemID int(11) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(255) NOT NULL,
				`type` varchar(11) NOT NULL,
				pricePrereg tinyint(3) unsigned NOT NULL DEFAULT '0',
				priceDoor tinyint(3) unsigned NOT NULL DEFAULT '0',
				limitTotal smallint(5) unsigned NOT NULL DEFAULT '0',
				limitPerPosition smallint(5) unsigned NOT NULL DEFAULT '0',
				dateExpires int(11) unsigned DEFAULT NULL,
				meta enum('Position','Partner','Team Members','CrossoverJJ','Count for Classes') DEFAULT NULL,
				description varchar(255) NOT NULL DEFAULT '',
				PRIMARY KEY  (itemID),
				UNIQUE KEY  `name` (eventID,`name`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;".
			"CREATE TABLE regsys__item_prices (
				eventID int(11) unsigned NOT NULL,
				itemID int(11) unsigned NOT NULL,
				tierCount smallint(5) unsigned NOT NULL,
				tierPrice smallint(5) unsigned NOT NULL,
				PRIMARY KEY  (itemID,tierCount)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;".
			"CREATE TABLE regsys__registrations (
				eventID int(11) unsigned NOT NULL,
				dancerID int(11) unsigned NOT NULL,
				itemID int(11) unsigned NOT NULL,
				price tinyint(3) unsigned NOT NULL,
				paypalConfirmed tinyint(1) NOT NULL DEFAULT '0',
				itemMeta text,
				PRIMARY KEY  (dancerID,itemID)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta($query); # Voodoo
			
			delete_option('regsysVersion');
			add_option('regsysVersion', self::version, '', 'no');
		}
		
		$options = self::getOptions();
		
		if (empty($options['emailFrom'])) {
			$options['emailFrom'] = get_option('admin_email');
		}
		
		delete_option('regsys');
		add_option('regsys', $options, '', 'no');
	}
	
	private function __clone() {}
	private function __construct() {}
}

add_action('admin_init', 'RegSys::wpAdminInit');
add_action('admin_menu', 'RegSys::wpAdminMenu');
register_activation_hook(__FILE__, 'RegSys::wpPluginActivate');
require __DIR__ . '/vendor/autoload.php';
endif;
