<?php

namespace DisruptiveElements\OpenEducationBadges\Controller;

use DisruptiveElements\OpenEducationBadges\Controller\Plugin\AdminPlugin;
use DisruptiveElements\OpenEducationBadges\Controller\Plugin\ManualPlugin;

class Plugin {

	const PLUGIN_VERSION = '1.0.0';
	const PLUGIN_DIR = WP_PLUGIN_DIR . '/openeducationbadges/';


	public static function register_hooks() {

		AdminPlugin::register_hooks();
		ManualPlugin::register_hooks();
	}
}

