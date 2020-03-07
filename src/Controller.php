<?php

namespace WP2StaticZip;

class Controller {
	public function run() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_zip_options';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            value VARCHAR(255) NOT NULL,
            label VARCHAR(255) NULL,
            description VARCHAR(255) NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // check for seed data
        // if deployment_url option doesn't exist, create:
        // $options = $this->getOptions();

        // if ( ! isset( $options['deployment_url'] ) ) {
        //     $this->seedOptions();
        // }

        add_filter( 'wp2static_add_menu_items', [ 'WP2StaticZip\Controller', 'addSubmenuPage' ] );

        // TOOD: used only if adding to core Options view
        // add_filter(
        //     'wp2static_render_options_page_vars',
        //     [ $this, 'addOptionsTemplateVars' ],
        //     15,
        //     1);

        // add_action(
        //     'wp2static_addon_ui_save_options',
        //     [ $this, 'uiSaveOptions' ],
        //     15,
        //     1);

        add_action(
            'admin_post_wp2static_zip_delete',
            [ $this, 'deleteZip' ],
            15,
            1);

        add_action(
            'wp2static_deploy',
            [ $this, 'generateZip' ],
            15,
            1);

        // add_action(
        //     'wp2static_post_process_file',
        //     [ $this, 'convertURLsToOffline' ],
        //     15,
        //     2);

        // add_action(
        //     'wp2static_set_destination_url',
        //     [ $this, 'setDestinationURL' ]);


        add_action(
            'wp2static_set_wordpress_site_url',
            [ $this, 'modifyWordPressSiteURL' ]);

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static zip',
                [ 'WP2StaticZip\CLI', 'zip' ]);
        }
	}

    // TODO: is this needed? confirm slashing of destination URLs...
    public function modifyWordPressSiteURL( $site_url ) {
        return rtrim( $site_url, '/' );
    }

    // public function setDestinationURL( $destination_url ) {
    //     $options = $this->getOptions();

    //     return $options['deployment_url']->value;
    // }

    // TODO: should be own addon for offline files
    // public function convertURLsToOffline( $file, $processed_site_path ) {
    //     WsLog::l('Zip Addon converting URLs to offline in file: ' . $file);
    //     error_log('within ProcessedSite path: ' . $processed_site_path);
    //     error_log('Detect type of file by name, extension or content type');
    //     error_log('modify URL');

    //     // other actions can process after this, based on priority
    // }

    /**
     *  Get all add-on options
     *
     *  @return mixed[] All options
     */
    // public function getOptions() : array {
    //     global $wpdb;
    //     $options = [];

    //     $table_name = $wpdb->prefix . 'wp2static_addon_zip_options';

    //     $rows = $wpdb->get_results( "SELECT * FROM $table_name" );

    //     foreach($rows as $row) {
    //         $options[$row->name] = $row;
    //     }

    //     return $options;
    // }

    /**
     * Seed options
     *
     */
    // public static function seedOptions() : void {
    //     global $wpdb;

    //     $table_name = $wpdb->prefix . 'wp2static_addon_zip_options';

    //     $query_string = "INSERT INTO $table_name (name, value, label, description) VALUES (%s, %s, %s, %s);";
    //     $query = $wpdb->prepare(
    //         $query_string,
    //         'deployment_url',
    //         'https://example.com',
    //         'Deployment URL',
    //         'The URL your static site will be published to');

    //     $wpdb->query( $query );
    // }

    /**
     * Save options
     *
     */
    // public static function saveOption( $name, $value ) : void {
    //     global $wpdb;

    //     $table_name = $wpdb->prefix . 'wp2static_addon_zip_options';

    //     $query_string = "INSERT INTO $table_name (name, value) VALUES (%s, %s);";
    //     $query = $wpdb->prepare( $query_string, $name, $value );

    //     $wpdb->query( $query );
    // }

    public static function renderZipPage() : void {
        $view = [];
        $view['nonce_action'] = 'wp2static-zip-delete';
        $view['uploads_path'] = \WP2Static\SiteInfo::getPath('uploads');
        $zip_path = \WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.zip';

        $view['zip_path'] = is_file( $zip_path ) ?  $zip_path : false;

        if ( is_file( $zip_path ) ) {
            $view['zip_size'] = filesize(\WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.zip');
            $view['zip_created'] = date ("F d Y H:i:s.", filemtime(\WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.zip') );
        }

        $view['zip_url'] =
            is_file( $zip_path ) ?
                \WP2Static\SiteInfo::getUrl( 'uploads' ) . 'wp2static-processed-site.zip' : '#';

        require_once __DIR__ . '/../views/zip-page.php';
    }

    // public function addOptionsTemplateVars( $template_vars ) {
    //     $template_vars['wp2static_zip_addon_options'] = $this->getOptions();

    //     // find position of deploy options
    //     $deployment_options_position = 0;
    //     foreach( $template_vars['options_templates'] as $index => $options_template ) {
    //       if (strpos($options_template, 'core-deployment-options.php') !== false) {
    //         $deployment_options_position = $index + 1;
    //       } 
    //     } 

    //     // insert zip deploy options template after that
    //     array_splice(
    //         $template_vars['options_templates'],
    //         $deployment_options_position,
    //         0, // # elements to remove
    //         [__DIR__ . '/../views/deploy-options.php']
    //     );

    //     return $template_vars;
    // }

    // TODO: use in other addons needing to add to core options
    // public function uiSaveOptions() {
    //     error_log('Zip Addon Saving Options, accessing $_POST');

    //     if (isset($_POST['deployment_url'])) {
    //         // TODO: validate URL
    //         $this->saveOption( 'deployment_url', $_POST['deployment_url'] );
    //     }
    // }

    public function deleteZip( $processed_site_path ) {
        \WP2Static\WsLog::l( 'Deleting deployable site ZIP file.');
        check_admin_referer( 'wp2static-zip-delete' );

        $zip_path = \WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.zip';

        if ( is_file( $zip_path ) ) {
            unlink( $zip_path );
        }

        wp_redirect(admin_url('admin.php?page=wp2static-zip'));
        exit;
    }

    public function generateZip( $processed_site_path ) {
        \WP2Static\WsLog::l('Zip Addon generating Zip');

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

    public static function activate_for_single_site() : void {
        self::setDefaultOptions();
    }

    public static function deactivate_for_single_site() : void {
        error_log('deactivating zip addon, maintaining options');
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
        error_log('activating zip addon');
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

    public static function addSubmenuPage( $submenu_pages ) {
        $submenu_pages['zip'] = [ 'WP2StaticZip\Controller', 'renderZipPage' ];

        return $submenu_pages;
    }
}
