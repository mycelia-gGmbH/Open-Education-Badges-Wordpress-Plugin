<?php

use DisruptiveElements\OpenEducationBadges\Util\Utils;

	$url_page = '?page='. $oeb_page;
	$url_create =  add_query_arg([
			'create' => '',
			'page'=> 'oeb_connections'
		],
		admin_url('admin.php')
	);
?>

<div class="wrap">
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
							'badge' => $badge['entityId'],
							'page'=> 'oeb_admin'
						],
						admin_url('admin.php')
					);
					?>
					<a href="<?= $url_details ?>">
						<div class="card">
							<h3 class="title"><?= $badge['name'] ?></h3>
							<figure>
								<img src="<?= $badge['image'] ?>" width="96">
								<figcaption><?= wp_trim_words($badge['description'], 20) ?></figcaption>
							</figure>
						</div>
					</a>
				<?php endforeach; ?>
				</div>

			<?php else:

				$badges = array_filter($oeb_badges, function($b) use($oeb_badge_entity_id) {
					return $b['entityId'] == $oeb_badge_entity_id;
				});
				$badge = reset($badges);
				if (!empty($badge)):

					$issuer = Utils::array_find($oeb_issuers, function($issuer) use ($badge) { return $issuer->id == $badge['issuer']; });

					?>
				<br><br><a class="button" href="<?= $url_page ?>">Zurück zur Übersicht</a><br>
				<div class="oeb-badgedetails postbox">
					<div class="oeb-badgedetails__left">
						<figure><img src="<?= $badge['image'] ?>" width="256"></figure>
						<div class="oeb-badgedetails__issuer">
							<?php if (!empty($issuer->image)): ?>
								<img src="<?= $issuer->image ?>" title="<?= $issuer->name ?>" alt="<?= $issuer->name ?>">
							<?php endif; ?>
							<p>
								Vergeben von:<br>
								<strong><?= $issuer->name ?></strong>
							</p>
						</div>

						<?php if (!empty($badge['tags'])): ?>
							<h3>Tags</h3>
							<ul class="oeb-tags">
								<?php foreach($badge['tags'] as $tag): ?>
									<li><?= $tag ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<p><strong>Kategorie:</strong> <?= $oeb_badge_object->category ?></p>
						<p><strong>Dauer:</strong> <?= $oeb_badge_object->duration ?> min</p>
						<strong>Erstellt:</strong> <?= $oeb_badge_object->created->format('d.m.Y') ?>

					</div>
					<div class="oeb-badgedetails__right">
						<h1><?= $badge['name'] ?></h1>
						<h3>Kurzbeschreibung</h3>
						<p><?= nl2br($badge['description']) ?></p>
						<h3>Kriterien</h3>
						<p><?= nl2br($badge['criteriaNarrative']) ?></p>
						<p><strong>Url:</strong> <a target="_blank" href="<?= $badge['criteriaUrl'] ?>"><?= $badge['criteriaUrl'] ?></a></p>
						<?php if (!empty($badge['alignment'])): ?>
							<h3>Alignment</h3>
							<?php foreach($badge['alignment'] as $alignment): ?>
								<pre><?= var_export($alignment, true) ?></pre>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if (!empty($oeb_badge_assertions)): ?>
							<h3>Assertions</h3>
							<table class="widefat">
								<thead>
									<tr>
										<th>ID</th>
										<th>Vergeben am</th>
									<tr>
								</thead>
								<tbody>
							<?php foreach($oeb_badge_assertions as $i => $assertion): ?>
								<tr<?= ($i % 2 != 0) ? ' class="alternate"' : '' ?>>
									<td><?= $assertion->recipient ?></td>
									<td><?= $assertion->created->format('d.m.Y') ?></td>
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
		<p><a href="<?= $url_create ?>">Verbindung anlegen</a><p>
	<?php endif; ?>
</div>