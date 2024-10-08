<?php
/*
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CoolUtils {
    /** Obtain the signing key from the key storage */
    static function get_key() {
        $key = get_option( CollaboraAdmin::COOL_JWT_KEY );
        return $key;
    }

    /** Verify JWT token
     *
     *  Verification include:
     *  - matching $id with fid in the payload
     *  - verifying the expiration
     */
    public static function verify_token_for_id(
        #[\SensitiveParameter]
        string $token,
        $id
    ) {
        $key = static::get_key();
        if ( gettype( $key ) != "string" ) {
            error_log( 'cool error: JWT key isn\'t set.' );
            return null;
        }
        try {
            $payload = JWT::decode( $token, new Key( $key, 'HS256' ) );
            if ( $payload && ( $payload->fid == $id ) && ( $payload->exp >= gettimeofday(true) ) ) {
                return $payload;
            }
        } catch ( \Exception $e ) {
            error_log( 'cool WOPI error: ' . $e->getMessage( ) );
        }
        return null;
    }
}

?>
