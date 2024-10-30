<?php
/** COOL WordPress frontend
 *
 * @package collabora-wordpress
 */

/**
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/** The Collabora frontend */
class CollaboraFrontend {
	/** Initialise the shortcodes */
	public function shortcodes_init() {
		add_shortcode( 'cool', array( $this, 'cool_shortcode' ) );
	}

	/** Wp_enqueue_script hook. */
	public function enqueue_scripts() {
		wp_enqueue_script( COOL_PLUGIN_NAME . '-cool-js', plugins_url( 'public/js/cool.js', COOL_PLUGIN_FILE ), array(), COOL_PLUGIN_VERSION_NUM, false );
		wp_enqueue_style( COOL_PLUGIN_NAME . '-cool-css', plugins_url( 'public/css/cool.css', COOL_PLUGIN_FILE ), array(), COOL_PLUGIN_VERSION_NUM, false );
	}

	/**
	 * Shortcode implementation
	 *
	 * @param array $atts Attributes.
	 * @param any   $content The content.
	 */
	public function cool_shortcode( $atts = array(), $content = null ) {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		if ( ! isset( $atts['id'] ) ) {
			return '<p>Error: file id is missing</p>';
		}
		$id   = $atts['id'];
		$mode = 'view';
		if ( isset( $atts['mode'] ) ) {
			$mode = $atts['mode'];
		}
		switch ( $mode ) {
			case 'view':
				if ( current_user_can( 'read_post', $id ) ) {
					return self::get_view_render( $id, false );
				}
				break;
			case 'edit':
				if ( current_user_can( 'edit_post', $id ) ) {
					return self::get_view_render( $id, true );
				}
				break;
			case 'button':
				return self::get_button( $id );
			default:
				return '<p>Invalid mode: ' . esc_html( $mode ) . '</p>';
		}
		return '<p>Unauthorized</p>';
	}

	/**
	 * Output the button for the short code button mode
	 *
	 * @param string $id The post id of the document.
	 */
	public static function get_button( string $id ) {
		wp_enqueue_script( COOL_PLUGIN_NAME . '-cool-previewer-js', plugins_url( 'public/js/previewer.js', COOL_PLUGIN_FILE ), array(), COOL_PLUGIN_VERSION_NUM, false );

		$filename = get_attached_file( $id );
		$name     = pathinfo( $filename, PATHINFO_BASENAME );
		// XXX localize.
		return '<p>Attachment "' . esc_html( $name ) . '" <button onclick="previewField(\'' .
			esc_url( CoolUtils::get_editor_url( $id ) ) . '\');">Edit</button></p>' .
			'<dialog id="cool-editor__dialog" class="cool-editor__dialog">' .
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
	 * @param bool       $can_write Whether we want write permission (editor vs view).
	 * @param null|array $options COOL frame options.
	 *
	 * @return string Markup to display.
	 */
	public static function get_view_render( int $id, bool $can_write, $options = null ) {
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

		$access_token = CoolUtils::token_for_file_id( $id, (int) $ttl, $can_write );
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
