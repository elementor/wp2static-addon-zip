<?php

/**
 * Plugin Name:       WP2Static Add-on: Zip Deployment
 * Plugin URI:        https://wp2static.com
 * Description:       Zip deployment add-on for WP2Static.
 * Version:           1.0.1
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-zip
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP2STATIC_ZIP_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP2STATIC_ZIP_VERSION', '1.0.1' );

if ( file_exists( WP2STATIC_ZIP_PATH . 'vendor/autoload.php' ) ) {
    require_once WP2STATIC_ZIP_PATH . 'vendor/autoload.php';
}

if ( ! class_exists( 'WP2StaticZip\Controller' ) ) {
    if ( file_exists( WP2STATIC_ZIP_PATH . 'src/WP2StaticZipException.php' ) ) {
        require_once WP2STATIC_ZIP_PATH . 'src/WP2StaticZipException.php';

        throw new WP2StaticZip\WP2StaticZipException(
            'Looks like you\'re trying to activate this addon from source' .
            ' code, without compiling it first. Please see' .
            ' https://wp2static.com/compiling-from-source for assistance.'
        );
    }
}

function run_wp2static_addon_zip() : void {
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

