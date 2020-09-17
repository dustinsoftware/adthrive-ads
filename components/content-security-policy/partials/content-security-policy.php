<?php
/**
 * Content Security Policy partial view
 *
 * @package AdThrive Ads
 */

if ( ! defined( 'ADTHRIVE_ADS_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests;block-all-mixed-content" />
