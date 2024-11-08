<?php

namespace DisruptiveElements\OpenEducationBadges\Entity;

use DisruptiveElements\OpenEducationBadges\Util\Utils;

class Badge extends ApiObject {
	public string $issuer_id;
	public \DateTime $created;
	public \DateTime $updated;

	public string $duration;
	public string $category;

	public function __construct(string $connection, array $api_data, string $issuer_id) {
		$this->connection = $connection;
		$this->api_data = $api_data;
		$this->id = $api_data['entityId'];
		$this->issuer_id = $issuer_id;

		$this->created = new \DateTime($this->api_data['createdAt']);

		$this->duration = $api_data['extensions']['extensions:StudyLoadExtension']['StudyLoad'];
		$this->category = $api_data['extensions']['extensions:CategoryExtension']['Category'] === 'competency' ? 'Kompetenz- Badge' : 'Teilnahme- Badge';
	}

	public function get_assertions() {
		$api_client = Utils::get_api_client($this->connection);
		$response = $api_client->get_assertions_by_badge($this->id);
		$assertions = array_map(function($r) { return new Assertion($this->connection, $r, $this->id, $this->issuer_id); }, $response);
		return $assertions;
	}
}