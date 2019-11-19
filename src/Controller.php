<?php

namespace WP2StaticZip;

class Controller {
    const VERSION = '0.1';
    const PLUGIN_NAME = 'wp2static-addon-zip';
    const OPTIONS_KEY = 'wp2static-addon-zip-options';
    const HOOK = 'wp2static-addon-zip';

	public function __construct() {

	}

	public function run() {
        add_action(
            'wp2static_deploy',
            [ $this, 'generateZip' ],
            15,
            1);

        add_action(
            'wp2static_post_process_file',
            [ $this, 'convertURLsToOffline' ],
            15,
            2);

        add_action(
            'wp2static_set_destination_url',
            [ $this, 'setDestinationURL' ]);


        add_action(
            'wp2static_set_wordpress_site_url',
            [ $this, 'modifyWordPressSiteURL' ]);

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static zip',
                [ 'WP2StaticZip\CLI', 'zip' ]);
        }
	}

    public function modifyWordPressSiteURL( $site_url ) {
        return rtrim( $site_url, '/' );
    }

    public function setDestinationURL( $destination_url ) {
        return 'https://psqauretestnov19.netlify.com';
    }

    public function convertURLsToOffline( $file, $processed_site_path ) {
        error_log('Zip Addon converting URLs to offline in file: ' . $file);
        error_log('within ProcessedSite path: ' . $processed_site_path);

        error_log('Detect type of file by name, extension or content type');

        error_log('modify URL');

        // other actions can process after this, based on priority
    }

    public function generateZip( $processed_site_path ) {
        error_log('Zip Addon generating Zip');

        $zip_archiver = new ZipArchiver();
        $zip_archiver->generateArchive( $processed_site_path );
    }

    /*
     * Naive encypting/decrypting
     *
     */
    public static function encrypt_decrypt($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";

        $secret_key =
            defined( 'AUTH_KEY' ) ?
            constant( 'AUTH_KEY' ) :
            'LC>_cVZv34+W.P&_8d|ejfr]d31h)J?z5n(LB6iY=;P@?5/qzJSyB3qctr,.D$[L';

        $secret_iv =
            defined( 'AUTH_SALT' ) ?
            constant( 'AUTH_SALT' ) :
            'ec64SSHB{8|AA_ThIIlm:PD(Z!qga!/Dwll 4|i.?UkC§NNO}z?{Qr/q.KpH55K9';

        $key = hash('sha256', $secret_key);
        $variate = substr(hash('sha256', $secret_iv), 0, 16);

        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $variate);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $variate);
        }

        return $output;
    }

    public static function setDefaultOptions() : void {
        $encrypted_options =
            self::encrypt_decrypt('encrypt', 'someoptionsforzipaddon');

        update_option(self::OPTIONS_KEY, $encrypted_options);
    }

    public static function activate_for_single_site() : void {
        self::setDefaultOptions();
    }

    public static function deactivate_for_single_site() : void {
        error_log('deactivating zip addon, maintaining options');
        //delete_option(self::OPTIONS_KEY);
    }

    public static function deactivate( bool $network_wide = null ) : void {
        error_log('deactivating zip addon 2');
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
        error_log('activating zip addon 2');
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
}
