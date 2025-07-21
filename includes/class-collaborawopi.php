<?php
/** COOL WordPress plugin WOPI host.
 *
 * Implement the WOPI host using WordPress REST API.
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

require_once COLLABORA_PLUGIN_DIR . 'includes/class-collaborautils.php';

/** Class to handle WOPI. */
class CollaboraWopi {
	const COLLABORA_ROUTE_NS = 'collabora';
	const REV_POST_TYPE      = 'collabora_revision';

	/**
	 * Init hook. Will register the post type for revision.
	 */
	public static function init() {
		self::register_post_types();
	}

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
	 * Return a permission denied HTTP 401 error.
	 *
	 * @param string $reason The text reason.
	 *
	 * @return WP_REST_Response The REST response.
	 */
	private static function permission_denied( string $reason ) {
		return new WP_REST_Response(
			$reason,
			401,
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
		$jwt_payload = CollaboraUtils::verify_token_for_id( $token, $id );
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
		if ( $post && ( 'attachment' !== $post->post_type ) ) {
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
		$reviewer_role    = get_option( CollaboraAdmin::COLLABORA_USER_ROLE_REVIEW );
		$can_review       = $reviewer_role && in_array( $reviewer_role, wp_get_current_user()->roles, true );
		$can_only_comment = $jwt_payload->cmt && ( $can_review || current_user_can( 'edit_post', $id ) );
		$file             = get_attached_file( $id );
		if ( ! $file ) {
			return self::file_error( 'File not found.' );
		}
		$is_administrator = isset( $user->roles['administrator'] ) && true === $user->roles['administrator'];

		$user    = wp_get_current_user();
		$mtime   = date_create_immutable_from_format( 'U', filemtime( $file ) );
		$payload = array(
			'BaseFileName'            => basename( $file ),
			'Size'                    => filesize( $file ),
			'LastModifiedTime'        => $mtime->format( 'c' ),
			'UserId'                  => $jwt_payload->uid,
			'UserFriendlyName'        => $user->get( 'display_name' ),
			'UserExtraInfo'           => array(
				'mail' => $user->get( 'user_email' ),
			),
			'UserCanWrite'            => $can_write,
			'UserCanNotWriteRelative' => true,
			// We only se this to true if it can AND not also have write permissions.
			'UserCanOnlyComment'      => ! $can_write && $can_only_comment,
			'IsAdminUser'             => $is_administrator,
			'IsAnonymousUser'         => false, // $user->isAnonymous(),
		);

		if ( function_exists( 'get_avatar_url' ) ) {
			// This require adding `https://secure.gravatar.com` to the `img-src` CSP rule.
			// Otherwise it will display the default icon.
			$avatar = get_avatar_url( $user->id );
			if ( false !== $avatar ) {
				$payload['UserExtraInfo']['avatar'] = $avatar;
			}
		}

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
				// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				echo file_get_contents( $file );
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
				// phpcs:enable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				return true;
			}
		);

		return $response;
	}

	/**
	 * Register the post type for the revisions
	 */
	public static function register_post_types() {
		register_post_type(
			self::REV_POST_TYPE,
			array(
				'label'            => __( 'Revisions', 'collabora-online' ),
				'public'           => false,
				'hierarchical'     => false,
				'rewrite'          => false,
				'query_var'        => false,
				'delete_with_user' => false,
				'can_export'       => false,
				'supports'         => array(),
			)
		);
		register_post_meta(
			self::REV_POST_TYPE,
			'collabora_rev_timestamp',
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => function ( $value ) {
					return esc_sql( $value );
				},
			)
		);
	}

	/**
	 * Create a new revision
	 *
	 * @param int    $attachment_id the ID if the attachment to create the revision for.
	 * @param string $file_path The path of the file to create a revision for.
	 *
	 * @return bool Whether there is success or not.
	 */
	private static function create_revision( int $attachment_id, string $file_path ) {
		$timestamp     = gettimeofday( true );
		$revision_path = $file_path . '.' . (string) $timestamp;
		if ( ! copy( $file_path, $revision_path ) ) {
			return false;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'      => self::REV_POST_TYPE,
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_parent'    => $attachment_id,
			),
			false,
			true
		);
		if ( 0 === $post_id ) {
			return false;
		}

		add_post_meta( $post_id, '_wp_attached_file', $revision_path, true );
		add_post_meta( $post_id, 'collabora_rev_timestamp', (string) $timestamp );

		return true;
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

		$can_write = ( $jwt_payload->wri && current_user_can( 'edit_post', $id ) );
		if ( ! $can_write && $jwt_payload->cmt ) {
			// Handle comment only mode (review).
			$reviewer_role = get_option( CollaboraAdmin::COLLABORA_USER_ROLE_REVIEW );
			$can_review    = $reviewer_role && in_array( $reviewer_role, wp_get_current_user()->roles, true );
			$can_write     = $can_review || current_user_can( 'edit_post', $id );
		}

		if ( ! $can_write ) {
			return self::permission_denied( 'Permission denied.' );
		}

		$data = $request->get_body();
		$file = get_attached_file( $id );

		$modtime   = filemtime( $file );
		$timestamp = $request->get_header( 'X-COOL-WOPI-Timestamp' );
		if ( $timestamp ) {
			$timestamp = date_create_immutable_from_format( DateTimeInterface::ISO8601, $timestamp );
		}
		if ( ! $timestamp || ( $timestamp->getTimestamp() !== $modtime ) ) {
			$payload = array(
				'COOLStatusCode' => 1010,
			);
			return new WP_REST_Response(
				$payload,
				409,
				array(
					'Access-Control-Allow-Origin' => '*',
					'Content-Type'                => 'application/json; charset=' . get_option( 'blog_charset' ),
				)
			);
		}

		if ( ! self::create_revision( $id, $file ) ) {
			return self::file_error( 'Creating revision.' );
		}

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		if ( file_put_contents( $file, $data, LOCK_EX ) === false ) {
			return self::file_error( 'Saving file.' );
		}
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		wp_update_post(
			array(
				'ID' => $id,
			)
		);

		clearstatcache(); // This is required for filemtime to returnt he actual value.
		$mtime   = date_create_immutable_from_format( 'U', filemtime( $file ) );
		$payload = array(
			'LastModifiedTime' => $mtime->format( 'c' ),
		);

		return new WP_REST_Response(
			$payload,
			200,
			array(
				'Access-Control-Allow-Origin' => '*',
				'Content-Type'                => 'application/json; charset=' . get_option( 'blog_charset' ),
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
