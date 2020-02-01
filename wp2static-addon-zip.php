<?php

/**
 * Plugin Name:       WP2Static Add-on: Zip
 * Plugin URI:        https://wp2static.com
 * Description:       Zip deployment add-on for WP2Static.
 * Version:           0.1
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-zip
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WP2STATIC_ZIP_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_NAME_VERSION', '0.1' );

require WP2STATIC_ZIP_PATH . 'vendor/autoload.php';

function run_wp2static_addon_zip() {
	$controller = new WP2StaticZip\Controller();
	$controller->run();
}

register_activation_hook(
    __FILE__,
    [ 'WP2StaticZip\Controller', 'activate' ]
);

register_deactivation_hook(
    __FILE__,
    [ 'WP2StaticZip\Controller', 'deactivate' ]
);

run_wp2static_addon_zip();

