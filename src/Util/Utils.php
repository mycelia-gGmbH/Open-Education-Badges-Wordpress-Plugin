<?php

namespace DisruptiveElements\OpenEducationBadges\Util;

use DisruptiveElements\OpenEducationBadges\Entity\Badge;
use DisruptiveElements\OpenEducationBadges\Entity\Issuer;
Use DisruptiveElements\OpenEducationBadges\Util\OpenEducationBadgesApi;

class Utils {

	static $api_clients = [];

	public static function array_find(array $arr, callable $fn) {
		$res = array_filter($arr, $fn);
		return reset($res);
	}

	public static function get_connection($connection_id) {
		$oeb_connections = get_option('oeb_connections');
		$oeb_connection = array_filter($oeb_connections, function($connection) use ($connection_id) {
			return $connection['id'] == $connection_id;
		});
		return reset($oeb_connection);
	}

	public static function get_api_client($connection_id) {

		if (empty(self::$api_clients[$connection_id])) {
			$connection = self::get_connection($connection_id);
			self::$api_clients[$connection_id] = new OpenEducationBadgesApi(
				$connection['client_id'],
				$connection['client_secret']
			);
			self::$api_clients[$connection_id]->set_store_token([static::class, 'store_token']);
			self::$api_clients[$connection_id]->set_retrieve_token([static::class, 'retrieve_token']);
		}
		return self::$api_clients[$connection_id];
	}

	public static function store_token($token, $api_client) {
		set_transient('oeb_token_' . $api_client->client_id, json_encode($token), $token['token_expires']);
	}

	public static function retrieve_token($api_client) {
		$token = get_transient('oeb_token_' . $api_client->client_id);
		if (!empty($token)) {
			return json_decode($token, JSON_OBJECT_AS_ARRAY);
		}
		return null;
	}

	public static function test_connection($client_id, $client_secret) {
		$test_api_client = new OpenEducationBadgesApi(
			$client_id,
			$client_secret
		);
		$test_api_client->set_store_token([static::class, 'store_token']);
		$test_api_client->set_retrieve_token(function() {});

		$token = $test_api_client->get_access_token();
		if (!empty($token)) {
			return $test_api_client;
		} else {
			return false;
		}
	}

	public static function get_issuers(): array {
		$issuers = [];
		$oeb_connections = get_option('oeb_connections');
		foreach($oeb_connections as $connection) {
			$api_client = self::get_api_client($connection['id']);
			$response = CachedApiWrapper::api_request($api_client, 'get_issuers');
			$response_issuers = array_map(function($r) use($connection) { return new Issuer($connection['id'], $r); }, $response);
			$issuers = array_merge($issuers, $response_issuers);
		}

		return $issuers;
	}

	public static function get_all_badges(): array {
		$badges = [];
		$oeb_connections = get_option('oeb_connections');
		foreach($oeb_connections as $connection) {
			$api_client = self::get_api_client($connection['id']);
			foreach($connection['issuers'] as $issuer_id) {
				// $badges = array_merge($badges, $api_client->get_badges($issuer_slug));
				// $badges = array_merge($badges, CachedApiWrapper::api_request($api_client, 'get_badges', [$issuer_slug]));
				$response = CachedApiWrapper::api_request($api_client, 'get_badges', [$issuer_id]);
				$response_badges = array_map(function($r) use($connection, $issuer_id) { return new Badge($connection['id'], $r, $issuer_id); }, $response);
				$badges = array_merge($badges, $response_badges);
			}
		}

		return $badges;
	}

	public static function issue_by_badge($badge_id, $emails) {
		$oeb_connections = get_option('oeb_connections');
		foreach($oeb_connections as $connection) {
			$api_client = self::get_api_client($connection['id']);
			foreach($connection['issuers'] as $issuer_id) {
				// $badges = $api_client->get_badges($issuer_slug);
				$badges = CachedApiWrapper::api_request($api_client, 'get_badges', [$issuer_id]);
				$target_badge = array_filter($badges, function($badge) use ($badge_id) {
					return $badge['entityId'] == $badge_id;
				});
				if (!empty($target_badge)) {
					foreach($emails as $email) {
						$api_client->issue_badge($issuer_id, $badge_id, $email);
					}
					return;
				}
			}
		}
	}

	public static function get_assertions_by_badge($badge_id) {
		$oeb_connections = get_option('oeb_connections');
		$assertions = [];
		foreach($oeb_connections as $connection) {
			$api_client = self::get_api_client($connection['id']);
			$assertions = array_merge($assertions, $api_client->get_assertions_by_badge($badge_id));
		}
		return $assertions;
	}

	public static function list_badges_by_email($email) {
		$oeb_connections = get_option('oeb_connections');
		$email_badge_ids = [];

		foreach($oeb_connections as $connection) {

			$api_client = self::get_api_client($connection['id']);

			// $api_issuers = $api_client->get_issuers();
			$api_issuers = CachedApiWrapper::api_request($api_client, 'get_issuers', []);
			$api_issuer_slugs = array_map(function($issuer) {
				return $issuer['entityId'];
			}, $api_issuers);

			foreach($connection['issuers'] as $issuer_slug) {

				// skip when missing in api issuers
				if (!in_array($issuer_slug, $api_issuer_slugs)) {
					continue;
				}

				// $assertions = $api_client->get_assertions($issuer_slug);
				$assertions = CachedApiWrapper::api_request($api_client, 'get_assertions', [$issuer_slug]);
				foreach ($assertions as $assertion) {
					if ($assertion['recipient']['type'] == 'email' && $assertion['recipient']['plaintextIdentity'] == $email) {
						$badge_id = preg_replace('(.*\/)', '', $assertion['badgeclass']);
						if (!in_array($badge_id, $email_badge_ids)) {
							$email_badge_ids[] = $badge_id;
						}
					}
				}
			}
		}

		if (!empty($email_badge_ids)) {
			$badges = self::get_all_badges();
			return array_filter($badges, function($badge) use ($email_badge_ids) {
				return in_array($badge->id, $email_badge_ids);
			});
		}

		return [];
	}
}

