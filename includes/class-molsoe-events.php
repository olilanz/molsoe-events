<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current version of the plugin.
 */
class Molsoe_Events {
	protected $plugin_name;
	protected $version;
	protected $loader;

	public function __construct() {
		$this->plugin_name = 'molsoe-events';
		$this->version = '0.9.0';

		$this->loader = $this->create_loader();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function create_loader() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-molsoe-events-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-molsoe-events-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-molsoe-events-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-molsoe-events-public.php';
		return new Molsoe_Events_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Molsoe_Events_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {
		$plugin_admin = new Molsoe_Events_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	private function define_public_hooks() {
		$plugin_public = new Molsoe_Events_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_public, 'init_shortcodes' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_receive_form', $plugin_public, 'receive_form' );
		$this->loader->add_action( 'wp_ajax_nopriv_receive_form', $plugin_public, 'receive_form' );
	}

	public function run() {
		// Run the loader to execute all of the hooks with WordPress
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_version() {
		return $this->version;
	}

	public function get_loader() {
		return $this->loader;
	}
}
