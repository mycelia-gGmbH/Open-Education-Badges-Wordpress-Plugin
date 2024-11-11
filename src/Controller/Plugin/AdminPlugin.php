<?php

namespace DisruptiveElements\OpenEducationBadges\Controller\Plugin;

Use DisruptiveElements\OpenEducationBadges\Controller\Plugin;
use DisruptiveElements\OpenEducationBadges\Entity\Badge;
use DisruptiveElements\OpenEducationBadges\Util\CachedApiWrapper;
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

class AdminPlugin {

	public function __construct() { }

	public static function register_hooks() {

		add_action('admin_init', [static::class, 'admin_init']);
		add_action('admin_menu', [static::class, 'admin_menu']);
	}

	public static function admin_init() {
		// init option value
		add_option('oeb_connections', [], '', false);
		add_option('oeb_settings', [], '', false);

		if (is_admin() && isset($_GET['page'])) {

			$asset_url = Plugin::PLUGIN_URL . 'assets';
			wp_enqueue_style("oeb-backend-style", "$asset_url/dist/backend.css");
			wp_enqueue_script("oeb-backend-script", "$asset_url/dist/backend.js");

			// handle oeb_connections save
			if ($_GET['page'] == 'oeb_connections' && isset($_GET['create']) || isset($_GET['edit'])) {

				$oeb_connection_id = $_GET['oeb_connection'] ?? '';
				$oeb_connection_name = $_POST['oeb_connection_name'] ?? '';
				$oeb_connection_clientid = $_POST['oeb_connection_clientid'] ?? '';
				$oeb_connection_clientsecret = $_POST['oeb_connection_clientsecret'] ?? '';

				if (isset($_POST['delete']) && !empty($oeb_connection_id)) {
					$option_connections = get_option('oeb_connections');
					$filtered_connections = array_filter($option_connections, function($connection) use ($oeb_connection_id) {
						return $connection['id'] != $oeb_connection_id;
					});
					update_option('oeb_connections', $filtered_connections);

					// redirect to overview
					wp_redirect(add_query_arg([
							'page'=> $_GET['page'],
						],
						admin_url('admin.php')
					));
					exit();

				} else if (!empty($oeb_connection_name) && !empty($oeb_connection_clientid) && !empty($oeb_connection_clientsecret)) {
					if ($api_client = Utils::test_connection($oeb_connection_clientid, $oeb_connection_clientsecret)) {
						$option_connections = get_option('oeb_connections');

						// create new
						if ($oeb_connection_id == '') {
							$autoinc_id = array_reduce($option_connections, function($carry, $connection) {
								return max($carry + 1, $connection['id'] + 1);
							}, 0);
							$issuers = array_map(function($issuer) { return $issuer['entityId']; }, $api_client->get_issuers());
							$new_connection = [
								'id' => $autoinc_id,
								'name' => $oeb_connection_name,
								'client_id' => $oeb_connection_clientid,
								'client_secret' => $oeb_connection_clientsecret,
								'issuers' => $issuers,
							];
							$option_connections[] = $new_connection;
						} else {
							foreach($option_connections as &$connection) {
								if ($connection['id'] == $oeb_connection_id) {
									$connection['name'] = $oeb_connection_name;
									$connection['client_id'] = $oeb_connection_clientid;
									$connection['client_secret'] = $oeb_connection_clientsecret;
									$connection['issuers'] = $_POST['oeb_issuers']??[];
								}
							}
						}

						update_option('oeb_connections', $option_connections);

						// TODO: success notice after redirect

						// redirect to overview
						wp_redirect(add_query_arg([
								'page'=> $_GET['page'],
							],
							admin_url('admin.php')
						));
						exit();

					} else {
						add_action('admin_notices', function() {
							?>
							<div class="notice notice-error is-dismissible">
								<p>Verbindung konnte nicht hergestellt werden, bitte Daten überprüfen.</p>
							</div>
							<?php
						});
					}
				}
			}

			// handle oeb_issue action
			if ($_GET['page'] == 'oeb_issue' && isset($_GET['badge'])) {
				if (!empty($_POST['oeb_users']) || !empty($_POST['oeb_emails'])) {

					$emails = [];
					if (!empty($_POST['oeb_users'])) {
						$users = get_users([
							'include' => $_POST['oeb_users']
						]);
						$emails = array_merge($emails, array_map(function($user) { return $user->user_email; }, $users));
					}
					if (!empty($_POST['oeb_emails'])) {
						$emails = array_merge($emails, array_filter(
							preg_split("/[,;\s]/",$_POST['oeb_emails']),
							function($email) {
								// TODO e-mail validation?
								return !empty($email);
							}
						));
					}

					Utils::issue_by_badge($_GET['badge'], $emails);

					wp_redirect(add_query_arg([
							'page'=> $_GET['page'],
						],
						admin_url('admin.php')
					));
					exit();
				}
			}

			if ($_GET['page'] == 'oeb_settings') {

				$oeb_settings = get_option('oeb_settings');
				if (empty($oeb_settings['cache_timeout'])) { $oeb_settings['cache_timeout'] = '60'; }

				if (isset($_POST['loglevel'])) {
					$oeb_settings['loglevel'] = $_POST['loglevel'];
				}
				if (isset($_POST['cache_timeout'])) {
					$oeb_settings['cache_timeout'] = $_POST['cache_timeout'];
				}
				update_option('oeb_settings', $oeb_settings);
			}
		}
	}

