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
		'frame'    => array(),
	)
);

$params = $args['frame'];

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
<body class="collabora-editor__body">

<div class="collabora-frame">
	<div style="display: none">
	<form action="" enctype="multipart/form-data" method="post" target="collabora-online-viewer" id="collabora-submit-form">
		<input name="access_token" value="<?php echo esc_attr( $params['accessToken'] ); ?>" type="hidden" />
		<input name="access_token_ttl" value="<?php echo esc_attr( $params['accessTokenTtl'] ); ?>" type="hidden" />
		<input type="submit" value="" />
	</form>
	</div>

	<iframe id="collabora-online-viewer" name="collabora-online-viewer" class="collabora-frame__iframe" style="<?php echo esc_attr( $params['iFrameStyle'] ); ?>" allow="clipboard-read *; clipboard-write *">
	</iframe>
	<script type="text/ecmascript">

	let closebutton = '<?php echo 'true' === $params['closebutton'] ? 'true' : 'false'; ?>';
	let options = null;
	if (closebutton == 'true') {
		options = { closebutton: true };
	}
	loadDocument('<?php echo esc_url( $params['wopiClient'] ); ?>', '<?php echo esc_attr( $params['wopiSrc'] ); ?>', options);
	</script>
</div>

</body>
</html>
