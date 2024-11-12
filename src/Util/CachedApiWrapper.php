<?php

namespace DisruptiveElements\OpenEducationBadges\Util;

// Caches OEB API Request results per client_id for 60 seconds

class CachedApiWrapper {
	public static function api_request($api_client, $function_name, $parameters = []) {

		$transient_key = 'oeb_api_cache_' . $api_client->client_id . '_' . $function_name . '_' . md5(serialize($parameters));
		$result = get_transient($transient_key);

		if (empty($result)) {
			$result = call_user_func([$api_client, $function_name], ...$parameters);

			$oeb_settings = get_option('oeb_settings');
			if (empty($oeb_settings['cache_timeout'])) { $oeb_settings['cache_timeout'] = '60'; }

			set_transient($transient_key, $result, intval($oeb_settings['cache_timeout']));
		}

		return $result;
	}

	public static function clear_request($api_client, $function_name, $parameters = []) {
		$transient_key = 'oeb_api_cache_' . $api_client->client_id . '_' . $function_name . '_' . md5(serialize($parameters));
		delete_transient($transient_key);
	}

	public static function clear_cache($api_client) {
		global $wpdb;
		$sql = "SELECT `option_name` AS `name`, `option_value` AS `value`
					FROM  $wpdb->options
					WHERE `option_name` LIKE '%transient_oeb_api_cache_%'
					ORDER BY `option_name`";

		$results = $wpdb->get_results($sql);
		// foreach($results as $row) {
		// 	var_export($row);
		// }
	}
}