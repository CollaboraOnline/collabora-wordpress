<?php
/** COOL document frame permalink
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

// This is assume the standard plugin installation on WordPress.
// This file is in `wp-content/plugins/collabora-online-wp`.
require_once __DIR__ . '/../../../wp-load.php';

if ( ! isset( $_GET['id'] ) ) {
	die( 'No id passed' );
}

$cool_mode = 'view';
if ( isset( $_GET['mode'] ) ) {
	$cool_mode = sanitize_key( wp_unslash( $_GET['mode'] ) );
}

$file_id = absint( wp_unslash( $_GET['id'] ) );

auth_redirect();

require_once __DIR__ . '/collabora-online-wp.php';
require_once __DIR__ . '/includes/class-collaborafrontend.php';

CollaboraFrontend::output_frame( $file_id, $cool_mode, false );
