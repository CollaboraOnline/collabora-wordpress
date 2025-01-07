<?php
/** COOL WordPress plugin admin
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

/** The Collabora admin page */
class CollaboraAdmin {

	/** Option for the COOL server URL */
	const COLLABORA_SERVER_OPTION = COLLABORA_PLUGIN_NAME . '-cool_server';
	/** Option for the WOPI server base URL */
	const COLLABORA_WOPI_BASE = COLLABORA_PLUGIN_NAME . '-wopi_base';
	/** Option to disable the certificate check */
	const COLLABORA_DISABLE_CERT_CHECK = COLLABORA_PLUGIN_NAME . '-disable_cert_check';
	/** The Token TTL */
	const COLLABORA_TOKEN_TTL = COLLABORA_PLUGIN_NAME . '-token-ttl';
	/** JWT key secret */
	const COLLABORA_JWT_KEY = COLLABORA_PLUGIN_NAME . '-jwt-key';

	/**
	 * Option page hook.
	 */
	public function option_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post" action="options.php">
		<?php
			settings_fields( 'collabora_options_group' );
			do_settings_sections( 'collabora_options_group' );
			submit_button();
		?>
		</form>
		</div>
		<?php
	}

	/**
	 * Delete the settings. Should be called when uninstalling.
	 */
	public function delete_settings() {
		delete_site_option( self::COLLABORA_SERVER_OPTION );
		delete_site_option( self::COLLABORA_WOPI_BASE );
		delete_site_option( self::COLLABORA_DISABLE_CERT_CHECK );
		delete_site_option( self::COLLABORA_TOKEN_TTL );
		delete_site_option( self::COLLABORA_JWT_KEY );
	}

	/**
	 * Admin menu hook.
	 */
	public function admin_menu() {
		$hook = add_options_page(
			__( 'Collabora Online Settings', 'collabora-online' ),
			__( 'Collabora Online', 'collabora-online' ),
			'manage_options',
			'collabora_options',
			array( $this, 'option_page_html' )
		);

		add_action( "load-{$hook}", array( $this, 'load_admin_page' ) );
	}

	/**
	 * Load page hook
	 */
	public function load_admin_page() {
	}

	/**
	 * Text setting hook.
	 *
	 * @param array $args Arguments for the hook.
	 */
	public function setting_text( array $args ) {
		?>
		<input id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" type="text" class="regular-text">
		<?php
	}

	/**
	 * Boolean setting hook.
	 *
	 * @param array $args Arguments for the hook.
	 */
	public function setting_bool( array $args ) {
		?>
		<input id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" <?php checked( $args['value'] ); ?> type="checkbox" value="1">
		<?php
	}

	/**
	 * Section setting hook.
	 */
	public function section_callback() {
		echo esc_html( __( 'Configure where to find the Collabora Online server.', 'collabora-online' ) );
	}

	/**
	 * Validate the TTL (must be integer) and if it is invalid return the previous value.
	 *
	 * @param string $value The TTL setting value to sanitize.
	 */
	public function sanitize_ttl( string $value ) {
		if ( ! is_numeric( $value ) ) {
			return get_option( self::COLLABORA_TOKEN_TTL, 86400 );
		}
		return strval( intval( $value ) );
	}

	/**
	 * Sanitize bool setting value. Will return null (false) if invalid.
	 *
	 * @param string|null $value The value.
	 */
	public function sanitize_bool( $value ) {
		if ( is_null( $value ) || '1' === $value ) {
			return $value;
		}
		return null;
	}

	/**
	 * "Sanitize" tje JWT key. i.e. pass through
	 *
	 * @param string $value The value.
	 */
	public function sanitize_jwt_key( string $value ) {
		return $value;
	}

	/**
	 * Initialise the admin page.
	 */
	public function admin_init() {
		register_setting(
			'collabora_options_group',
			self::COLLABORA_SERVER_OPTION,
			array(
				'sanitize_callback' => 'sanitize_url',
			)
		);
		register_setting(
			'collabora_options_group',
			self::COLLABORA_WOPI_BASE,
			array(
				'sanitize_callback' => 'sanitize_url',
			)
		);
		register_setting(
			'collabora_options_group',
			self::COLLABORA_DISABLE_CERT_CHECK,
			array(
				'type'              => 'boolean',
				'description'       => __( 'Disable the certificate check when connecting to the Collabora Online server', 'collabora-online' ),
				'sanitize_callback' => array( $this, 'sanitize_bool' ),
			)
		);
		register_setting(
			'collabora_options_group',
			self::COLLABORA_TOKEN_TTL,
			array(
				'type'              => 'integer',
				'description'       => __( 'The token TTL in seconds', 'collabora-online' ),
				'default'           => 86400,
				'sanitize_callback' => array( $this, 'sanitize_ttl' ),
			)
		);
		register_setting(
			'collabora_options_group',
			self::COLLABORA_JWT_KEY,
			array(
				'description'       => __( 'JWT secret key to generate tokens', 'collabora-online' ),
				'sanitize_callback' => array( $this, 'sanitize_jwt_key' ),
			)
		);

		add_settings_section(
			'collabora_options_section',
			'',
			array( $this, 'section_callback' ),
			'collabora_options_group',
		);
		add_settings_field(
			self::COLLABORA_SERVER_OPTION,
			__( 'Collabora Online server URL:', 'collabora-online' ),
			array( $this, 'setting_text' ),
			'collabora_options_group',
			'collabora_options_section',
			array(
				'id'    => self::COLLABORA_SERVER_OPTION,
				'value' => get_option( self::COLLABORA_SERVER_OPTION, 'https://localhost:9980' ),
			)
		);
		add_settings_field(
			self::COLLABORA_WOPI_BASE,
			__( 'WOPI host URL:', 'collabora-online' ),
			array( $this, 'setting_text' ),
			'collabora_options_group',
			'collabora_options_section',
			array(
				'id'    => self::COLLABORA_WOPI_BASE,
				'value' => get_option( self::COLLABORA_WOPI_BASE, 'https://localhost' ),
			)
		);
		add_settings_field(
			self::COLLABORA_DISABLE_CERT_CHECK,
			__( 'Disable TLS certificate check for COOL (development only):', 'collabora-online' ),
			array( $this, 'setting_bool' ),
			'collabora_options_group',
			'collabora_options_section',
			array(
				'id'    => self::COLLABORA_DISABLE_CERT_CHECK,
				'value' => get_option( self::COLLABORA_DISABLE_CERT_CHECK, false ),
			)
		);
		add_settings_field(
			self::COLLABORA_TOKEN_TTL,
			__( 'Token TTL in seconds:', 'collabora-online' ),
			array( $this, 'setting_text' ),
			'collabora_options_group',
			'collabora_options_section',
			array(
				'id'    => self::COLLABORA_TOKEN_TTL,
				'value' => get_option( self::COLLABORA_TOKEN_TTL, 86400 ),
			)
		);
		add_settings_field(
			self::COLLABORA_JWT_KEY,
			__( 'JWT key secret to generate token:', 'collabora-online' ),
			array( $this, 'setting_text' ),
			'collabora_options_group',
			'collabora_options_section',
			array(
				'id'    => self::COLLABORA_JWT_KEY,
				'value' => get_option( self::COLLABORA_JWT_KEY, '' ),
			)
		);
	}
}

?>
