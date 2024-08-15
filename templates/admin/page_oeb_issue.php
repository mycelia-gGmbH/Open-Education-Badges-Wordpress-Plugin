<?php
	$url_page = '?page='. $oeb_page;
?>

<h1>Badge vergeben</h1>

<?php if (empty($oeb_badges)): ?>
	<p>Keine Badges verfügbar</p>
<?php else: ?>
	<p><strong>Bitte einen Badge wählen:</strong></p>

	<?php foreach($oeb_badges as $badge): ?>
		<a href="<?= add_query_arg([
			'badge' => $badge['slug'],
			'page'=> $oeb_page
		],
		admin_url('admin.php')
	) ?>" style="display: inline-block;"><img src="<?= $badge['image'] ?>" width="96">
		<p><?= $badge['name'] ?></p>
	</a>
	<?php endforeach; ?>
<?php endif; ?>