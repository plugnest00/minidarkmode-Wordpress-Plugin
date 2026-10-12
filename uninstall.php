<?php
/**
 * Removes all Mini Dark Mode options when the plugin is deleted.
 * (Current site only; no multisite network cleanup.)
 *
 * @package minidarkmode
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Deletes every option the plugin can create.
 *
 * The options are deleted one by one instead of with a single
 * "DELETE ... LIKE 'minidarkmode_%'" query: that keeps the options cache in
 * sync, and direct database calls are discouraged.
 *
 * Keep this list in sync with register_settings().
 */
function minidarkmode_delete_options() {
	$options = array(
		'minidarkmode_position',
		'minidarkmode_preset',
		'minidarkmode_button_style',
		'minidarkmode_show_icon',
		'minidarkmode_floating',
		'minidarkmode_shortcut',
		'minidarkmode_accent_headings',
		'minidarkmode_header_transparent',
		'minidarkmode_advanced',
		'minidarkmode_palette_brand',
		'minidarkmode_palette_brand_alt',
		'minidarkmode_palette_heading',
		'minidarkmode_palette_text',
		'minidarkmode_palette_bg_primary',
		'minidarkmode_palette_bg_secondary',
		'minidarkmode_palette_bg_subtle',
		'minidarkmode_palette_bg_alt',
		'minidarkmode_palette_other',
		'minidarkmode_theme_accent',
		'minidarkmode_theme_link',
		'minidarkmode_theme_link_hover',
		'minidarkmode_theme_heading',
		'minidarkmode_theme_text',
		'minidarkmode_theme_border',
		'minidarkmode_surface_bg',
		'minidarkmode_surface_content',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Anything else left under the plugin's prefix (options written by older
	// or unreleased builds) is swept up as well, so deleting the plugin never
	// leaves rows behind.
	foreach ( array_keys( wp_load_alloptions() ) as $option ) {
		if ( 0 === strpos( $option, 'minidarkmode_' ) ) {
			delete_option( $option );
		}
	}
}

minidarkmode_delete_options();
