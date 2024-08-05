<?php
/**
 * Plugin Name:       Sirreal Dev
 * Plugin URI:        https://github.com/sirreal/sirreal-dev
 * Description:       Personal WordPress development plugin.
 * Version:           0.1
 * Author:            Jon Surrell
 * Author URI:        https://profiles.wordpress.org/jonsurrell/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package HtmlApiDebugger
 */

namespace sirreal;

function jdbg( ...$data ) {
	ob_start();
	echo "======\n";
	foreach ( $data as $d ) {
		print_r( $d );
		echo "\n";
	}
	echo "======\n";
	file_put_contents( WP_CONTENT_DIR . '/log.txt', ob_get_clean(), FILE_APPEND );
}
