<?php

namespace DisruptiveElements\OpenEducationBadges\Controller\Plugin;

use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QROutputInterface;
Use DisruptiveElements\OpenEducationBadges\Controller\Plugin;
use DisruptiveElements\OpenEducationBadges\Entity\Badge;
use DisruptiveElements\OpenEducationBadges\Util\CachedApiWrapper;
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

Use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class AdminPlugin {

	public function __construct() { }

	public static function register_hooks() {

		add_action('admin_init', [static::class, 'admin_init']);
		add_action('admin_menu', [static::class, 'admin_menu']);
		add_filter('submenu_file', [static::class, 'submenu_file']);
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
			if ($_GET['page'] == 'oeb_connections' && !empty($_POST)) {

				$oeb_connection_id = $_GET['oeb_connection'] ?? '';
				$oeb_connection_name = $_POST['oeb_connection_name'] ?? '';
				$oeb_connection_clientid = $_POST['oeb_connection_clientid'] ?? '';
				$oeb_connection_clientsecret = $_POST['oeb_connection_clientsecret'] ?? '';
				$oeb_connection_baseurl = $_POST['oeb_connection_baseurl'] ?? '';

				if (isset($_POST['delete']) && $oeb_connection_id !== '') {
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
					if ($api_client = Utils::test_connection($oeb_connection_clientid, $oeb_connection_clientsecret, $oeb_connection_baseurl)) {
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
								'baseurl' => $oeb_connection_baseurl,
							];
							$option_connections[] = $new_connection;
						} else {
							foreach($option_connections as &$connection) {
								if ($connection['id'] == $oeb_connection_id) {
									$connection['name'] = $oeb_connection_name;
									$connection['client_id'] = $oeb_connection_clientid;
									$connection['client_secret'] = $oeb_connection_clientsecret;
									$connection['baseurl'] = $oeb_connection_baseurl;
									$connection['issuers'] = $_POST['oeb_connection_issuers']??[];
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

			// handle badge qr codes
			if ($_GET['page'] == 'oeb_admin' && isset($_GET['badge'])) {

				// save qr code
				if (isset($_GET['action']) && $_GET['action'] == 'qr' && !empty($_POST)) {
					if (wp_verify_nonce($_REQUEST['_wpnonce'], 'oeb-edit-qr-'.$_GET['badge'])) {

						$badges = Utils::get_all_badges();

						$badge_id = $_GET['badge'];
						$badge = Utils::array_find($badges, function($badge) use ($badge_id) { return $badge->id == $badge_id; });
						if (!empty($badge)) {

							if (isset($_POST['save'])) {
								$response = $badge->save_qrcode(
									$_GET['qr'],
									$_POST['oeb_qr_title'],
									$_POST['oeb_qr_createdBy'],
									$_POST['oeb_qr_valid_from'],
									$_POST['oeb_qr_expires_at']
								);
							} else if (isset($_POST['delete'])) {
								$badge->delete_qrcode($_GET['qr']);
							}

							wp_redirect(add_query_arg([
									'page'=> $_GET['page'],
									'badge' => $badge_id
								],
								admin_url('admin.php')
							));
							exit();
						}
					}
				}

				// delete assertion
				if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'assertions' && !empty($_POST)) {
					if (wp_verify_nonce($_REQUEST['_wpnonce'], 'oeb-delete-assertion-'.$_POST['assertion_id'])) {
						$badge_id = $_GET['badge'];
						$badge = Utils::get_badge($badge_id);
						$badge->retract($_POST['assertion_id']);
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

					// Utils::issue_by_badge($_GET['badge'], $emails);

					// $badges = Utils::get_all_badges();
					// $badge = Utils::array_find($badges, function($badge) use ($badge_id) { return $badge->id == $badge_id; });
					$badge_id = $_GET['badge'];
					$badge = Utils::get_badge($badge_id);
					if (!empty($badge)) {
						$badge->issue($emails);
					}


					// wp_redirect(add_query_arg([
					// 		'page'=> $_GET['page'],
					// 		'badge' => $_GET['badge'],
					// 	],
					// 	admin_url('admin.php')
					// ));
					// exit();
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
		add_menu_page('Open Education Badges', 'Open Education Badges', 'oeb_issue', $slug, [static::class, 'page_oeb_admin']);
		add_submenu_page($slug, 'OEB Einstellungen', 'Einstellungen', 'oeb_manage', 'oeb_settings', [static::class, 'page_oeb_settings']);
		add_submenu_page($slug, 'OEB Verbindungen', 'Verbindungen', 'oeb_manage', 'oeb_connections', [static::class, 'page_oeb_connections']);
		
		$oeb_connections = get_option('oeb_connections');
		if (!empty($oeb_connections)) {
			add_submenu_page($slug, 'OEB Badge vergeben', 'Badge vergeben', 'oeb_issue', 'oeb_issue', [static::class, 'page_oeb_issue']);
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
				$oeb_badge_qrcodes = $oeb_badge_object->get_qrcodes();
			}
		}

		if (isset($_GET['action']) && ($_GET['action'] == 'qr' || $_GET['action'] == 'qr-show')) {

			$oeb_qr_code = null;
			if (!empty($_GET['qr'])) {
				$oeb_qr_code_id = $_GET['qr'];
				$oeb_qr_code = Utils::array_find($oeb_badge_qrcodes, function($qr) use($oeb_qr_code_id) {
					return $qr->id == $oeb_qr_code_id;
				});
				if (empty($oeb_qr_code)) {
					return false;
				}
			}
			if ($_GET['action'] == 'qr') {
				$oeb_qr_title = $_POST['oeb_qr_title'] ?? $oeb_qr_code->title ?? '';
				$oeb_qr_createdBy = $_POST['oeb_qr_createdBy'] ?? $oeb_qr_code->createdBy ?? '';
				$oeb_qr_valid_from = $_POST['oeb_qr_valid_from'] ?? $oeb_qr_code->valid_from ? $oeb_qr_code->valid_from->format('Y-m-d') : '';
				$oeb_qr_expires_at = $_POST['oeb_qr_expires_at'] ?? $oeb_qr_code->expires_at ? $oeb_qr_code->expires_at->format('Y-m-d') : '';

				include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_admin_qr_edit.php');
			} else if ($_GET['action'] == 'qr-show') {
				$options = new QROptions();
				$options->connectPaths = true;
				$qrcode_svg = (new QRCode($options))->render($oeb_qr_code->url);
				$options->outputType = QROutputInterface::GDIMAGE_PNG;
				$options->scale = 10;
				$qrcode_png = (new QRCode($options))->render($oeb_qr_code->url);
				include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_admin_qr_show.php');
			}
		} else {
			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_admin.php');
		}
	}

	static function submenu_file($submenu_file) {
		global $plugin_page;

		$hidden_submenus = [
			'oeb_issue',
		];

		// Select another submenu item to highlight (optional).
		if ($plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
			$submenu_file = 'oeb_admin';
		}

		// Hide the submenu.
		foreach ($hidden_submenus as $submenu) {
			remove_submenu_page('oeb_admin', $submenu);
		}

		return $submenu_file;
	}

	public static function page_oeb_settings() {
		$oeb_page = 'oeb_settings';
		$oeb_settings = get_option('oeb_settings');
		include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_settings.php');
	}

	public static function page_oeb_connections() {
		$oeb_page = 'oeb_connections';

		$oeb_connections = get_option('oeb_connections');

		if (isset($_GET['create']) || isset($_GET['oeb_connection'])) {
			$oeb_connection_id = $_GET['oeb_connection'] ?? '';
			if ($oeb_connection_id != '') {
				$oeb_connection = Utils::get_connection($oeb_connection_id);
			}
			$oeb_connection_name = $_POST['oeb_connection_name'] ?? $oeb_connection['name'] ?? '';
			$oeb_connection_clientid = $_POST['oeb_connection_clientid'] ?? $oeb_connection['client_id'] ?? '';
			$oeb_connection_clientsecret = $_POST['oeb_connection_clientsecret'] ?? $oeb_connection['client_secret'] ?? '';
			$oeb_connection_issuers = $_POST['oeb_connection_issuers'] ?? $oeb_connection['issuers'] ?? [];
			$oeb_connection_baseurl = $_POST['oeb_connection_baseurl'] ?? $oeb_connection['baseurl'] ?? '';

			$oeb_issuers = [];
			if ($oeb_connection_id != '') {
				$oeb_api_client = Utils::get_api_client($oeb_connection_id);
				if (!empty($oeb_api_client)) {
					$oeb_issuers = CachedApiWrapper::api_request($oeb_api_client, 'get_issuers');
				}
			}

			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_connections_edit.php');
		} else {
			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_connections.php');
		}
	}

	public static function page_oeb_issue() {
		$oeb_page = 'oeb_issue';

		$oeb_badges = Utils::get_all_badges();
		if (!empty($_GET['badge'])) {
			$oeb_badge_entity_id = $_GET['badge'];
			$oeb_badge = Utils::array_find($oeb_badges, function($badge) use($oeb_badge_entity_id) {
				return $badge->id == $oeb_badge_entity_id;
			});
			$users = get_users();
			$oeb_badge_assertions = $oeb_badge->get_assertions();
			$oeb_badge_recipients = [];
			foreach($oeb_badge_assertions as $assertion) {
				$oeb_badge_recipients[] = $assertion->recipient;
			}
			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_issue_badge.php');
		} else {
			include realpath(Plugin::PLUGIN_DIR . 'templates/admin/page_oeb_issue.php');
		}
	}
}

