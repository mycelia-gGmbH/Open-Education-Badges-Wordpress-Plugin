<?php
	$url_page = '?page='. $oeb_page;
?>

<h1>Badge vergeben</h1>

<div class="oeb-issue-badge">
	<?php if (empty($oeb_badges)): ?>
		<p>Keine Badges verfügbar</p>
	<?php else: ?>
		<p class="oeb-issue-badge__cta"><strong>Bitte einen Badge wählen:</strong></p>

		<div class="oeb-badgelist">
			<?php foreach($oeb_badges as $badge): ?>
				<a href="<?= add_query_arg([
					'badge' => $badge['slug'],
					'page'=> $oeb_page
				],
				admin_url('admin.php')
			) ?>"  class="oeb-badgelist__item">

				<div class="oeb-badgelist__image">
					<img src="<?= $badge['image'] ?>" width="96" title="<?= $badge['name'] ?>"  alt="<?= $badge['name'] ?>">
				</div>

				<div class="oeb-badgelist__title">
					<p><?= $badge['name'] ?></p>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
