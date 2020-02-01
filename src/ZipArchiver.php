<?php

namespace WP2StaticZip;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ZipArchiver {

    public function __construct() {
        error_log('constructing Wp2StaticZip\ZipArchiver');
    }

    public function generateArchive( $processed_site_path ) {
        error_log('Generating ZIP archive from:' . $processed_site_path);

        $archive_path = rtrim( $processed_site_path, '/' );
        $temp_zip = $archive_path . '.tmp';

        $zip_archive = new ZipArchive();

        if ( $zip_archive->open( $temp_zip, ZipArchive::CREATE ) !== true ) {
            $err = 'Could not create zip: ' . $temp_zip;
            WsLog::l( $err );
            throw new WP2StaticException( $err );
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $processed_site_path,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ( $iterator as $filename => $file_object ) {
            $base_name = basename( $filename );
            if ( $base_name != '.' && $base_name != '..' ) {
                $real_filepath = realpath( $filename );

                if ( ! $real_filepath ) {
                    $err = 'Trying to add unknown file to Zip: ' . $filename;
                    WsLog::l( $err );
                    throw new WP2StaticException( $err );
                }

                // Standardise all paths to use / (Windows support)
                $filename = str_replace( '\\', '/', $filename );

                if ( ! is_string( $filename ) ) {
                    continue;
                }

                if ( ! $zip_archive->addFile(
                    $real_filepath,
                    str_replace( $processed_site_path, '', $filename )
                )
                ) {
                    $err = 'Could not add file: ' . $filename;
                    WsLog::l( $err );
                    throw new WP2StaticException( $err );
                }
            }
        }

        $zip_archive->close();

        $zip_path = $processed_site_path . '.zip';

        rename( $temp_zip, $zip_path );

        chmod( $zip_path, 0644 );
    }
}
