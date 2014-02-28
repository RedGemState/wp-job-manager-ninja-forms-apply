=== WP Job Manager - Apply with Ninja Forms ===

Author URI: http://astoundify.com
Plugin URI: https://github.com/Astoundify/wp-job-manager-ninja-forms-apply/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=contact@appthemer.com&item_name=Donation+for+Astoundify WP Job Manager Ninja Forms
Contributors: spencerfinnell
Tags: job, job listing, job apply, wp job manager, job manager, ninja forms, ninja, forms
Requires at least: 3.5
Tested up to: 3.8
Stable Tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Allow themes using the WP Job Manager plugin to apply via a Ninja Form.

== Description ==

Apply directly to Jobs (and Resumes if using Resume Manager) via a custom Ninja Form. Use any available Ninja Form fields to build a completely custom submission form.

= Where can I use this? =

Astoundify has released the first fully integrated WP Job Manager theme. Check out ["Jobify"](http://themeforest.net/item/jobify-job-board-wordpress-theme/5247604?ref=Astoundify)

== Frequently Asked Questions ==

= The form does not appear in my theme =

It is up to the theme to respect your choice to use this plugin (as there is no way to automatically insert the form). The easiest way is to output a shortcode with the form ID that has been specified.

`echo do_shortcode( sprintf( '[ninja_forms_display_form id="%s"]', get_option( 'job_manager_job_apply' ) );`

= I do not receive an email =

You **must** create a *hidden* field with the following specific settings:

* **Label:** `application_email`
* **Default Value:** `Post/Page ID`

[View an image of the settings](https://i.cloudup.com/pnfVzYBFiN.png)

== Installation ==

1. Install and Activate
2. Go to "Job Listings > Settings" and enter select the form you would like from the dropdown.

== Changelog ==

= 1.0: March 2, 2014 =

* First official release!
