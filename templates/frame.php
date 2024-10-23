<?php
/** COOL frame embedded template
 *
 * @package collabora-wordpress
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
	<link rel="stylesheet" id="collabora-wordpress-cool-css" href="<?php echo esc_url( $args['base_url'] ); ?>/public/css/cool.css" media="all">
<?php // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script id="collabora-wordpress-cool-js" src="<?php echo esc_url( $args['base_url'] ); ?>/public/js/cool.js"></script>
<?php // phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
</head>
<body>
	<?php echo balanceTags( $args['frame'] ); ?>
</body>
</html>
