<?php
/**
 * Collabora Online WP
 *
 * @package           collabora-online-wp
 * @author            Collabora Productivity
 * @copyright         2024-2025 Collabora Productivity
 * @license           MPL-2.0
 *
 * @wordpress-plugin
 * Plugin Name: Collabora Online WP
 * Plugin URI: https://github.com/CollaboraOnline/collabora-wordpress
 * Description: WordPress connector for Collabora Online, allow you to view or edit collaboratively office documents directly from WordPress in Collabora Online.
 * Version: 0.9.10
 * License: MPL-2.0
 * License URI: https://www.mozilla.org/en-US/MPL/2.0/
 * Text Domain:       collabora-online
 *
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'COLLABORA_PLUGIN_NAME', 'collabora-online-wp' );
define( 'COLLABORA_PLUGIN_VERSION', '0.9.10' );
define( 'COLLABORA_PLUGIN_VERSION_NUM', 910 );
define( 'COLLABORA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'COLLABORA_PLUGIN_FILE', plugin_dir_path( __FILE__ ) . '/' . basename( __FILE__ ) );

register_activation_hook( __FILE__, array( 'Collabora', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Collabora', 'plugin_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'Collabora', 'plugin_uninstall' ) );

require_once COLLABORA_PLUGIN_DIR . 'includes/class-collabora.php';

/**
 * Run the plugin
 */
function collabora_run() {
	$plugin = new Collabora();
}

collabora_run();
