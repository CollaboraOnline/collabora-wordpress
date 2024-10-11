<?php
/*
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

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

    public function __construct() {
        $this->version = COOL_PLUGIN_VERSION;
        $this->plugin_name = COOL_PLUGIN_NAME;
        $this->load_deps();
        $this->init_plugin();
    }

    public static function plugin_activation() {
    }

    public static function plugin_deactivation() {
    }

    public static function plugin_uninstall() {
        $this->plugin_admin->delete_settings();
    }

    public function load_deps() {
        require_once COOL_PLUGIN_DIR . 'includes/class-collabora-admin.php';
        require_once COOL_PLUGIN_DIR . 'includes/class-collabora-wopi.php';
        require_once COOL_PLUGIN_DIR . 'includes/class-collabora-frontend.php';
    }

    public function init_plugin() {
        $this->plugin_admin = new CollaboraAdmin();
        add_action( 'admin_menu', array( $this->plugin_admin, 'admin_menu' ) );
        add_action( 'admin_init', array( $this->plugin_admin, 'admin_init' ) );
        add_action( 'rest_api_init', array( CollaboraWopi::class, 'register_routes' ) );

        $this->frontend = new CollaboraFrontend();
        add_action( 'init', array( $this->frontend, 'shortcodes_init' ) ) ;
        add_action( 'wp_enqueue_scripts', array( $this->frontend, 'enqueue_scripts' ) );
    }

    public function run() {
    }
}

?>
