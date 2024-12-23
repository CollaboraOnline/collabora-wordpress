<?php
/** COOL frame embedded
 *
 * @package collabora-online-wp
 */

/**
 * Copyright the Collabora Online contributors.
 *
 * SPDX-License-Identifier: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) ) {
	$my_path = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( empty( $my_path[0] ) ) {
		die( 'URL error' );
	}
	require_once $my_path[0] . 'wp-load.php';
} else {
	die( 'Can\'t load WordPress.' );
}

if ( ! isset( $_GET['id'] ) ) {
	die( 'No id passed' );
}

$cool_mode = 'view';
if ( isset( $_GET['mode'] ) ) {
	$cool_mode = sanitize_key( wp_unslash( $_GET['mode'] ) );
}

$file_id = absint( wp_unslash( $_GET['id'] ) );

require_once __DIR__ . '/collabora-online-wp.php';
require_once __DIR__ . '/includes/class-collaborafrontend.php';

$base_url = plugins_url( '', COOL_PLUGIN_FILE );

load_template(
	__DIR__ . '/templates/frame.php',
	true,
	array(
		'frame'    => CollaboraFrontend::get_view_render( $file_id, $cool_mode, array( 'closebutton' => 'true' ) ),
		'base_url' => $base_url,
	)
);
