<?php
/** COOL button embedded template
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
		'id'         => 0,
		'want_write' => false,
		'attachment' => '',
		'label'      => '',
	)
);

wp_enqueue_script( COLLABORA_PLUGIN_NAME . '-cool-previewer-js', plugins_url( 'public/js/previewer.js', COLLABORA_PLUGIN_FILE ), array(), COLLABORA_PLUGIN_VERSION_NUM, false );
?>

<p>
	<?php echo esc_html( $args['attachment'] ); ?>
	<button onclick="previewField('<?php echo esc_url( CollaboraUtils::get_editor_url( $args['id'], $args['want_write'] ) ); ?>');">
	<?php echo esc_html( $args['label'] ); ?>

	</button>
</p>
<dialog id="collabora-editor__dialog" class="collabora-editor__dialog alignfull">
	<iframe class="collabora-frame__preview"></iframe>
</dialog>
