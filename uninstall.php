<?php
/**
 * Uninstall INSPIRE Mobile Nav.
 *
 * Runs when the plugin is deleted from the Plugins screen; removes the
 * settings it saved so nothing is left behind in wp_options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'imn_settings' );
