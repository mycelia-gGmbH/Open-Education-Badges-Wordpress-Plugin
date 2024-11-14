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

		remove_role('oeb_manage');
		add_role(
			'oeb_manage',
			'OpenEducationBadges: Manager',
			[
				'oeb_manage' => true,
				'oeb_issue' => true,
				'read' => true,
            'level_0' => true
			]
	  	);
		remove_role('oeb_issue');
		add_role(
			'oeb_issue',
			'OpenEducationBadges: Badge vergeben',
			[
				'oeb_issue' => true,
				'read' => true,
            'level_0' => true,
			]
	  	);

		$admin_role = get_role('administrator');
		$admin_role->add_cap('oeb_issue', true);
		$admin_role->add_cap('oeb_manage', true);

		// $user = wp_get_current_user();
		// var_export($user->allcaps); die();

		AdminPlugin::register_hooks();
		BlocksPlugin::register_hooks();
		// ManualPlugin::register_hooks();
	}
}

