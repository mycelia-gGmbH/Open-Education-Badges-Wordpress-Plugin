<?php

namespace DisruptiveElements\OpenEducationBadges\Controller;

use DisruptiveElements\OpenEducationBadges\Controller\Plugin\AdminPlugin;
use DisruptiveElements\OpenEducationBadges\Controller\Plugin\BlocksPlugin;
use DisruptiveElements\OpenEducationBadges\Controller\Plugin\ManualPlugin;

class Plugin {

	const PLUGIN_VERSION = '1.0.0';
	const PLUGIN_DIR = WP_PLUGIN_DIR . '/openeducationbadges/';
	const PLUGIN_URL = WP_PLUGIN_URL . '/openeducationbadges/';


	public static function register_hooks() {

		if (!wp_roles()->is_role('oeb_issue')) {
			add_role('oeb_issue', 'OpenEducationBadges: Badge vergeben', []);
		}

		AdminPlugin::register_hooks();
		BlocksPlugin::register_hooks();
		// ManualPlugin::register_hooks();
	}
}

