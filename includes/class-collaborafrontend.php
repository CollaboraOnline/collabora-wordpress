<?php
/** COOL WordPress frontend
 *
 * @package collabora-wordpress
 */

/* Block. This will register the block automatically. */
require_once COOL_PLUGIN_DIR . 'cool-block/cool-block.php';

/**
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/** The Collabora frontend */
class CollaboraFrontend {
	/** Initialise the shortcodes and block editor. */
	public function init() {
		add_shortcode( 'cool', array( $this, 'cool_shortcode' ) );

		add_filter( 'teeny_mce_buttons', array( $this, 'cool_shortcode_button' ) );
		add_filter( 'mce_buttons', array( $this, 'cool_shortcode_button' ) );
		add_filter( 'mce_external_plugins', array( $this, 'cool_tinymce_js' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'cool_tinymce_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'cool_tinymce_css' ) );
	}

	/** Wp_enqueue_script hook. */
	public function enqueue_scripts() {
		wp_enqueue_style( COOL_PLUGIN_NAME . '-cool-css', plugins_url( 'assets/css/cool.css', COOL_PLUGIN_FILE ), array(), COOL_PLUGIN_VERSION_NUM, false );
	}

	/**
	 * Queue the style for tinymce.
	 */
	public function cool_tinymce_css() {
		wp_enqueue_style( COOL_PLUGIN_NAME . '-cool-tinymce', plugins_url( 'editor/cool-tinymce.css', COOL_PLUGIN_FILE ), array(), COOL_PLUGIN_VERSION_NUM, false );
	}

	/**
	 * Load the TinyMCE plugin hook.
	 *
	 * @param array $plugins The plugins array.
	 */
	public function cool_tinymce_js( array $plugins ) {
		$plugins['cool-shortcode-button'] = plugins_url( 'editor/cool-tinymce.js', COOL_PLUGIN_FILE );

		return $plugins;
	}

	/**
	 * Add the TinyMCE button hook.
	 *
	 * @param array $buttons The buttons array.
	 */
	public function cool_shortcode_button( array $buttons ) {
		array_push( $buttons, 'cool-shortcode-button' );

		return $buttons;
	}

	/**
	 * Shortcode implementation
	 *
	 * @param array  $atts Attributes.
	 * @param any    $content The content.
	 * @param string $name The short code name. We expect 'cool'.
	 */
	public function cool_shortcode( $atts, $content, $name ) {
		if ( 'cool' !== $name ) {
			die( 'Incorrect short code' );
		}

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		if ( ! isset( $atts['id'] ) ) {
			return '<p>' . __( 'Error: file id is missing', 'collabora-wordpress' ) . '</p>';
		}
		$id   = $atts['id'];
		$mode = 'view';
		if ( isset( $atts['mode'] ) ) {
			$mode = $atts['mode'];
		}
		return self::get_button_markup( $id, $mode );
	}

	/**
	 * Get the button markup
	 *
	 * @param string $id The post id of the document.
	 * @param string $mode The mode.
	 */
	public static function get_button_markup( string $id, string $mode ) {
		switch ( $mode ) {
			case 'view':
				if ( current_user_can( 'read_post', $id ) ) {
					return self::get_button( $id, false );
				}
				break;
			case 'edit':
				if ( current_user_can( 'edit_post', $id ) ) {
					return self::get_button( $id, true );
				}
				break;
			default:
				// translators: %s is the mode.
				$message = sprintf( __( 'Invalid mode: %s', 'collabora-wordpress' ), $mode );
				return '<p>' . esc_html( $message ) . '</p>';
		}
		return '<p>' . esc_html( __( 'You don\'t have permission to view the attached file.', 'collabora-wordpress' ) ) . '</p>';
	}

	/**
	 * Output the button for the short code button mode
	 *
	 * @param string $id The post id of the document.
	 * @param bool   $want_write Whether the user want to write the file.
	 */
	private static function get_button( string $id, bool $want_write ) {
		wp_enqueue_script( COOL_PLUGIN_NAME . '-cool-previewer-js', plugins_url( 'assets/js/previewer.js', COOL_PLUGIN_FILE ), array(), COOL_PLUGIN_VERSION_NUM, false );

		$filename = get_attached_file( $id );
		$name     = pathinfo( $filename, PATHINFO_BASENAME );
		if ( true === $want_write ) {
			$label = __( 'Edit', 'collabora-wordpress' );
		} else {
			$label = __( 'View', 'collabora-wordpress' );
		}
		// translators: %s is the name of the attachment.
		$attachment = sprintf( __( 'Attachment "%s"', 'collabora-wordpress' ), $name );
		// XXX localize.
		return '<p>' . esc_html( $attachment ) . ' <button onclick="previewField(\'' .
			esc_url( CoolUtils::get_editor_url( $id, $want_write ) ) . '\');">' . $label . '</button></p>' .
			'<dialog id="cool-editor__dialog" class="cool-editor__dialog alignfull">' .
			'<iframe class="cool-frame__preview"></iframe>' .
			'</dialog>';
	}

	/**
	 * Output the COOL frame
	 *
	 * @param array $params Parameters for the frame.
	 *
	 * @return string Markup to display.
	 */
	public static function cool_frame( array $params ) {
		wp_enqueue_script( COOL_PLUGIN_NAME . '-cool-js', plugins_url( 'assets/js/cool.js', COOL_PLUGIN_FILE ), array(), COOL_PLUGIN_VERSION_NUM, false );

		$closebutton = 'true' === $params['closebutton'] ? 'true' : 'false';
		$wopi_src    = $params['wopiSrc'];
		$wopi_client = $params['wopiClient'];

		return '
<div class="cool-frame">
  <div style="display: none">
    <form action="" enctype="multipart/form-data" method="post" target="collabora-online-viewer" id="collabora-submit-form">
      <input name="access_token" value="' . $params['accessToken'] . '" type="hidden" />
      <input name="access_token_ttl" value="' . $params['accessTokenTtl'] . '" type="hidden" />
      <input type="submit" value="" />
    </form>
  </div>

  <iframe id="collabora-online-viewer" name="collabora-online-viewer" class="cool-frame__iframe" style="' . $params['iFrameStyle'] . '" allow="clipboard-read *; clipboard-write *">
  </iframe>
  <script type="text/ecmascript">' .

		" let closebutton = '$closebutton';
    let options = null;
    if (closebutton == 'true') {
        options = { closebutton: true };
    }
    loadDocument('$wopi_client', '$wopi_src', options);" .

		'  </script>
</div>';
	}

	/**
	 * Output the a view for a COOL frame
	 *
	 * @param int        $id The document id.
	 * @param bool       $want_write Whether we want write permission (editor vs view).
	 * @param null|array $options COOL frame options.
	 *
	 * @return string Markup to display.
	 */
	public static function get_view_render( int $id, bool $want_write, $options = null ) {
		require_once COOL_PLUGIN_DIR . 'includes/class-coolrequest.php';

		$wopi_base = get_option( CollaboraAdmin::COOL_WOPI_BASE );

		$req         = new CoolRequest();
		$wopi_client = $req->get_wopi_client_url();
		if ( null === $wopi_client ) {
			return '<p>' .
				__( 'The Collabora Online server is not available: ', 'collabora-wordpress' ) .
				esc_html( $req->error_string() ) .
				'</p>';
		}

		$ttl = 0;
		if ( 0 === $ttl ) {
			$ttl = 86400;
		}
		$ttl += gettimeofday( true );

		$access_token = CoolUtils::token_for_file_id( $id, (int) $ttl, $want_write );
		$closebutton  = 'false';

		if ( $options ) {
			if ( isset( $options['closebutton'] ) && 'true' === $options['closebutton'] ) {
				$closebutton = 'true';
			}
		}

		return self::cool_frame(
			array(
				'wopiClient'     => $wopi_client,
				'wopiSrc'        => rawurlencode( $wopi_base . '/wp-json/' . CollaboraWopi::COLLABORA_ROUTE_NS . '/wopi/files/' . $id ),
				'accessToken'    => $access_token,
				'accessTokenTtl' => $ttl * 1000, // It's in usec. The JWT is in sec.
				'closebutton'    => $closebutton,
				'iFrameStyle'    => '',
			)
		);
	}
}
