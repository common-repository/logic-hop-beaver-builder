<?php

	/*
		Plugin Name: Logic Hop Personalization for Beaver Builder Add-on
		Plugin URI:	https://logichop.com/docs/beaver_builder-add-on
		Description: The Logic Hop Personalization for Beaver Builder Add-on brings the power of personalization to WordPress with Beaver Builder Page Builder.
		Author: Logic Hop
		Version: 1.0.2
		Author URI: https://logichop.com
	*/

	if (!defined('ABSPATH')) die;

	if ( is_admin() ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'logichop/logichop.php' ) && ! is_plugin_active( 'logic-hop/logichop.php' ) ) {
			add_action( 'admin_notices', 'logichop_beaver_builder_plugin_notice' );
		}
	}

	function logichop_beaver_builder_plugin_notice () {
		$message = sprintf(__('The Logic Hop Personalization for Beaver Builder Add-on requires the Logic Hop plugin. Please download and activate the <a href="%s" target="_blank">Logic Hop plugin</a>.', 'logichop'),
							'http://wordpress.org/plugins/logic-hop/'
						);

		printf('<div class="notice notice-warning is-dismissible">
						<p>
							%s
						</p>
					</div>',
					$message
				);
	}

	/**
	 * Plugin activation/deactviation routine to clear Logic Hop transients
	 *
	 * @since    1.0.0
	 */
	function logichop_beaver_builder_activation () {
		delete_transient( 'logichop' );
  }
	register_activation_hook( __FILE__, 'logichop_beaver_builder_activation' );
	register_deactivation_hook( __FILE__, 'logichop_beaver_builder_activation' );

	if ( ! class_exists( 'LogicHop_BeaverBuilder' ) ) {
		 require_once( 'includes/beaver_builder.php' );
  }

	new LogicHop_BeaverBuilder();
