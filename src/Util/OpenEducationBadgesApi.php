<?php


namespace DisruptiveElements\OpenEducationBadges\Util;

class OpenEducationBadgesApi {

	public $client_id = "";
	private $client_secret = "";
	private $api_base = "";

	private $username = "";
	private $password = "";

	private $store_token;
	private $retrieve_token;
	private $logfunc;
	private $loglevel = 'error';

	public function __construct(
		string $client_id = "",
		string $client_secret = "",
		string $api_base = "",
		string $username = "",
		string $password = "",
		callable $store_token = null,
		callable $retrieve_token = null
	) {

		$this->client_id = $client_id;
		$this->client_secret = $client_secret;

		if (empty($api_base)) { $api_base = 'https://api.openbadges.education/'; }

		$this->api_base = $api_base;
		$this->username = $username;
		$this->password = $password;
		$this->store_token = $store_token ?? [$this, 'store_token_default'];
		$this->retrieve_token = $retrieve_token ?? [$this, 'retrieve_token_default'];

		$settings = get_option('oeb_settings');
		if (!empty($settings) && !empty($settings['loglevel'])) {
			$this->loglevel = $settings['loglevel'];
		} else {
			$this->loglevel = 'error';
		}
	}

	public function set_store_token (callable $store_token) {
		$this->store_token = $store_token;
	}

	public function set_retrieve_token (callable $retrieve_token) {
		$this->retrieve_token = $retrieve_token;
	}

	public function set_log(callable $log) {
		$this->logfunc = $log;
	}

	public function log($msg, $level = 'error') {

		if ($this->loglevel == 'error') {
			if ($level == 'info' || $level == 'debug') {
				return false;
			}
		}
		if ($this->loglevel == 'info') {
			if ($level == 'debug') {
				return false;
			}
		}

		$msg = preg_replace('/"data:image\/png;base64,[^"]+"/', '"data:image\/png;base64,image-data-removed"', $msg);

		if ($this->logfunc) {
			call_user_func($this->logfunc, $this, $msg, $level);
		} else {
			// default logging
			if (function_exists('wc_get_logger')) {
				$logger = wc_get_logger();
				$logger->log(
					$level,
					$msg,
					[
						'source' => 'OpenEducationBadges'
					]
				);
			} else {
				if (!is_dir(WP_CONTENT_DIR.'/OpenEducationBadges')) {
					mkdir(WP_CONTENT_DIR.'/OpenEducationBadges');
			  	}
				$current_file = date('Y-m-d').'.log';
				file_put_contents(WP_CONTENT_DIR.'/OpenEducationBadges/'.$current_file, date('H:i:s')." $level: $msg\n", FILE_APPEND | LOCK_EX);
			}
		}
	}

	private function store_token_default($token) {
		file_put_contents(
			'access_token.json',
			json_encode($token)
		);
	}

	private function retrieve_token_default() {
		return json_decode(
			file_get_contents('access_token.json'),
			JSON_OBJECT_AS_ARRAY
		);
	}

	/**
	 * Get access token and the corresponding expiration timestamp.
	 *
	 * @return array access token and expiration timestamp
	 */
	public function get_access_token() {

		$token = call_user_func($this->retrieve_token, $this);
		if (!empty($token)) {
			// unset token if expired
			if ($token['token_retrieved'] + $token['token_expires'] <= time()) {
				unset($token);
			}
		}
		if (empty($token)) {
			$token = $this->request_access_token();
		}
		return $token;
	}

