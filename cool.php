<?php
/*
 * Copyright the Collabora Online contributors.
 *
 * SPDX-License-Identifier: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if ( !empty( $_SERVER['SCRIPT_FILENAME'] ) ) {
	$path = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
    if ( empty( $path[0] ) ) {
        die( 'URL error' );
    }
	require_once $path[0] . 'wp-load.php';
} else {
    die( 'Can\'t load Wordpress.' );
}

$id = $_GET['id'];

require_once __DIR__ . '/collabora-wordpress.php';
require_once __DIR__ . '/includes/class-collabora-frontend.php';

$base_url = plugins_url( '', COOL_PLUGIN_FILE );

load_template(
	__DIR__ . '/templates/frame.php',
	true,
	array(
        'frame' => CollaboraFrontend::get_view_render( $id, false, array( 'closebutton' => 'true' ) ),
        'base_url' => $base_url,
	)
);


?>
