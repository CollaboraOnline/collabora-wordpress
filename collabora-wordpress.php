<?php
/*
 * @package           collabora-wordpres
 * @author            Collabora Productivity
 * @copyright         2024 Collabora Productivity
 * @license           MPL-2.0
 *
 * @wordpress-plugin
 * Plugin Name: Collabora Online for Wordpress
 * Plugin URI: https://github.com/hfiguiere/collabora-wordpress
 * Description: Wordpress connector for Collabora Online, allow you to view or edit collaboratively office documents directly from Wordpress in Collabora Online.
 * Version: 0.1.0
 * License: MPL-2.0
 * License URI: https://www.mozilla.org/en-US/MPL/2.0/
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

define( 'COOL_PLUGIN_NAME', 'collabora-wordpress' );
define( 'COOL_PLUGIN_VERSION', '0.1.0' );
define( 'COOL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'Collabora', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Collabora', 'plugin_deactivation' ) );
register_uninstall_hook( __FILE__, array ( 'Collabora', 'plugin_uninstall' ) );

require_once COOL_PLUGIN_DIR . 'includes/class-collabora.php';
if ( is_readable( COOL_PLUGIN_DIR . '/vendor/autoload.php' ) ) {
    require_once COOL_PLUGIN_DIR . 'vendor/autoload.php';
}

function run_collabora() {
    $plugin = new Collabora();
    $plugin->run();
}

run_collabora();
?>
