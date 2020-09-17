<?php
/**
 * Content Security Policy Main Class
 *
 * @package AdThrive Ads
 */

namespace AdThrive_Ads\Components\Content_Security_Policy;

/**
 * Main class
 */
class Main {

	/**
	 * Add hooks
	 */
	public function setup() {
		add_filter( 'adthrive_ads_options', array( $this, 'add_options' ), 15, 1 );

		add_action( 'wp_head', array( $this, 'csp_head' ) );
	}

	/**
	 * Add fields to the options metabox
	 *
	 * @param CMB $cmb A CMB metabox instance
	 */
	public function add_options( $cmb ) {
		$cmb->add_field( array(
			'name' => 'Add Content Security Policy',
			'desc' => 'Upgrade insecure requests and block all mixed content.',
			'id' => 'add_content_security_policy',
			'type' => 'checkbox',
		) );

		return $cmb;
	}

	/**
	 * Add the AdThrive ads script
	 */
	public function csp_head() {
		$add_content_security_policy = \AdThrive_Ads\Options::get( 'add_content_security_policy' );

		$https_forwarded = isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'];
		$ssl = is_ssl() || $https_forwarded;

		if ( $ssl && isset( $add_content_security_policy ) && 'on' === $add_content_security_policy ) {
			require 'partials/content-security-policy.php';
		}
	}
}
