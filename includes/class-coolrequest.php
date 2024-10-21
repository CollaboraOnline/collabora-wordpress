<?php
/*
 * Copyright the Collabora Online contributors.
 *
 * SPDX-License-Identifier: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * Get the discovery XML content
 *
 * Return `false` in case of error.
 */
function get_discovery( $server ) {
    $discovery_url = $server . '/hosting/discovery';
    $disable_checks = (bool)get_option( CollaboraAdmin::COOL_DISABLE_CERT_CHECK );

    $options = array();
    if ( $disable_checks === true ) {
        $options['sslverify'] = false;
    }
    $response = wp_remote_get( $discovery_url, $options );
    if (is_array( $response ) ) {
        return $response['body'];
    } else {
        return null;
    }
}

function get_wopi_src_url( $discovery_parsed, $mimetype ) {
    if ( $discovery_parsed === null || $discovery_parsed == false ) {
        return null;
    }
    $result = $discovery_parsed->xpath( sprintf( '/wopi-discovery/net-zone/app[@name=\'%s\']/action', $mimetype ) );
    if ( $result && count( $result ) > 0 ) {
        return $result[ 0 ][ 'urlsrc' ];
    }
    return null;
}

class CoolRequest {

    private $error_code;

    const ERROR_MSG = [
        0 => 'Success',
        101 => 'GET Request not found.',
        201 => 'Collabora Online server address is not valid.',
        202 => 'Collabora Online server address scheme does not match the current page url scheme.',
        203 => 'Not able to retrieve the discovery.xml file from the Collabora Online server.',
        102 => 'The retrieved discovery.xml file is not a valid XML file.',
        103 => 'The requested mime type is not handled.',
        204 => 'Warning! You have to specify the scheme protocol too (http|https) for the server address.'
    ];

    private $wopi_src;

    public function __construct() {
        $this->error_code = 0;
        $this->wopi_src = '';
    }

    public function error_string() {
        return $this->error_code . ': ' . static::ERROR_MSG[$this->error_code];
    }

    /** Return the wopi client URL */
    public function get_wopi_client_url() {
        $_HOST_SCHEME = isset( $_SERVER[ 'HTTPS' ] ) ? 'https' : 'http';
        $wopi_client_server = get_option( CollaboraAdmin::COOL_SERVER_OPTION );
        if ( !$wopi_client_server ) {
            $this->error_code = 201;
            return;
        }
        $wopi_client_server = trim( $wopi_client_server );

        if ( !str_starts_with( $wopi_client_server, 'http' ) ) {
            $this->error_code = 204;
            return;
        }


        if ( !str_starts_with( $wopi_client_server, $_HOST_SCHEME . '://' ) ) {
            $this->error_code = 202;
            return;
        }

        $discovery = get_discovery( $wopi_client_server );
        if ( $discovery === false ) {
            $this->error_code = 203;
            return;
        }

        if ( \PHP_VERSION_ID < 80000 ) {
            // This is deprecated and disabled by default in PHP 8.0
            $load_entities = libxml_disable_entity_loader( true );
        }
        $discovery_parsed = simplexml_load_string( $discovery );
        if ( \PHP_VERSION_ID < 80000 ) {
            // This is deprecated and disabled by default in PHP 8.0
            libxml_disable_entity_loader( $load_entities );
        }
        if ( !$discovery_parsed ) {
            $this->error_code = 102;
            return;
        }

        $this->wopi_src = strval( get_wopi_src_url( $discovery_parsed, 'text/plain' )[ 0 ] );
        if ( !$this->wopi_src ) {
            $this->error_code = 103;
            return;
        }

        return $this->wopi_src;
    }
}

?>
