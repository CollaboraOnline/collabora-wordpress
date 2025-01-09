<?php
/** COOL WordPress plugin utilities.
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

require_once COLLABORA_PLUGIN_DIR . 'vendor/firebase/php-jwt/src/JWT.php';
require_once COLLABORA_PLUGIN_DIR . 'vendor/firebase/php-jwt/src/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/** Some COOL utilities */
class CollaboraUtils {
	/** Obtain the signing key */
	private static function get_key() {
		$key = get_option( CollaboraAdmin::COLLABORA_JWT_KEY );
		return $key;
	}

	/** Verify JWT token
	 *
	 *  Verification include:
	 *  - matching $id with fid in the payload
	 *  - verifying the expiration
	 *
	 * @param string $token The token.
	 * @param int    $id The id of the post.
	 */
	public static function verify_token_for_id(
		#[\SensitiveParameter]
		string $token,
		int $id
	) {
		$key = static::get_key();
		if ( gettype( $key ) !== 'string' ) {
			// error_log( 'cool error: JWT key isn\'t set.' );
			return null;
		}
		try {
			$payload = JWT::decode( $token, new Key( $key, 'HS256' ) );
			if ( $payload && ( $payload->fid === $id ) && ( $payload->exp >= gettimeofday( true ) ) ) {
				return $payload;
			}
		} catch ( \Exception $e ) {
			// error_log( 'cool WOPI error: ' . $e->getMessage() );
		}
		return null;
	}

	/**
	 * Create a JWT token for the Media with id $id, a $ttl, and an
	 * eventual write permission.
	 *
	 * @param int    $id The ID of the file.
	 * @param int    $ttl The TTL of the token in seconds.
	 * @param string $mode The opening mode.
	 *
	 * The token will carry the following:
	 *
	 * - fid: the post id in WordPress.
	 * - uid: the User id for the token. Permissions should be checked
	 *   whenever.
	 * - exp: the expiration time of the token.
	 * - wri: if true, then this token has write permissions.
	 * - cmt: if true, then this is a comment permission. Requires wri = true.
	 */
	public static function token_for_file_id( int $id, int $ttl, $mode = 'view' ) {
		$wri = false;
		$cmt = false;
		switch ( $mode ) {
			case 'edit':
				$wri = true;
				break;
			case 'review':
				$wri = true;
				$cmt = true;
				break;
			case 'view':
			default:
				break;
		}
		$payload = array(
			'fid' => $id,
			'uid' => get_current_user_id(),
			'exp' => $ttl,
			'wri' => $wri,
			'cmt' => $cmt,
		);
		$key     = static::get_key();
		$jwt     = JWT::encode( $payload, $key, 'HS256' );

		return $jwt;
	}

	/**
	 * Get the editor URL for the post with $id
	 *
	 * @param integer $id The ID of the post the file is attached to.
	 * @param string  $mode The mode to open the file.
	 */
	public static function get_editor_url( $id, string $mode ) {
		$query         = array(
			'id' => $id,
		);
		$query['mode'] = $mode;
		$baseurl       = plugins_url( 'cool.php', COLLABORA_PLUGIN_FILE ) . '?' . http_build_query( $query );
		return wp_nonce_url( $baseurl, 'collabora-frame-' . $id );
	}
}
