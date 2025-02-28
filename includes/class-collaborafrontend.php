<?php
/** COOL WordPress frontend
 *
 * @package collabora-online-wp
 */

/**
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* Block. This will register the block automatically. */
require_once COLLABORA_PLUGIN_DIR . 'cool-block/cool-block.php';

/** The Collabora frontend */
class CollaboraFrontend {

	/** The shortcode */
	const SHORTCODE = 'collabora_online';

	/** Initialise the shortcodes and block editor. */
	public function init() {
		add_shortcode( self::SHORTCODE, array( $this, 'cool_shortcode' ) );

		add_filter( 'teeny_mce_buttons', array( $this, 'cool_shortcode_button' ) );
		add_filter( 'mce_buttons', array( $this, 'cool_shortcode_button' ) );
		add_filter( 'mce_external_plugins', array( $this, 'cool_tinymce_js' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'cool_tinymce_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'cool_tinymce_css' ) );
	}

	/** Wp_enqueue_script hook. */
	public function enqueue_scripts() {
		wp_enqueue_style( COLLABORA_PLUGIN_NAME . '-cool-css', plugins_url( 'public/css/cool.css', COLLABORA_PLUGIN_FILE ), array(), COLLABORA_PLUGIN_VERSION_NUM, false );
	}

	/**
	 * Queue the style for tinymce.
	 */
	public function cool_tinymce_css() {
		wp_enqueue_style( COLLABORA_PLUGIN_NAME . '-cool-tinymce', plugins_url( 'editor/cool-tinymce.css', COLLABORA_PLUGIN_FILE ), array(), COLLABORA_PLUGIN_VERSION_NUM, false );
	}

	/**
	 * Load the TinyMCE plugin hook.
	 *
	 * @param array $plugins The plugins array.
	 */
	public function cool_tinymce_js( array $plugins ) {
		$plugins['collabora-shortcode-button'] = plugins_url( 'editor/cool-tinymce.js', COLLABORA_PLUGIN_FILE );

		return $plugins;
	}

	/**
	 * Add the TinyMCE button hook.
	 *
	 * @param array $buttons The buttons array.
	 */
	public function cool_shortcode_button( array $buttons ) {
		array_push( $buttons, 'collabora-shortcode-button' );

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
		if ( self::SHORTCODE !== $name ) {
			die( 'Incorrect short code' );
		}

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		if ( ! isset( $atts['id'] ) ) {
			return '<p>' . __( 'Error: file id is missing', 'collabora-online' ) . '</p>';
		}
		$id   = $atts['id'];
		$mode = 'view';
		if ( isset( $atts['mode'] ) ) {
			$mode = $atts['mode'];
		}
		return self::get_button_markup( $id, $mode );
	}

	/**
	 * Get the button properties
	 *
	 * @param string $id The post id of the document.
	 * @param string $mode The mode.
	 *
	 * @return array The properties.
	 */
	public static function get_button_properties( string $id, string $mode ) {
		$authorized = false;
		$message    = null;
		$label      = null;
		$attachment = null;
		switch ( $mode ) {
			case 'view':
				if ( current_user_can( 'read_post', $id ) ) {
					$authorized = true;
				}
				break;
			case 'review':
				$reviewer_role = get_option( CollaboraAdmin::COLLABORA_USER_ROLE_REVIEW );
				if ( $reviewer_role && in_array( $reviewer_role, wp_get_current_user()->roles, true ) ) {
					$authorized = true;
				} elseif ( current_user_can( 'edit_post', $id ) ) {
					$authorized = true;
				}
				break;
			case 'edit':
				if ( current_user_can( 'edit_post', $id ) ) {
					$authorized = true;
				}
				break;
		}
		if ( $authorized ) {
			$filename = get_attached_file( $id );
			$name     = pathinfo( $filename, PATHINFO_BASENAME );
			// translators: %s is the file name.
			$attachment = sprintf( __( 'Attachment "%s"', 'collabora-online' ), $name );

			switch ( $mode ) {
				case 'edit':
					$label = __( 'Edit', 'collabora-online' );
					break;
				case 'review':
					$label = __( 'Review', 'collabora-online' );
					break;
				case 'view':
					$label = __( 'View', 'collabora-online' );
					break;
			}
		} else {
			// translators: %s is just the URL to the login page. Please preserve markup.
			$message = sprintf( __( 'Please <a href="%s">login</a> to access the attachment.', 'collabora-online' ), wp_login_url( get_permalink() ) );
		}
		return array(
			'mode'       => $mode,
			'message'    => $message,
			'authorized' => $authorized,
			'label'      => $label,
			'attachment' => $attachment,
		);
	}

	/**
	 * Get the button markup
	 *
	 * @param string $id The post id of the document.
	 * @param string $mode The mode.
	 *
	 * @return string Button markup to display.
	 */
	public static function get_button_markup( string $id, string $mode ) {
		$props = self::get_button_properties( $id, $mode );
		if ( null !== $props['message'] ) {
			return '<p>' . wp_kses_post( $props['message'] ) . '</p>';
		}
		if ( ! $props['authorized'] ) {
			return sprintf(
				'<p>%s</p>',
				esc_html( __( 'You don\'t have permission to view the attached file.', 'collabora-online' ) )
			);
		}
		return self::get_button( $id, $props );
	}

	/**
	 * Output the button for the short code button mode
	 *
	 * @param string $id The post id of the document.
	 * @param array  $props The properties.
	 *
	 * @return string Button markup to display.
	 */
	private static function get_button( string $id, array $props ) {
		wp_enqueue_script( COLLABORA_PLUGIN_NAME . '-cool-previewer-js', plugins_url( 'public/js/previewer.js', COLLABORA_PLUGIN_FILE ), array(), COLLABORA_PLUGIN_VERSION_NUM, false );

		return sprintf(
			'<p>%s <button onclick="previewField(\'%s\');">%s</button> <a href="%s">%s</a></p>' .
			'<dialog id="collabora-editor__dialog" class="collabora-editor__dialog alignfull">' .
			'<iframe class="collabora-frame__preview"></iframe>' .
				'</dialog>',
			esc_html( $props['attachment'] ),
			esc_url( CollaboraUtils::get_editor_url( $id, $props['mode'] ) ),
			esc_html( $props['label'] ),
			esc_url( CollaboraUtils::get_permalink( $id, $props['mode'] ) ),
			esc_html( '#' ),
		);
	}

	/**
	 * Output the a view for a COOL frame
	 *
	 * @param int        $id The document id.
	 * @param string     $mode Editor mode.
	 * @param null|array $options COOL frame options.
	 *
	 * @return null|array Properties of the markup.
	 */
	private static function get_view_render( int $id, string $mode, $options = null ) {
		require_once COLLABORA_PLUGIN_DIR . 'includes/class-collaborarequest.php';

		$wopi_base = get_option( CollaboraAdmin::COLLABORA_WOPI_BASE );

		$req         = new CollaboraRequest();
		$wopi_client = $req->get_wopi_client_url();
		if ( null === $wopi_client ) {
			return null;
		}

		$ttl = 0;
		if ( 0 === $ttl ) {
			$ttl = 86400;
		}
		$ttl += gettimeofday( true );

		$access_token = CollaboraUtils::token_for_file_id( $id, (int) $ttl, $mode );
		$closebutton  = 'false';

		if ( $options ) {
			if ( isset( $options['closebutton'] ) && 'true' === $options['closebutton'] ) {
				$closebutton = 'true';
			}
		}

		return array(
			'wopiClient'     => $wopi_client,
			'wopiSrc'        => rawurlencode( $wopi_base . '/wp-json/' . CollaboraWopi::COLLABORA_ROUTE_NS . '/wopi/files/' . $id ),
			'accessToken'    => $access_token,
			'accessTokenTtl' => $ttl * 1000, // It's in usec. The JWT is in sec.
			'closebutton'    => $closebutton,
			'iFrameStyle'    => '',
		);
	}


	/**
	 * Output the COOL frame
	 *
	 * @param int    $id The document id.
	 * @param string $mode Editor mode.
	 * @param bool   $closebutton We want a close button.
	 */
	public static function output_frame( $id, $mode, $closebutton = false ) {
		$base_url = plugins_url( '', COLLABORA_PLUGIN_FILE );

		$frame = self::get_view_render( $id, $mode, array( 'closebutton' => $closebutton ? 'true' : 'false' ) );

		if ( null === $frame ) {
			die(
				'<p>' .
					esc_html( __( 'The Collabora Online server is not available: ', 'collabora-online' ) ) .
				'</p>'
			);
		}

		$file = get_attached_file( $id );

		load_template(
			__DIR__ . '/../templates/frame.php',
			true,
			array(
				'frame'    => $frame,
				'base_url' => $base_url,
				'docname'  => basename( $file ),
			)
		);
	}
}
