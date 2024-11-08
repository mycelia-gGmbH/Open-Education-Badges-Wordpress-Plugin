<?php

namespace DisruptiveElements\OpenEducationBadges\Util;

// Caches OEB API Request results per client_id for 60 seconds

class CachedApiWrapper {
	public static function api_request($api_client, $function_name, $parameters = []) {

		$transient_key = 'oeb_api_cache_' . $api_client->client_id . '_' . $function_name . '_' . md5(serialize($parameters));
		$result = get_transient($transient_key);

		if (empty($result)) {
			$result = call_user_func([$api_client, $function_name], ...$parameters);
			set_transient($transient_key, $result, 600);
		}

		return $result;
	}
}