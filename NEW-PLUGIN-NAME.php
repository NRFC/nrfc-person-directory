<?php

/**
 * Plugin Name: NEW_PLUGIN_NAME
 * Description: NEW_PLUGIN_NAME
 * Version: 1.0.0
 * Author: Toby Batch <tech@nfn.org.uk>
 * License: MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/src/NEW_PLUGIN_NAME.class.php';

/**
 * Settings Page
 */
add_action(
	'admin_menu',
	function () {
		add_options_page(
			'NEW_PLUGIN_NAME Settings',
			'NEW_PLUGIN_NAME',
			'manage_options',
			'NEW_PLUGIN_NAME',
			'NEW_PLUGIN_NAME_render_settings_page'
		);
	}
);

/**
 * Register Settings
 */
add_action(
	'admin_init',
	function () {
		register_setting( 'NEW_PLUGIN_NAME_group', 'NEW_PLUGIN_NAME_settings' );
	}
);

/**
 * Render Settings Page
 */
function NEW_PLUGIN_NAME_render_settings_page() {
	$settings = get_option( 'NEW_PLUGIN_NAME_settings', array() );

        ?>
	<div class="wrap">
		<h1>NEW_PLUGIN_NAME Settings</h1>
	</div>
	<?php
}
