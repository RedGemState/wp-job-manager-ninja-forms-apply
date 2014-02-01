<?php
/**
 * Plugin Name: WP Job Manager - Apply With Ninja Forms
 * Plugin URI:  https://github.com/Astoundify/wp-job-manager-ninja-forms-apply/
 * Description: Apply to jobs that have added an email address via Ninja Forms
 * Author:      Astoundify, JustinSainton
 * Author URI:  http://astoundify.com
 * Version:     1.1.1
 * Text Domain: job_manager_ninja_apply
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) )  {
	exit;
}

class Astoundify_Job_Manager_Apply_Ninja {

	/**
	 * @var $instance
	 */
	private static $instance;

	/**
	 * @var $jobs_form_id
	 */
	private $jobs_form_id;

	/**
	 * @var $resumes_form_id
	 */
	private $resumes_form_id;

	/**
	 * Make sure only one instance is only running.
	 */
	public static function get_instance() {
		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Start things up.
	 *
	 * @since WP Job Manager - Apply with Gravity Forms 1.0
	 */
	public function __construct() {
		$this->jobs_form_id    = get_option( 'job_manager_ninja'        , 0 );
		$this->resumes_form_id = get_option( 'job_manager_ninja_resumes', 0 );

		$this->setup_actions();
		$this->setup_globals();
		$this->load_textdomain();
	}

	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @since WP Job Manager - Apply with Gravity Forms 1.0
	 *
	 * @return void
	 */
	private function setup_globals() {
		$this->file       = __FILE__;

		$this->basename   = apply_filters( 'job_manager_ninja_apply_plugin_basenname', plugin_basename( $this->file ) );
		$this->plugin_dir = apply_filters( 'job_manager_ninja_apply_plugin_dir_path' , plugin_dir_path( $this->file ) );
		$this->plugin_url = apply_filters( 'job_manager_ninja_apply_plugin_dir_url  ', plugin_dir_url ( $this->file ) );

		$this->lang_dir   = apply_filters( 'job_manager_ninja_apply_lang_dir', trailingslashit( $this->plugin_dir . 'languages' ) );
		$this->domain     = 'job_manager_ninja_apply';
	}

	/**
	 * Loads the plugin language files
	 *
 	 * @since WP Job Manager - Apply with Gravity Forms 1.0
	 */
	public function load_textdomain() {
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . $this->domain . '/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( $this->domain, $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			return load_textdomain( $this->domain, $mofile_local );
		}

		return false;
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since WP Job Manager - Apply with Gravity Forms 1.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		add_filter( 'job_manager_settings'    , array( $this, 'job_manager_settings' ) );
		add_action( 'ninja_forms_post_process', array( $this, 'notification_email' ) );
		add_action( 'ninja_forms_post_process', array( $this, 'remove_email_filter' ), 99 );
	}

	/**
	 * Add a setting in the admin panel to enter the ID of the Gravity Form to use.
	 *
	 * @since WP Job Manager - Apply with Gravity Forms 1.0
	 *
	 * @param array $settings
	 * @return array $settings
	 */
	public function job_manager_settings( $settings ) {
		$settings[ 'job_listings' ][1][] = array(
			'name'    => 'job_manager_ninja',
			'std'     => null,
			'type'    => 'select',
			'options' => self::get_forms(),
			'label'   => __( 'Jobs Ninja Form', 'job_manager_ninja_apply' ),
			'desc'    => __( 'The Ninja Form you created for contacting employers.', 'job_manager_ninja_apply' ),
		);

		if ( class_exists( 'WP_Resume_Manager' ) ) {
			$settings[ 'job_listings' ][1][] = array(
				'name'  => 'job_manager_ninja_resumes',
				'std'   => null,
				'type'    => 'select',
				'options' => self::get_forms(),
				'label' => __( 'Resumes Ninja Form', 'job_manager_ninja_apply' ),
				'desc'  => __( 'The Ninja Form you created for contacting employees.', 'job_manager_ninja_apply' ),
			);
		}

		return $settings;
	}

	private static function get_forms() {

		$forms = array( 0 => __( 'Please select a form', 'job_manager_ninja_apply' ) );

		$_forms = ninja_forms_get_all_forms();

		if ( ! empty( $_forms ) ) {

			foreach ( $_forms as $_form ) {
				$forms[ $_form['id'] ] = $_form['data']['form_title'];
			}
		}

		return $forms;
	}

	/**
	 * Set the notification email when sending an email.
	 *
	 * @since WP Job Manager - Apply with Gravity Forms 1.0
	 *
	 * @return string The email to notify.
	 */
	public function notification_email() {

		if ( ! is_singular( array( 'resume', 'job_listing' ) ) ) {
			return;
		}

		global $ninja_forms_processing;

		$form_id = $ninja_forms_processing->get_form_ID();

		if ( $form_id !== absint( $this->jobs_form_id ) && $form_id !== absint( $this->resumes_form_id ) ) {
			return;
		}

		global $post, $_proper_ninja_email;

		$_proper_ninja_email = $form_id == $this->jobs_form_id ? $post->_application : $post->_candidate_email;

		add_filter( 'wp_mail', array( $this, 'proper_email' ) );
	}

	function proper_email( $mail ) {
		global $_proper_ninja_email;

		if ( filter_var( $_proper_ninja_email, FILTER_VALIDATE_EMAIL ) ) {
			$mail['to'] = $_proper_ninja_email;
		}

		return $mail;
	}

	function remove_email_filter() {
		remove_filter( 'wp_mail', array( $this, 'proper_email' ) );
	}
}

add_action( 'init', array( 'Astoundify_Job_Manager_Apply_Ninja', 'get_instance' ) );