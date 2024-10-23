<?php
/** COOL WordPress plugin WOPI host.
 *
 * Implement the WOPI host using WordPress REST API.
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

require_once COOL_PLUGIN_DIR . 'includes/class-coolutils.php';

/** Class to handle WOPI. */
class CollaboraWopi {
	const COLLABORA_ROUTE_NS = 'cool';

	/**
	 * Route registration hook
	 */
	public static function register_routes() {
		register_rest_route(
			self::COLLABORA_ROUTE_NS,
			'/wopi/files/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get' ),
					'args'                => self::request_parameters(),
					'permission_callback' => '__return_true',
				),
			)
		);
		register_rest_route(
			self::COLLABORA_ROUTE_NS,
			'/wopi/files/(?P<id>\d+)/contents',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_content' ),
					'args'                => self::request_parameters(),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'put_content' ),
					'args'                => self::request_parameters(),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Return a permission denied HTTP 403 error.
	 *
	 * @param string $reason The text reason.
	 *
	 * @return WP_REST_Response The REST response.
	 */
	private static function permission_denied( string $reason ) {
		return new WP_REST_Response(
			$reason,
			403,
			array(
				'Content-Type' => 'text/plain',
			)
		);
	}

	/**
	 * Return a not found HTTP 404 error.
	 *
	 * @param string $reason The text reason.
	 *
	 * @return WP_REST_Response The REST response.
	 */
	private static function not_found( string $reason ) {
		return new WP_REST_Response(
			$reason,
			404,
			array(
				'Content-Type' => 'text/plain',
			)
		);
	}

	/**
	 * Return a file HTTP 500 error.
	 *
	 * @param string $reason The text reason.
	 *
	 * @return WP_REST_Response The REST response.
	 */
	private static function file_error( string $reason ) {
		return new WP_REST_Response(
			$reason,
			500,
			array(
				'Content-Type' => 'text/plain',
			)
		);
	}

	/**
	 * Returns an array. 'error' is set to true in case of error. If
	 * successful the JWT is in 'jwt_payload'. Otherwise it returns a
	 * `WP_REST_Response` in 'response'.
	 *
	 * @param string $token The token.
	 * @param int    $id The file id.
	 *
	 * @return array An array. If 'error' is true then 'response' contains
	 * the WP_REST_Response.
	 */
	private static function auth( string $token, int $id ) {
		$jwt_payload = CoolUtils::verify_token_for_id( $token, $id );
		if ( null === $jwt_payload ) {
			return array(
				'error'    => true,
				'response' => self::permission_denied( 'Authentication failed.' ),
			);
		}

		$user = wp_set_current_user( $jwt_payload->uid );
		if ( ! $user->exists() ) {
			return array(
				'error'    => true,
				'response' => self::permission_denied( 'Unknown user.' ),
			);
		}

		$post = get_post( $id );
		// If the post_type isn't an attachment, it is considered not found.
		if ( 'attachment' !== $post->post_type ) {
			return array(
				'error'    => true,
				'response' => self::not_found( 'File doesn\'t exist.' ),
			);
		}

		return array(
			'error'       => false,
			'jwt_payload' => $jwt_payload,
		);
	}

	/**
	 * WOPI get file info
	 *
	 * @param array $request The HTTP request.
	 *
	 * @return WP_REST_Response The REST Response.
	 */
	public static function get( $request ) {
		$id    = (int) $request['id'];
		$token = (string) $request['access_token'];

		$auth = self::auth( $token, $id );
		if ( $auth['error'] ) {
			return $auth['response'];
		}
		$jwt_payload = $auth['jwt_payload'];
		if ( null === $jwt_payload ) {
			return self::permission_denied( 'Authentication failed.' );
		}

		$can_write        = $jwt_payload->wri && current_user_can( 'edit_post', $id );
		$file             = get_attached_file( $id );
		$is_administrator = isset( $user->roles['administrator'] ) && true === $user->roles['administrator'];

		$user    = wp_get_current_user();
		$mtime   = date_create_immutable_from_format( 'U', filemtime( $file ) );
		$payload = array(
			'BaseFileName'     => basename( $file ),
			'Size'             => filesize( $file ),
			'LastModifiedTime' => $mtime->format( 'c' ),
			'UserId'           => $jwt_payload->uid,
			'UserFriendlyName' => $user->get( 'display_name' ),
			'UserExtraInfo'    => array(
				// 'avatar' => $avatarUrl,
				'mail' => $user->get( 'user_email' ),
			),
			'UserCanWrite'     => $can_write,
			'IsAdminUser'      => $is_administrator,
			'IsAnonymousUser'  => false, // $user->isAnonymous(),
		);

		return new WP_REST_Response(
			$payload,
			200,
			array(
				'Content-Type' => 'application/json; charset=' . get_option( 'blog_charset' ),
			)
		);
	}

	/**
	 * WOPI get content
	 *
	 * @param array $request The HTTP request.
	 *
	 * @return WP_REST_Response The REST Response.
	 */
	public static function get_content( $request ) {
		$id    = (int) $request['id'];
		$token = (string) $request['access_token'];

		$auth = self::auth( $token, $id );
		if ( $auth['error'] ) {
			return $auth['response'];
		}
		$jwt_payload = $auth['jwt_payload'];
		if ( null === $jwt_payload ) {
			return self::permission_denied( 'Authentication failed.' );
		}

		$file      = get_attached_file( $id );
		$mime_type = mime_content_type( $file );
		$response  = new WP_HTTP_Response(
			null,
			200,
			array(
				'Content-Transfer-Encoding'   => 'binary',
				'Access-Control-Allow-Origin' => '*',
				'Content-Type'                => $mime_type,
			)
		);

		/*
		 * This is the tricky part. We want to return the binary content
		 * and prevent WP from making it a string
		 */
		add_filter(
			'rest_pre_serve_request',
			function () use ( $file ) {
				echo file_get_contents( $file );
				return true;
			}
		);

		return $response;
	}

	/**
	 * WOPI put content
	 *
	 * @param array $request The HTTP request.
	 *
	 * @return WP_REST_Response The REST Response.
	 */
	public static function put_content( $request ) {
		$id    = (int) $request['id'];
		$token = (string) $request['access_token'];

		$auth = self::auth( $token, $id );
		if ( $auth['error'] ) {
			return $auth['response'];
		}
		$jwt_payload = $auth['jwt_payload'];
		if ( null === $jwt_payload ) {
			return self::permission_denied( 'Authentication failed.' );
		}

		$can_write = $jwt_payload->wri && current_user_can( 'edit_post', $id );
		if ( ! $can_write ) {
			return self::permission_denied( 'Permission denied.' );
		}

		$data = $request->get_body();
		$file = get_attached_file( $id );
		if ( ! copy( $file, $file . '.' . (string) gettimeofday( true ) ) ) {
			error_log( 'Creating backup copy.' . var_export( $wp_filesystem->errors->errors, true ) );
			return self::file_error( 'Creating backup copy.' );
		}
		if ( file_put_contents( $file, $data, LOCK_EX ) === false ) {
			error_log( 'Saving file.' );
			return self::file_error( 'Saving file.' );
		}

		wp_update_post(
			array(
				'ID' => $id,
			)
		);

		return new WP_REST_Response(
			'File saved.',
			200,
			array(
				'Access-Control-Allow-Origin' => '*',
				'Content-Type'                => 'text/plain',
			)
		);
	}

	/**
	 * Hook for the request parameters.
	 */
	public static function request_parameters() {
		$params = array();

		$params['access_token'] = array(
			'required' => true,
			'type'     => 'string',
		);

		$params['access_token_ttl'] = array(
			'required'          => false,
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'absint',
		);

		$params['WOPISrc'] = array(
			'required' => false,
			'type'     => 'string',
			'pattern'  => '^(https?://)(.+)$',
		);

		return $params;
	}
}
