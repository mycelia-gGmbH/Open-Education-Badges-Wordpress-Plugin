<?php

namespace DisruptiveElements\OpenEducationBadges\Entity;

use DisruptiveElements\OpenEducationBadges\Util\CachedApiWrapper;
use DisruptiveElements\OpenEducationBadges\Util\Utils;

class Badge extends ApiObject {
	public string $issuer_id;
	public \DateTime $created;
	public \DateTime $updated;

	public string $name;
	public string $image;
	public string $description;

	public string $duration = '';
	public string $category = '';
	public array $competencies = [];

	public function __construct(string $connection, array $api_data, string $issuer_id) {
		$this->connection = $connection;
		$this->api_data = $api_data;
		$this->id = $api_data['entityId'];
		$this->issuer_id = $issuer_id;

		$this->name = $api_data['name'];
		$this->image = $api_data['image'];
		$this->description = $api_data['description'];

		$this->created = new \DateTime($this->api_data['createdAt']);

		if (!empty($api_data['extensions'])) {
			if (!empty($api_data['extensions']['extensions:StudyLoadExtension'])) {
				$this->duration = $api_data['extensions']['extensions:StudyLoadExtension']['StudyLoad'];
			}
			if (!empty($api_data['extensions']['extensions:CategoryExtension'])) {
				$this->category = $api_data['extensions']['extensions:CategoryExtension']['Category'] === 'competency' ? 'Kompetenz- Badge' : 'Teilnahme- Badge';
			}
			if (!empty($api_data['extensions']['extensions:CompetencyExtension'])) {
				$this->competencies = $api_data['extensions']['extensions:CompetencyExtension'];
				foreach($this->competencies as &$competency) {
					$competency['category'] = $competency['category'] == 'skill' ? 'Fähigkeit' : 'Wissen';
				}
			}
		}

	}

	public function get_assertions() {
		$api_client = Utils::get_api_client($this->connection);
		$response = $api_client->get_assertions_by_badge($this->id);
		$assertions = array_map(function($r) { return new Assertion($this->connection, $r, $this->id, $this->issuer_id); }, $response);
		return $assertions;
	}

	public function get_qrcodes() {
		$api_client = Utils::get_api_client($this->connection);
		$response = CachedApiWrapper::api_request($api_client, 'get_qrcodes', [$this->issuer_id, $this->id]);
		$qrcodes = [];
		if (!empty($response)) {
			$qrcodes = array_map(function($qrcode) {
				return new QrCode($this->connection, $qrcode, $this->issuer_id, $this->id);
			}, $response);
		}
		return $qrcodes;
	}

	public function save_qrcode($qrcode_id, $title, $createdBy, $valid_from, $expires_at) {
		$api_client = Utils::get_api_client($this->connection);

		$data = [
			'title' => $title,
			'createdBy' => $createdBy,
		];
		if (!empty($valid_from)) {
			$dt_valid_from = \DateTime::createFromFormat('Y-m-d', $valid_from);
			if (!empty($dt_valid_from)) {
				$data['valid_from'] = $dt_valid_from->format('Y-m-d\T00:00:00');
			}
		}
		if (!empty($expires_at)) {
			$dt_expires_at = \DateTime::createFromFormat('Y-m-d', $expires_at);
			if (!empty($dt_valid_from)) {
				$data['expires_at'] = $dt_expires_at->format('Y-m-d\T00:00:00');
			}
		}

		if (empty($qrcode_id)) {
			$response = $api_client->create_qrcode($this->issuer_id, $this->id, $data);
		} else {
			$response = $api_client->update_qrcode($this->issuer_id, $this->id, $qrcode_id, $data);
		}

		CachedApiWrapper::clear_request($api_client, 'get_qrcodes', [$this->issuer_id, $this->id]);
	}

	public function delete_qrcode($qrcode_id) {
		$api_client = Utils::get_api_client($this->connection);
		$api_client->delete_qrcode($this->issuer_id, $this->id, $qrcode_id);
		CachedApiWrapper::clear_request($api_client, 'get_qrcodes', [$this->issuer_id, $this->id]);
	}
}