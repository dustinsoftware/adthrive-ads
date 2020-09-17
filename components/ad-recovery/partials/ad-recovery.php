<?php
/**
 * Ads partial view
 *
 * @package AdThrive Ads
 */

if ( ! defined( 'ADTHRIVE_ADS_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>

<script>
	if (!window.adthrive.config) {
		window.adblock_exp_val = 'off';
		if (Math.floor(Math.random() * 100) < 95) {
			window.adblock_exp_val = 'onpage';
			<?php
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $data['recovery_script'];
				// phpcs:enable
			?>
		}
	}
</script>
