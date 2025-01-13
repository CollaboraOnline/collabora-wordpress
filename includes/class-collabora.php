<?php
/** COOL WordPress plugin
 *
 * @package collabora-online-wp
 */

/**
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/** The main plugin */
class Collabora {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The admin section of the plugin.
	 *
	 * @var CollaboraAdmin $plugin_admin The administrative interfce.
	 */
	protected $plugin_admin;

	/**
	 * The fronted of the plugin.
	 *
	 * @var CollaboraFrontend $frontend The frontend
	 */
	protected $frontend;

	/**
	 * Plugin constructor
	 */
	public function __construct() {
		$this->version     = COLLABORA_PLUGIN_VERSION;
		$this->plugin_name = COLLABORA_PLUGIN_NAME;
		$this->load_deps();
		$this->init_plugin();
	}

	/**
	 * Activation hook
	 */
	public static function plugin_activation() {
	}

	/**
	 * Deactivation hook
	 */
	public static function plugin_deactivation() {
	}

	/**
	 * Uninstall hook
	 */
	public static function plugin_uninstall() {
		$this->plugin_admin->delete_settings();
	}

	/**
	 * Load the dependencies
	 */
	public function load_deps() {
		require_once COLLABORA_PLUGIN_DIR . 'includes/class-collaboraadmin.php';
		require_once COLLABORA_PLUGIN_DIR . 'includes/class-collaborawopi.php';
		require_once COLLABORA_PLUGIN_DIR . 'includes/class-collaborafrontend.php';
	}

	/**
	 * Load the translations.
	 */
	public static function load_i18n() {
		load_plugin_textdomain(
			'collabora-online',
			false,
			COLLABORA_PLUGIN_DIR . '/languages/'
		);
	}

	/**
	 * Plugin initialisation
	 */
	public function init_plugin() {
		add_action( 'init', array( self::class, 'load_i18n' ) );

		$this->plugin_admin = new CollaboraAdmin();
		add_action( 'admin_menu', array( $this->plugin_admin, 'admin_menu' ) );
		add_action( 'admin_init', array( $this->plugin_admin, 'admin_init' ) );
		add_action( 'init', array( CollaboraWopi::class, 'init' ) );
		add_action( 'rest_api_init', array( CollaboraWopi::class, 'register_routes' ) );

		$this->frontend = new CollaboraFrontend();
		add_action( 'init', array( $this->frontend, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_scripts' ) );
	}
}
