<?php

use DisruptiveElements\OpenEducationBadges\Util\Utils;

	$url_page = '?page='. $oeb_page;
	$url_create_connection =  add_query_arg([
			'create' => '',
			'page'=> 'oeb_connections'
		],
		admin_url('admin.php')
	);
?>

<div class="wrap oeb-wrap">
	<h1 class="wp-heading-inline">
		Open Education Badges
	</h1>

	<?php if (!empty($oeb_connections)): ?>

		<?php if (!empty($oeb_badges)): ?>

			<?php if (empty($oeb_badge_entity_id)): ?>

				<h2>Verfügbare Badges</h2>
				<div class="oeb-badgeslist">
				<?php foreach($oeb_badges as $badge): ?>
					<?php
						$url_details =  add_query_arg([
							'badge' => $badge->id,
							'page'=> 'oeb_admin'
						],
						admin_url('admin.php')
					);
					?>
					<a href="<?= $url_details ?>">
						<div class="card">
							<h3 class="title"><?= $badge->name ?></h3>
							<figure>
								<img src="<?= $badge->image ?>" width="96">
								<figcaption><?= wp_trim_words($badge->description, 20) ?></figcaption>
							</figure>
						</div>
					</a>
				<?php endforeach; ?>
				</div>

			<?php else:


				// $badges = array_filter($oeb_badges, function($b) use($oeb_badge_entity_id) {
				// 	return $b->id == $oeb_badge_entity_id;
				// });
				// $badge = reset($badges);
				$badge = $oeb_badge_object;

				if (!empty($badge)):

					$url_create_assertion = add_query_arg([
						'badge' => $badge->id,
						'page'=> 'oeb_issue'
					],
					admin_url('admin.php')
				);

					$issuer = Utils::array_find($oeb_issuers, function($issuer) use ($badge) { return $issuer->id == $badge->issuer_id; });

					?>
				<br><br>
				<a class="button" href="<?= $url_page ?>">Zurück zur Übersicht</a>
				<a class="button" href="<?= $url_create_assertion ?>">Badge vergeben</a>
				<br>
				<div class="oeb-badgedetails postbox">
					<div class="oeb-badgedetails__left">
						<figure><img src="<?= $badge->image ?>" width="256"></figure>
						<div class="oeb-badgedetails__issuer">
							<?php if (!empty($issuer->image)): ?>
								<img src="<?= $issuer->image ?>" title="<?= $issuer->name ?>" alt="<?= $issuer->name ?>">
							<?php endif; ?>
							<p>
								Vergeben von:<br>
								<strong><?= $issuer->name ?></strong>
							</p>
						</div>

						<?php if (!empty($badge->api_data['tags'])): ?>
							<h3>Tags</h3>
							<ul class="oeb-tags">
								<?php foreach($badge->api_data['tags'] as $tag): ?>
									<li><?= $tag ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<p><strong>Kategorie:</strong> <?= $badge->category ?></p>
						<p><strong>Dauer:</strong> <?= $badge->duration ?> min</p>
						<strong>Erstellt:</strong> <?= $badge->created->format('d.m.Y') ?>

					</div>
					<div class="oeb-badgedetails__right">
						<h1><?= $badge->name ?></h1>
						<h3>Kurzbeschreibung</h3>
						<p><?= nl2br($badge->description) ?></p>
						<h3>Kriterien</h3>
						<p><?= nl2br($badge->api_data['criteriaNarrative']) ?></p>
						<?php if (!empty($badge->api_data['criteriaUrl'])): ?>
							<p>
								<strong>Url:</strong>
								<a target="_blank" href="<?= $badge->api_data['criteriaUrl'] ?>"><?= $badge->api_data['criteriaUrl'] ?></a>
							</p>
						<?php endif; ?>
						<?php if (!empty($badge->api_data['alignment'])): ?>
							<h3>Alignment</h3>
							<?php foreach($badge->api_data['alignment'] as $alignment): ?>
								<pre><?= var_export($alignment, true) ?></pre>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if (!empty($badge->competencies)): ?>
							<h3>Kompetenzen</h3>
							<table class="widefat oeb-badgedetails__competencies">
							<?php foreach($badge->competencies as $competency): ?>
								<tr>
									<td>
										<strong><?= $competency['name'] ?></strong>
										<br><?= $competency['category'] ?>
										<br>Dauer: <?= $competency['studyLoad'] ?> min
									</td>
									<td>
										<?= $competency['description'] ?>
									</td>
								</tr>
							<?php endforeach; ?>
							</table>
						<?php endif; ?>

						<h3>QR-Code Vergaben</h3>
						<?php if (!empty($oeb_badge_qrcodes)): ?>
							<table class="widefat">
								<thead>
									<tr>
										<th>Name</th>
										<th>Ersteller:in</th>
										<th>Gültigkeit</th>
										<th style="width: 165px;">Aktionen</th>
									<tr>
								</thead>
							<?php foreach($oeb_badge_qrcodes as $qrcode): ?>
								<tr>
									<td><?= $qrcode->title ?></td>
									<td><?= $qrcode->createdBy ?></td>
									<td>
										<?php if (!empty($qrcode->valid_from)): ?>
										Ab <?= $qrcode->valid_from->format('d.m.Y') ?><br>
										<?php endif; ?>
										<?php if (!empty($qrcode->expires_at)): ?>
										Bis <?= $qrcode->expires_at->format('d.m.Y') ?>
										<?php endif; ?>
									</td>
									<td style="white-space: nowrap;">
										<?php
										$url_edit_qr =  add_query_arg([
												'badge' => $badge->id,
												'action' => 'qr',
												'page'=> 'oeb_admin',
												'qr' => $qrcode->id
											],
											admin_url('admin.php')
										);
										?>
										<a class="button button-xs button-secondary" href="<?= $url_edit_qr ?>">Bearbeiten</a>
										<?php
										$url_show_qr =  add_query_arg([
												'badge' => $badge->id,
												'action' => 'qr-show',
												'page'=> 'oeb_admin',
												'qr' => $qrcode->id
											],
											admin_url('admin.php')
										);
										?>
										<a class="button button-xs button-secondary" href="<?= $url_show_qr ?>">Anzeigen</a>
									</td>
								</tr>
							<?php endforeach; ?>
							</table><br>
						<?php endif; ?>

						<?php 
							$url_create_qr =  add_query_arg([
									'badge' => $badge->id,
									'action' => 'qr',
									'page'=> 'oeb_admin'
								],
								admin_url('admin.php')
							);
						?>
						<a href="<?= $url_create_qr ?>" class="button">QR Code erstellen</a>

						<?php if (!empty($oeb_badge_assertions)): ?>
							<br><h3><?= count($oeb_badge_assertions) ?> Empfänger:innen</h3>
							<table class="widefat">
								<thead>
									<tr>
										<th>ID</th>
										<th>Vergeben am</th>
										<th>Aktionen</th>
									<tr>
								</thead>
								<tbody>
							<?php foreach($oeb_badge_assertions as $i => $assertion): ?>
								<tr<?= ($i % 2 != 0) ? ' class="alternate"' : '' ?>>
									<td><?= $assertion->recipient ?></td>
									<td><?= $assertion->created->format('d.m.Y') ?></td>
									<td>
										<form action="" method="post" onsubmit="return confirm('Möchten Sie diesen Badge wirklich zurücknehmen?')">
											<?php wp_nonce_field('oeb-delete-assertion-'.$assertion->id); ?>
											<input type="hidden" name="assertion_id" value="<?= $assertion->id ?>">
											<input type="hidden" name="action" value="assertions">
											<button class="button button-xs button-secondary" type="submit" name="delete">Zurücknehmen</button>
										</form>
									</td>
								<tr>
							<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
						<?php if (0): ?>
							<pre><?= var_export($badge, true) ?></pre>
						<?php endif; ?>
					</ul>
				</div>
				<?php endif; ?>
			<?php endif; ?>

		<?php else: ?>

			<p>Verbindung hergestellt, aber bisher keine Badges angelegt</p>

		<?php endif; ?>

	<?php else: ?>
		<p><strong>Bisher keine Verbindungen eingerichtet.</strong><p>
		<p><a href="<?= $url_create_connection ?>">Verbindung anlegen</a><p>
	<?php endif; ?>
</div>