	public static function admin_menu() {
		$slug = 'oeb_admin';
		add_menu_page('Open Education Badges', 'Open Education Badges', 'manage_options', $slug, [static::class, 'page_oeb_admin']);
		add_submenu_page($slug, 'OEB Einstellungen', 'Einstellungen', 'manage_options', 'oeb_settings', [static::class, 'page_oeb_settings']);
		add_submenu_page($slug, 'OEB Verbindungen', 'Verbindungen', 'manage_options', 'oeb_connections', [static::class, 'page_oeb_connections']);
		
		$oeb_connections = get_option('oeb_connections');
		if (!empty($oeb_connections)) {
			add_submenu_page($slug, 'OEB Badge vergeben', 'Badge vergeben', 'manage_options', 'oeb_issue', [static::class, 'page_oeb_issue']);
		}
	}

	public static function page_oeb_admin() {
		$oeb_page = 'oeb_admin';
		$oeb_connections = get_option('oeb_connections');

		$oeb_issuers = Utils::get_issuers();
		$oeb_badge_objects = Utils::get_all_badges();
		// $oeb_badges = array_map(function(Badge $b) { return $b->api_data; }, $oeb_badge_objects);
		$oeb_badges = $oeb_badge_objects;
		$oeb_badge_entity_id = $_GET['badge'] ?? '';
		if (!empty($oeb_badge_entity_id)) {
			$oeb_badge_object = Utils::array_find($oeb_badge_objects, function($badge) use ($oeb_badge_entity_id) { return $badge->id == $oeb_badge_entity_id; });
			if (!empty($oeb_badge_object)) {
				$oeb_badge_assertions = $oeb_badge_object->get_assertions();
			}
		}
		include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_admin.php');
	}

	public static function page_oeb_settings() {
		$oeb_page = 'oeb_settings';
		$oeb_settings = get_option('oeb_settings');
		include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_settings.php');
	}

	public static function page_oeb_connections() {
		$oeb_page = 'oeb_connections';

		$oeb_connections = get_option('oeb_connections');

		if (isset($_GET['create']) || isset($_GET['edit'])) {
			$oeb_connection_id = $_GET['oeb_connection'] ?? '';
			if ($oeb_connection_id != '') {
				$oeb_connection = Utils::get_connection($oeb_connection_id);
			}
			$oeb_connection_name = $_POST['oeb_connection_name'] ?? $oeb_connection['name'] ?? '';
			$oeb_connection_clientid = $_POST['oeb_connection_clientid'] ?? $oeb_connection['client_id'] ?? '';
			$oeb_connection_clientsecret = $_POST['oeb_connection_clientsecret'] ?? $oeb_connection['client_secret'] ?? '';
			$oeb_connection_issuers = $_POST['oeb_connection_issuers'] ?? $oeb_connection['issuers'] ?? [];

			$oeb_issuers = [];
			if ($oeb_connection_id != '') {
				$api_client = Utils::get_api_client($oeb_connection_id);
				$oeb_issuers = CachedApiWrapper::api_request($api_client, 'get_issuers');
			}

			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_connections_edit.php');
		} else {
			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_connections.php');
		}
	}

	public static function page_oeb_issue() {
		$oeb_page = 'oeb_issue';

		$oeb_badges = Utils::get_all_badges();
		$oeb_badge_entity_id = $_GET['badge'] ?? '';
		$oeb_badge = Utils::array_find($oeb_badges, function($badge) use($oeb_badge_entity_id) {
			return $badge->id == $oeb_badge_entity_id;
		});
		if (isset($_GET['badge'])) {
			$users = get_users();
			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_issue_badge.php');
		} else {
			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_issue.php');
		}
	}
}

