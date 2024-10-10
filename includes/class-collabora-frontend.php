<?php
/*
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

class CollaboraFrontend {
    public function shortcodes_init() {
        add_shortcode( 'cool', array ( $this, 'cool_shortcode' ) );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( COOL_PLUGIN_NAME . '-cool-js', plugins_url( 'public/js/cool.js', COOL_PLUGIN_FILE ), array(), 0, false );
        wp_enqueue_style( COOL_PLUGIN_NAME . '-cool-css', plugins_url( 'public/css/cool.css', COOL_PLUGIN_FILE ), array(), 0, false );
    }

    public function cool_shortcode( $atts = [], $content = null, $tag = '' ) {
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );
        if ( $atts[ 'source' ] ) {
            return '<p>Displaying source ' . esc_html( $atts[ 'source' ] ) . '</p>'
                . self::get_view_render( $atts[ 'source' ], false );
        } else {
            return '<p>Error: source is missing</p>';
        }
    }

    // XXX sanitize
    public static function cool_frame( array $params ) {
        $closebutton = $params[ 'closebutton' ] == 'true' ? 'true' : 'false';
        $wopi_src = $params[ 'wopiSrc' ];
        $wopi_client = $params[ 'wopiClient' ];

        return '
<div class="cool-frame">
  <div style="display: none">
    <form action="" enctype="multipart/form-data" method="post" target="collabora-online-viewer" id="collabora-submit-form">
      <input name="access_token" value="' . $params[ 'accessToken' ] . '" type="hidden" />
      <input name="access_token_ttl" value="' . $params[ 'accessTokenTtl' ] . '" type="hidden" />
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

    public static function get_view_render( $id, bool $can_write, $options = null ) {
        require_once COOL_PLUGIN_DIR . 'includes/class-cool-request.php';

        $wopi_base = get_option( CollaboraAdmin::COOL_WOPI_BASE );

        $req = new CoolRequest();
        $wopi_client = $req->get_wopi_client_url();
        if ( $wopi_client === null ) {
            return '<p>' .
                __( 'The Collabora Online server is not available: ', COOL_PLUGIN_NAME ) .
                esc_html( $req->error_string() ) .
                '</p>';
        }

        $ttl = 0;
        if ( $ttl == 0 ) {
            $ttl = 86400;
        }
        $ttl += gettimeofday( true );

        $access_token = CoolUtils::token_for_file_id( $id, $ttl, $can_write );

        /*
        if ($options) {
            if (isset($options['closebutton']) && $options['closebutton'] == 'true') {
                $render_array['#closebutton'] = 'true';
            }
            }*/

        return self::cool_frame (
            array(
                'wopiClient' => $wopi_client,
                'wopiSrc' => urlencode( $wopi_base . '/wp-json/' . CollaboraWopi::COLLABORA_ROUTE_NS . '/wopi/files/' . $id ),
                'accessToken' => $access_token,
                'accessTokenTtl' => $ttl * 1000, // It's in usec. The JWT is in sec.
                'closebutton' => 'false',
                'iFrameStyle' => '',
            )
        );
    }
}

?>
