<?php
/** COOL frame embedded template
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$args = wp_parse_args(
	$args,
	array(
		'base_url' => '',
		'frame'    => '',
	)
);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
<?php // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<?php // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<link rel="stylesheet" id="collabora-online-wp-cool-css" href="<?php echo esc_url( $args['base_url'] ); ?>/public/css/cool.css" media="all">
	<script id="collabora-online-wp-cool-js" src="<?php echo esc_url( $args['base_url'] ); ?>/public/js/cool.js"></script>
<?php // phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
<?php // phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
</head>
<body class="cool-editor__body">
	<?php
	// 'frame' is a template parameter. And where we call the template, it's content has
	// been sanitized.
	// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $args['frame'];
	// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</body>
</html>
