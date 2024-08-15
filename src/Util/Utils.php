<?php

namespace DisruptiveElements\OpenEducationBadges\Util;

Use DisruptiveElements\OpenEducationBadges\Util\OpenEducationBadgesApi;

class Utils {

	static $api_clients = [];

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

	public static function get_all_badges() {
		$oeb_connections = get_option('oeb_connections');
		$badges = [];
		foreach($oeb_connections as $connection) {
			$api_client = self::get_api_client($connection['id']);
			foreach($connection['issuers'] as $issuer_slug) {
				// $badges = array_merge($badges, $api_client->get_badges($issuer_slug));
				$badges = array_merge($badges, CachedApiWrapper::api_request($api_client, 'get_badges', [$issuer_slug]));
			}
		}

		return $badges;
	}

	public static function issue_by_badge($badge_slug, $users) {
		$oeb_connections = get_option('oeb_connections');
		foreach($oeb_connections as $connection) {
			$api_client = self::get_api_client($connection['id']);
			foreach($connection['issuers'] as $issuer_slug) {
				// $badges = $api_client->get_badges($issuer_slug);
				$badges = CachedApiWrapper::api_request($api_client, 'get_badges', [$issuer_slug]);
				$target_badge = array_filter($badges, function($badge) use ($badge_slug) {
					return $badge['slug'] == $badge_slug;
				});
				if (!empty($target_badge)) {
					foreach($users as $user) {
						$api_client->issue_badge($issuer_slug, $badge_slug, $user->user_email);
						// var_export('issue: ' . var_export([$issuer_slug, $badge_slug, $user->user_email], true));
					}
					return;
				}
			}
		}
	}
}