	/**
	 * API request
	 *
	 * @param string $method
	 * @param string $endpoint
	 * @param array $params
	 * @return mixed
	 */
	public function api_request(string $method, string $endpoint, array $params) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, False);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);

		$headerParams = [];
		$headerParams['Accept'] = 'application/json';


		$is_auth = false;
		// authorization
		if (strpos($endpoint, 'o/token/') === 0) {
			// auth using client_id
			if (!empty($this->client_id) && !empty($this->client_secret)) {
				$bearer = base64_encode($this->client_id . ":" . $this->client_secret);
				$headerParams['Authorization'] = 'Basic ' . $bearer;
				$is_auth = true;
			// auth using username and password
			} else if (!empty($this->username) && !empty($this->password)) {
				$is_auth = true;
			}
		// everything else
		} else {
			$token = $this->get_access_token();
			$headerParams['Authorization'] = 'Bearer ' . $token['access_token'];
		}


		$url = $this->api_base . $endpoint;

		$payload = http_build_query($params, "", "&");

		if ($method === 'get') {

			$url .= '?'.$payload;

		} else if ($method === 'post' || $method == 'put' || $method == 'delete') {

			if (!empty($params)) {
				if ($is_auth) {
					$headerParams['Content-Type'] = 'application/x-www-form-urlencoded';
				} else {
					$headerParams['Content-Type'] = 'application/json';
					$payload = json_encode($params);
				}
			}
			if ($method == 'post') {
				curl_setopt($ch, CURLOPT_POST, True);
			}
			if ($method == 'put') {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			}
			if ($method === 'delete') {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			}

			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		} else {
			return false;
		}

		// set URL
		curl_setopt($ch, CURLOPT_URL, $url);

		// set Headers
		$headers = [];
		foreach ($headerParams as $key => $val) {
			$headers[] = "$key: $val";
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$this->log(
			'Request: ' . var_export(
				[
					'url'=>$url,
					'payload'=>$payload,
					'headers'=>$headers
				],
				true
			),
			'debug'
		);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log('Response: ' . var_export(
				$response,
				true
			),
			'debug'
		);

		// decode response available
		if (!empty($response)) {
			$response = json_decode($response, true);
		}

		if ($http_code >= 200 && $http_code <= 300) {

		} else {
			$this->log(
				'API Error: ' . var_export(
					[
						'response' => $response,
						'http_code' => $http_code
					],
					true
				),
				'error'
			);
		}

		// return false;
		return $response;
	}
	private function get($endpoint, $params) {
		return $this->api_request('get', $endpoint, $params);
	}
	private function post($endpoint, $params) {
		return $this->api_request('post', $endpoint, $params);
	}
	private function put($endpoint, $params) {
		return $this->api_request('put', $endpoint, $params);
	}
	private function delete($endpoint, $params) {
		return $this->api_request('delete', $endpoint, $params);
	}

	private function request_access_token() {

		$retrieved_time = time();

		if (!empty($this->client_id) && !empty($this->client_secret)) {
			$response = $this->post('o/token/', [
				'grant_type' => 'client_credentials',
				'scope' => 'rw:profile rw:issuer rw:backpack',
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
			]);
		} else if (!empty($this->username) && !empty($this->password)) {
			$response = $this->post('o/token/', [
				'grant_type' => 'password',
				'scope' => 'rw:profile rw:issuer rw:backpack',
				'client_id' => 'public',
				'username' => $this->username,
				'password' => $this->password,
			]);
		} else {
			throw new \Exception('No API credentials given');
		}

		if (!empty($response)) {

			$token = array(
				'access_token' => $response['access_token'],
				'token_expires' => $response['expires_in'],
				'token_retrieved' => $retrieved_time,
			);

			call_user_func($this->store_token, $token, $this);

			return $token;
		}
	}

	public function get_all_badges() {
		$response = $this->get("v1/issuer/all-badges", []);
		return $response;
	}

	public function get_issuers() {
		// $response = $this->get("v1/issuer/issuers", []);
		// return $response;
		$response = $this->get("v2/issuers", []);
		if (!empty($response) && $response['status']['success']) {
			return $response['result'];
		}
		return false;
	}

	public function get_badges($issuer) {
		// $response = $this->get("v1/issuer/issuers/$issuer/badges", []);
		// return $response;
		$response = $this->get("v2/issuers/$issuer/badgeclasses", []);
		if (!empty($response)) {
			if ($response['status']['success']) {
				return $response['result'];
			}
		}
		return false;
	}

	public function issue_badge($issuer, $badge, $recipient) {

		// $response = $this->post("v1/issuer/issuers/$issuer/badges/$badge/assertions", [
		// 	"badge_class" => $badge,
		// 	"create_notification" => true,
		// 	"evidence_items" => [],
		// 	"issuer" => $issuer,
		// 	"narrative" => "",
		// 	"recipient_identifier" => $recipient,
		// 	"recipient_type" => "email"
		// ]);

		// if (!empty($response)) {
		// 	$this->log(
		// 		'Issued Badge: ' . var_export(
		// 			[
		// 				'issuer' => $issuer,
		// 				'badge' => $badge,
		// 				'recipient' => $recipient
		// 			],
		// 			true
		// 		),
		// 		'info'
		// 	);
		// }
		// return $response;


		$response = $this->post("v2/issuers/$issuer/assertions", [
			"badgeclass" => $badge,
			"recipient" => [
				"identity" => $recipient
			]
		]);

		if ($response['status']['success']) {
			return $response['result'];
		}
		return false;
	}

	public function retract_badge($assertion_id) {
		$response = $this->delete("v2/assertions/$assertion_id", [
			"revocation_reason" => "Manually revoked by Issuer"
		]);
		if ($response['status']['success']) {
			return $response['result'];
		}
		return false;
	}

	public function get_assertions($issuer) {
		// $response = $this->get("v1/issuer/issuers/$issuer/assertions", []);
		// return $response;
		$response = $this->get("v2/issuers/$issuer/assertions", []);
		if ($response['status']['success']) {
			return $response['result'];
		}
		return false;
	}

	public function get_assertions_by_badge($badge) {
		$response = $this->get("v2/badgeclasses/$badge/assertions", []);
		if ($response['status']['success']) {
			return $response['result'];
		}
		return false;
	}

	// FIXME: no v2 qr api available
	public function get_qrcodes($issuer, $badge) {
		$response = $this->get("v1/issuer/issuers/$issuer/badges/$badge/qrcodes", []);
		if (!empty($response)) {
			return $response;
		}
		return false;
	}
	public function create_qrcode($issuer, $badge, $data) {
		// FIXME: required even though in endpoint URL
		$data['issuer_id'] = $issuer;
		$data['badgeclass_id'] = $badge;

		$response = $this->post("v1/issuer/issuers/$issuer/badges/$badge/qrcodes", $data);
		if (!empty($response)) {
			return $response;
		}
		return false;
	}
	public function update_qrcode($issuer, $badge, $qrcode, $data) {
		// FIXME: required even though in endpoint URL
		$data['issuer_id'] = $issuer;
		$data['badgeclass_id'] = $badge;

		$response = $this->put("v1/issuer/issuers/$issuer/badges/$badge/qrcodes/$qrcode", $data);
		if (!empty($response)) {
			return $response;
		}
		return false;
	}
	public function delete_qrcode($issuer, $badge, $qrcode) {
		// FIXME: required even though in endpoint URL
		$data['issuer_id'] = $issuer;
		$data['badgeclass_id'] = $badge;

		$response = $this->delete("v1/issuer/issuers/$issuer/badges/$badge/qrcodes/$qrcode", $data);
		if (!empty($response)) {
			return $response;
		}
		return false;
	}

	public function get_client_id() {
		return $this->client_id;
	}
}
