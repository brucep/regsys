RegSys
======

An event registration system for dance organizations.


Requirements
------------

* PHP 5.3.2+
* MySQL 5.0.3+
* WordPress 2.7.0+
* [Composer](http://getcomposer.org/)

Note: The [WordPress database table prefix](prefix) is not supported. If you rely on the prefix to run multiple WordPress installs in a single database, then you will not be able to have separate data for each install.


Installation
------------

1. Install/update dependencies via Composer.
2. Activate the plugin in WordPress. (This will create/update the database tables.)


Integrating the Registration Form With Your Theme
-------------------------------------------------

The short answer:

 1. Call `RegSys::registrationHead()` prior to calling `get_header` to enqueue stylesheets and scripts.
 2. Call `RegSys::registrationForm()` from where you want the registration form to appear.

The simplest way, if you are using your default header and footer:

 1. Copy `page-register.php` to your theme's folder.
 2. If the [slug](slug) for your registration page is not `register`, then rename the new file to match the appropriate slug.

If you are using a [Custom Page Template](template) that does not use your default header and footer (e.g., for multiple pages in a sub-section):

 * Add a [Custom Field](meta) named `regsysRegistrationForm` with a value of `true` to prevent the default header and footer from being used.
 * Use `get_post_meta` to test for `regsysRegistrationForm` and call the previously mentioned methods as appropriate.


### Styling the Registration Form

If your theme has a stylesheet named `style-regsys.css`, it will be included automatically along with this plugin's default stylesheet.

If you want to use your own stylesheet without including the plugin's default stylesheet, use `style-regsys-override.css` instead.


Checklist for a New Event
-------------------------

 1. Create a new Event. `Registration Reports > Add New Event`

 2. Create Items for the Event. `Registration Reports > [Your New Event] > Items > Add New Item`

 3. Update Registration Options:

	1. Change `Current Event` to match the new Event.

	2. **Check the `Registration Testing` checkbox.**

	3. Review other options and update as needed.

 4. Review registration form appearance and functionality as desired. (You have to be logged in to see the form while `Registration Testing` is enabled.)

 5. When you are *ready for the registration form to be public*, uncheck the `Registration Testing` checkbox.


Miscellaneous Notes
-------------------

### Events

If you are taking in more housing requests than you can handle, then you can switch from the third to the second option for `Housing Support`.
This will disable requests for needing housing while still allowing people to sign up to provide housing.


### Items

The `Needs Additional Info?` field:

 * The various `Requires …` options will cause additional fields to appear on the registration form (for competitions only).

 * If part of your event consists of a workshop/classes, you should flag any
   package that would include classes with the `Package has Classes` option.
   This allows for better tracking of class-specific lead/follow numbers
   (i.e., you can exclude anyone registered for a dancing-only package).


### Reports

The reports are visible to Administrators and Editors.

The Registration List report can (should) be used to verify that people paying via check have paid the right amount.
Payments from PayPal are automatically confirmed via [Instant Payment Notification](https://www.paypal.com/ipn).


### PayPal IPN Confirmation and Loading WordPress

There is a line in the plugin file `confirm-paypal.php` that hardcodes the path to the WordPress file `wp-load.php`.
The default value assumes that the `wp-content` folder has been [moved outside](wp-content) of the WordPress directory.
It also assumes that the WordPress directory itself has been renamed to `wp`.
If either of these assumptions are not correct, then you will have to edit the path manually.


Thank you for reading, *namaste*, and good luck.


[prefix]:     http://codex.wordpress.org/Creating_Tables_with_Plugins#Database_Table_Prefix
[slug]:       http://codex.wordpress.org/Template_Hierarchy#Page_display
[template]:   http://codex.wordpress.org/Page_Templates#Custom_Page_Template
[meta]:       http://codex.wordpress.org/Custom_Fields
[wp-content]: http://codex.wordpress.org/Editing_wp-config.php#Moving_wp-content_folder
