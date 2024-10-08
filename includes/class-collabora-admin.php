<?php
/*
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

class CollaboraAdmin {

    /** option for the COOL server URL */
    const COOL_SERVER_OPTION = COOL_PLUGIN_NAME . '-cool_server';
    /** option for the WOPI server base URL */
    const COOL_WOPI_BASE = COOL_PLUGIN_NAME . '-wopi_base';
    /** option to disable the certificate check */
    const COOL_DISABLE_CERT_CHECK = COOL_PLUGIN_NAME . '-disable_cert_check';
    /** JWT key secret */
    const COOL_JWT_KEY = COOL_PLUGIN_NAME . '-jwt-key';

    function option_page_html() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post" action="options.php">
        <?php
            settings_fields ('cool_options_group');
            do_settings_sections('cool_options_group');
            submit_button ();
        ?>
        </form>
        </div>
        <?php
    }

    public function delete_settings() {
        delete_site_option( self::COOL_SERVER_OPTION );
        delete_site_option( self::COOL_WOPI_BASE );
        delete_site_option( self::COOL_DISABLE_CERT_CHECK );
        delete_site_option( self::COOL_JWT_KEY );
    }

    public function admin_menu() {
        $hook = add_options_page(
            __( 'Collabora Online Settings', COOL_PLUGIN_NAME ),
            __( 'Collabora Online', COOL_PLUGIN_NAME ),
            'manage_options',
            'cool_options',
            array( $this, 'option_page_html' )
        );

        add_action ( "load-{$hook}", array( $this, 'load_admin_page' )  );
    }

    function load_admin_page() {

    }

    function setting_text( array $args ) {
        ?>
        <input id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" type="text" class="regular-text">
        <?php
    }

    function setting_bool( array $args ) {
        ?>
		<input id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" <?php checked( $args['value'] ); ?> type="checkbox" value="1">
        <?php
    }

    function section_callback () {
        echo __( 'Configure where to find the Collabora Online server.', COOL_PLUGIN_NAME );
    }

    public function admin_init() {
        register_setting( 'cool_options_group', self::COOL_SERVER_OPTION );
        register_setting( 'cool_options_group', self::COOL_WOPI_BASE );
        register_setting( 'cool_options_group', self::COOL_DISABLE_CERT_CHECK, array (
            'type' => 'boolean',
            'description' =>  __( 'Disable the certificate check when connecting to the Collabora Online server', COOL_PLUGIN_NAME ),
        ));
        register_setting( 'cool_options_group', self::COOL_JWT_KEY, array (
            'description' => __( 'JWT secert key to generate tokens', COOL_PLUGIN_NAME ),
        ));

        add_settings_section (
            'cool_options_section',
            '',
            array ( $this, 'section_callback' ),
            'cool_options_group',
        );
        add_settings_field (
            self::COOL_SERVER_OPTION,
            __( 'Collabora Online server URL', COOL_PLUGIN_NAME ),
            array( $this, 'setting_text' ),
            'cool_options_group',
            'cool_options_section',
            array(
                'id' => self::COOL_SERVER_OPTION,
                'value' => get_option (self::COOL_SERVER_OPTION, 'https://localhost:9980'),
            )
        );
        add_settings_field (
            self::COOL_WOPI_BASE,
            __( 'WOPI host URL.', COOL_PLUGIN_NAME ),
            array( $this, 'setting_text' ),
            'cool_options_group',
            'cool_options_section',
            array(
                'id' => self::COOL_WOPI_BASE,
                'value' => get_option (self::COOL_WOPI_BASE, 'https://localhost'),
            )
        );
        add_settings_field (
            self::COOL_DISABLE_CERT_CHECK,
            __( 'Disable TLS certificate check for COOL (development only).', COOL_PLUGIN_NAME ),
            array( $this, 'setting_bool' ),
            'cool_options_group',
            'cool_options_section',
            array(
                'id' => self::COOL_DISABLE_CERT_CHECK,
                'value' => get_option (self::COOL_DISABLE_CERT_CHECK, false),
            )
        );
        add_settings_field (
            self::COOL_JWT_KEY,
            __( 'JWT key secret to generate token.', COOL_PLUGIN_NAME ),
            array( $this, 'setting_text' ),
            'cool_options_group',
            'cool_options_section',
            array(
                'id' => self::COOL_JWT_KEY,
                'value' => get_option (self::COOL_JWT_KEY, ''),
            )
        );
    }
}

?>
