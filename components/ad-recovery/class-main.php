<?php
/**
 * Ad Recovery Main Class
 *
 * @package AdThrive Ads
 */

namespace AdThrive_Ads\Components\Ad_Recovery;

/**
 * Main class
 */
class Main {
	private $remote = 'http://ctrl.getpublica.com/14577988-820a-4422-9851-3a60e1386eaa-bootstrap.js?token=_gZMlwgUc0-uHOrdXZf0VlGKo9yFmP6HggxMaL';
	private $filename = ADTHRIVE_ADS_PATH . 'recovery.js';

	/**
	 * Add hooks
	 */
	public function setup() {
		add_action( 'wp_footer', array( $this, 'ad_recovery' ) );

		add_action( 'adthrive_daily_event', array( $this, 'adthrive_daily_event' ) );

		add_filter( 'adthrive_ads_options', array( $this, 'add_options' ), 15, 1 );

		add_filter( 'adthrive_ads_updated', array( $this, 'options_updated' ), 10, 3 );

		register_deactivation_hook( ADTHRIVE_ADS_FILE, array( $this, 'plugin_deactivated' ) );
	}

	/**
	 * Add the Ad Block Recovery script
	 */
	public function ad_recovery() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$recovery_enabled = \AdThrive_Ads\Options::get( 'ad_recovery' );

		if ( 'off' !== $recovery_enabled ) {
			$data['recovery_script'] = $this->get();

			if ( $data['recovery_script'] ) {
				require 'partials/ad-recovery.php';
			}
		}
	}

	/**
	 * Add fields to the options metabox
	 *
	 * @param CMB $cmb A CMB metabox instance
	 */
	public function add_options( $cmb ) {
		$cmb->add_field( array(
			'name' => 'Ad Block Recovery',
			'desc' => 'Show ads to users with ad blockers enabled.',
			'id' => 'ad_recovery',
			'type' => 'radio_inline',
			'options' => array(
				'on' => 'On',
				'off' => 'Off',
			),
			'default' => 'on',
		) );

		return $cmb;
	}

	/**
	 * Deactivation hook - delete the recovery.js file
	 */
	public function plugin_deactivated() {
		$this->delete();
	}

	/**
	 * Called when the adthrive_ads option is updated
	 */
	public function options_updated( $old_value, $value, $option ) {
		if ( 'off' !== $value['ad_recovery'] ) {
			$this->save();
		} else {
			$this->delete();
		}
	}

	/**
	 * Update the ads.txt file daily
	 */
	public function adthrive_daily_event() {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$recovery_enabled = \AdThrive_Ads\Options::get( 'ad_recovery' );

		if ( 'off' !== $recovery_enabled ) {
			$this->save();
		}
	}

	/**
	 * Get the local recovery file
	 */
	private function get() {
		if ( \WP_Filesystem() ) {
			global $wp_filesystem;

			if ( $wp_filesystem->exists( ADTHRIVE_ADS_PATH . 'recovery.js' ) ) {
				return $wp_filesystem->get_contents( ADTHRIVE_ADS_PATH . 'recovery.js' );
			}
		}

		return false;
	}

	/**
	 * Get and save the latest recovery file
	 */
	private function save() {
		// use a transient to store the etag?
		$response = wp_remote_get( $this->remote );

		if ( ! is_wp_error( $request ) && is_array( $response ) ) {
			if ( \WP_Filesystem() ) {
				global $wp_filesystem;

				$wp_filesystem->put_contents( $this->filename, wp_remote_retrieve_body( $response ), FS_CHMOD_FILE );
			}
		}
	}

	/**
	 * Delete the recovery file
	 */
	private function delete() {
		if ( \WP_Filesystem() ) {
			global $wp_filesystem;

			if ( $wp_filesystem->is_file( $this->filename ) ) {
				$wp_filesystem->delete( $this->filename );
			}
		}
	}
}
