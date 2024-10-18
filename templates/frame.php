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

$args = wp_parse_args(
    $args,
    array(
        'base_url' => '',
        'frame' => ''
    )
);

$base_url = esc_url( $args['base_url'] );

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" id="collabora-wordpress-cool-css" href="<?php echo $base_url; ?>/public/css/cool.css" media="all">
    <script id="collabora-wordpress-cool-js" src="<?php echo $base_url; ?>/public/js/cool.js"></script>
</head>
<body>
    <?php echo balanceTags( $args['frame'] ); ?>
</body>
</html>
