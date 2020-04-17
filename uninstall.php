<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// remove zip file if exists
$upload_path_and_url = wp_upload_dir();
$uploads_path = trailingslashit( $upload_path_and_url['basedir'] );
$processed_site_zip = $uploads_path . 'wp2static-processed-site.zip';

if ( is_file( $processed_site_zip ) ) {
    unlink();
}
