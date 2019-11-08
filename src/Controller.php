<?php

namespace WP2StaticZip;

class Controller {

	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '0.1';
		}

		$this->plugin_name = 'wp2static-addon-zip';
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

    public function postProcess() {
        error_log('Zip Addon post processing');
    }

    public function generateZip() {
        error_log('Zip Addon generate Zip');

        $zip_archiver = new ZipArchiver();
        $zip_archiver->GenerateArchive();
    }
}
