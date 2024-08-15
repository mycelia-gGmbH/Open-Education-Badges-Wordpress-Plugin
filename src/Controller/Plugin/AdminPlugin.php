<?php

namespace DisruptiveElements\OpenEducationBadges\Controller\Plugin;

Use DisruptiveElements\OpenEducationBadges\Controller\Plugin;
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

class AdminPlugin {

	public function __construct() { }

	public static function register_hooks() {

		add_action('admin_init', [static::class, 'admin_init']);
		add_action('admin_menu', [static::class, 'admin_menu']);
	}

	public static function admin_init() {
		add_option('oeb_connections', [], '', false);

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
						$issuers = array_map(function($issuer) { return $issuer['slug']; }, $api_client->get_issuers());
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
	}

	public static function admin_menu() {
		$slug = 'oeb_admin';
		add_menu_page('Open Education Badges', 'Open Education Badges', 'manage_options', $slug, [static::class, 'page_oeb_admin']);
		add_submenu_page($slug, 'OEB Verbindungen', 'Verbindungen', 'manage_options', 'oeb_connections', [static::class, 'page_oeb_connections']);
	}

	public static function page_oeb_admin() {
		$oeb_page = 'oeb_admin';
		$oeb_connections = get_option('oeb_connections');
		$oeb_badges = Utils::get_all_badges();
		include Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_admin.php';
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
				$oeb_issuers = $api_client->get_issuers();
			}

			include Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_connections_edit.php';
		} else {
			include Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_connections.php';
		}

	}
}

