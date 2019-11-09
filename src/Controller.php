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
            'wp2static_crawling_complete',
            [ $this, 'postProcess' ],
            $args);

        add_action(
            'wp2static_post_processing_complete',
            [ $this, 'generateZip' ],
            $args);

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static zip',
                [ 'WP2StaticZip\CLI', 'zip' ]);
        }
	}

    public function postProcess( $args ) {
        do_action( 'wp2static_post_processing_commence', $args );

        error_log(print_r($args, true));
        error_log('Zip Addon post processing');

        /* 

        TODO: where is PostProcesing living?

        if in WP2Staatic Core, but we need to process differently for ZIP (offline URLs)

        do we do_action to have core do the post processing?

        if yes, we need to pass arg of destination_url + deployment name

        after core's PostProcessing, we need to then apply our own post_processing, to set URLs to offline mode...if that option is set in Zip Addon options

        PostProcessingCache can avoid us re-running for files that haven't changed

        use destination_url + deployment name in PostProcessingCache index to ignore when URL or deploy method changes.... else, could we use encrypted_options as index, seeing as it should be a unique combination of all options... so if any deployment option changes, we're ignoring that cache? extend to hash of all PostProcessingOptions, to auto-invalidate....

        $args = [
            'destinationURL' => 'https://somedomain.com',
            // postprocessoptions available in core function
            // 'postProcessOptionsHash' => $hashOfPostProcessingOptions,
            'encryptedDeployAddonOptions' => $encryptedOptions,
        ];

        ie: do_action('wp2static_post_process', $args)

        and in WP2Static\PostProcessor() check/add to PostProcessingCache,
        using passed args to form Cache key

        

        */


        error_log('reading files from StaticSite');

        error_log('processing each file and saving to processed dir');
        exec('cp -R ' . $args['staticSitePath'] . ' /tmp/processed');

        // add extensibility for other 
        do_action( 'wp2static_post_processing_complete', $args );
    }

    public function generateZip() {
        error_log('Zip Addon generate Zip');

        $zip_archiver = new ZipArchiver();
        $zip_archiver->GenerateArchive();
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
        error_log('activating zip addon');
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
