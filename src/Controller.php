<?php

namespace WP2StaticZip;

class Controller {
    public function run() : void {
        add_action(
            'admin_post_wp2static_zip_delete',
            [ $this, 'deleteZip' ],
            15,
            1
        );

        add_action(
            'wp2static_deploy',
            [ $this, 'generateZip' ],
            15,
            2
        );

        add_action(
            'admin_menu',
            [ $this, 'addOptionsPage' ],
            15,
            1
        );

        add_filter( 'parent_file', [ $this, 'setActiveParentMenu' ] );

        do_action(
            'wp2static_register_addon',
            'wp2static-addon-zip',
            'deploy',
            'ZIP Deployment',
            'https://wp2static.com/addons/zip/',
            'Deploys to ZIP archive'
        );

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static zip',
                [ CLI::class, 'zip' ]
            );
        }
    }

    public static function renderZipPage() : void {
        $view = [];
        $view['nonce_action'] = 'wp2static-zip-delete';
        $view['uploads_path'] = \WP2Static\SiteInfo::getPath( 'uploads' );
        $zip_path = \WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.zip';

        $view['zip_path'] = is_file( $zip_path ) ? $zip_path : false;

        if ( is_file( $zip_path ) ) {
            $view['zip_size'] = filesize( $zip_path );
            $view['zip_created'] = gmdate( 'F d Y H:i:s.', (int) filemtime( $zip_path ) );
        }

        $view['zip_url'] =
            is_file( $zip_path ) ?
                \WP2Static\SiteInfo::getUrl( 'uploads' ) . 'wp2static-processed-site.zip' : '#';

        require_once __DIR__ . '/../views/zip-page.php';
    }

    public function deleteZip( string $processed_site_path ) : void {
        \WP2Static\WsLog::l( 'Deleting deployable site ZIP file.' );
        check_admin_referer( 'wp2static-zip-delete' );

        $zip_path = \WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.zip';

        if ( is_file( $zip_path ) ) {
            unlink( $zip_path );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wp2static-zip' ) );
        exit;
    }

    public function generateZip( string $processed_site_path, string $enabled_deployer ) : void {
        if ( $enabled_deployer !== 'wp2static-addon-zip' ) {
            return;
        }

        $zip_archiver = new ZipArchiver();
        $zip_archiver->generateArchive( $processed_site_path );
    }

    public static function activate_for_single_site() : void {
    }

    public static function deactivate_for_single_site() : void {
    }

    public static function deactivate( bool $network_wide = null ) : void {
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::deactivate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::deactivate_for_single_site();
        }
    }

    public static function activate( bool $network_wide = null ) : void {
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::activate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::activate_for_single_site();
        }
    }

    public function addOptionsPage() : void {
        add_submenu_page(
            'options.php',
            'ZIP Deployment Options',
            'ZIP Deployment Options',
            'manage_options',
            'wp2static-addon-zip',
            [ $this, 'renderZipPage' ]
        );
    }

    // ensure WP2Static menu is active for addon
    public function setActiveParentMenu() : void {
            global $plugin_page;

        if ( 'wp2static-addon-zip' === $plugin_page ) {
            // phpcs:ignore
            $plugin_page = 'wp2static-options';
        }
    }
}
