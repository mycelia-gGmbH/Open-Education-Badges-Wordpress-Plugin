<?php

namespace DisruptiveElements\OpenEducationBadges\Entity;

class Issuer extends ApiObject {

	public string $name;
	public string $image;

	public function __construct(string $connection, array $api_data) {
		$this->connection = $connection;
		$this->api_data = $api_data;
		$this->id = $api_data['entityId'];
		$this->name = $api_data['name'];
		$this->image = $api_data['image'];
	}

}