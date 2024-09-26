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
    }

    public static function plugin_activation() {
    }

    public static function plugin_deactivation() {
    }

    public static function plugin_uninstall() {
        delete_site_option( 'cool_server' );
        delete_site_option( 'cool_wopi_base' );
        delete_site_option( 'cool_unsecure_ssl_cert' );
    }

    public static function init() {

    }

    public function run() {
    }
}

?>
