<?php

namespace DisruptiveElements\OpenEducationBadges\Entity;

use DisruptiveElements\OpenEducationBadges\Util\CachedApiWrapper;
use DisruptiveElements\OpenEducationBadges\Util\Utils;

class QrCode extends ApiObject {
	public string $issuer_id;
	public string $badge_id;

	public string $title;
	public string $createdBy;
	public ?\DateTime $valid_from;
	public ?\DateTime $expires_at;
	public string $url;

	public function __construct(string $connection, array $api_data, string $issuer_id, string $badge_id) {
		$this->connection = $connection;
		$this->api_data = $api_data;
		$this->id = $api_data['slug'];
		$this->issuer_id = $issuer_id;
		$this->badge_id = $badge_id;

		$this->title = $api_data['title'];
		$this->createdBy = $api_data['createdBy'];
		$this->valid_from = $this->parse_qr_date($api_data['valid_from']);
		$this->expires_at = $this->parse_qr_date($api_data['expires_at']);
		$this->url = "https://openbadges.education/public/issuer/issuers/$issuer_id/badges/$badge_id/request/".$this->id;
	}

	private function parse_qr_date(?string $qrdate): ?\DateTime {
		if (!empty($qrdate)) {
			$dt_qrdate = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $qrdate);
			if (!empty($dt_qrdate)) {
				return $dt_qrdate;
			}
		}
		return null;
	}
}