<?php

namespace DisruptiveElements\OpenEducationBadges\Entity;

class Assertion extends ApiObject {
	public string $badge_id;
	public string $issuer_id;
	public \DateTime $created;

	public string $recipient;

	public function __construct(string $connection, array $api_data, string $badge_id, string $issuer_id) {
		$this->connection = $connection;
		$this->api_data = $api_data;
		$this->id = $api_data['entityId'];
		$this->badge_id = $badge_id;
		$this->issuer_id = $issuer_id;
		$this->recipient = $api_data['recipient']['plaintextIdentity'];

		$this->created = new \DateTime($this->api_data['createdAt']);

	}
}