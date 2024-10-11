<?php
/*
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

require_once COOL_PLUGIN_DIR . 'includes/class-cool-utils.php';

class CollaboraWopi {
    const COLLABORA_ROUTE_NS = 'cool';

    public static function register_routes() {
        register_rest_route(
            self::COLLABORA_ROUTE_NS,
            '/wopi/files/(?P<id>\d+)',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback'=> array( self::class, 'get' ),
                    'args' => self::request_parameters(),
                    'permission_callback' => '__return_true',
                ),
            )
        );
        register_rest_route(
            self::COLLABORA_ROUTE_NS,
            '/wopi/files/(?P<id>\d+)/contents',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array( self::class, 'get_content' ),
                    'args' => self::request_parameters(),
                    'permission_callback' => '__return_true',
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array( self::class, 'put_content' ),
                    'args' => self::request_parameters(),
                    'permission_callback' => '__return_true',
                ),
            )
        );
    }

    static function permission_denied( string $reason ) {
        return new WP_REST_Response(
            $reason,
            403,
            array(
                'Content-Type' => 'text/plain'
            )
        );
    }

    static function not_found( string $reason ) {
        return new WP_REST_Response(
            $reason,
            404,
            array(
                'Content-Type' => 'text/plain'
            )
        );
    }

    static function get( $request ) {
        $id = (string) $request['id'];
        $token = (string) $request['access_token'];

        $jwt_payload = CoolUtils::verify_token_for_id( $token, $id );
        if ( $jwt_payload == null ) {
            return self::permission_denied( 'Authentication failed.' );
        }

        $user = wp_set_current_user( $jwt_payload->uid );
        if ( !$user->exists() ) {
            return self::permission_denied( 'Unknown user.' );
        }

        $post = get_post ($id);
        // If the post_type isn't an attachment, it is considered not found.
        if ( $post->post_type !== 'attachment' ) {
            return self::not_found( 'File doesn\'t exist.' );
        }

        $can_write = $jwt_payload->wri && current_user_can( 'edit_post', $id );
        $file = get_attached_file($id);
        $is_administrator = isset($user->roles['administrator']) && $user->roles['administrator'] === true;

        $mtime = date_create_immutable_from_format('U', filemtime( $file ) );
        $payload = [
            'BaseFileName' => basename( $file ),
            'Size' => filesize( $file ),
            'LastModifiedTime' => $mtime->format( 'c' ),
            'UserId' => $jwt_payload->uid,
            'UserFriendlyName' => $user->get( 'display_name' ),
            'UserExtraInfo' => [
                // 'avatar' => $avatarUrl,
                'mail' => $user->get( 'user_email' ),
            ],
            'UserCanWrite' => $can_write,
            'IsAdminUser' => $is_administrator,
            'IsAnonymousUser' => false, //$user->isAnonymous(),
        ];

        return new WP_REST_Response(
            $payload,
            200,
            array(
                'Content-Type' => 'application/json; charset=' . get_option( 'blog_charset' ),
            )
        );
    }

    static function get_content( $request ) {
        $id = (string) $request['id'];
        $token = (string) $request['access_token'];

        $jwt_payload = CoolUtils::verify_token_for_id( $token, $id );
        if ( $jwt_payload == null ) {
            return self::permissionDenied( 'Authentication failed.' );
        }

        $user = wp_set_current_user( $jwt_payload->uid );
        if ( !$user->exists() ) {
            return self::permissionDenied( 'Unknown user.' );
        }

        $can_write = $jwt_payload->wri;

        $post = get_post( $id );
        // If the post_type isn't an attachment, it is considered not found.
        if ( $post->post_type !== 'attachment' ) {
            return self::notFound( 'File doesn\'t exist.' );
        }

        $file = get_attached_file( $id );
        $mime_type = mime_content_type( $file );
        $response = new WP_HTTP_Response(
            null,
            200,
            array(
                'Content-Transfer-Encoding' => 'binary',
                'Access-Control-Allow-Origin' => '*',
                'Content-Type' => $mime_type,
            )
        );
        /*
         * This is the tricky part. We want to return the binary content
         * and prevent WP from making it a string
         */
        add_filter( 'rest_pre_serve_request', function () use( $file ) {
            echo file_get_contents( $file );
            return true;
        } );

        return $response;
    }

    static function put_content( $request ) {
        $code = 200;
        $response = "Winter is coming";

        return new WP_REST_Response(
            $response,
            $code,
            array(
                'Access-Control-Allow-Origin' => '*',
                'Content-Type' => 'application/jrd+json; charset=' . get_option( 'blog_charset' ),
            )
        );
    }

    static function request_parameters() {
        $params = array();

        $params['access_token'] = array(
            'required' => true,
            'type' => 'string',
        );

        $params['access_token_ttl'] = array(
            'required' => false,
            'type' => 'integer',
            'default' => 0,
            'sanitize_callback' => 'absint',
        );

        $params['WOPISrc'] = array(
            'required' => false,
            'type' => 'string',
            'pattern' => '^(https?://)(.+)$',
        );

        return $params;
    }
}

?>
