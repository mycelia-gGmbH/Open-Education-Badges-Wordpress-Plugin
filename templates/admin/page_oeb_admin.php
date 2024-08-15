<?php
	$url_page = '?page='. $oeb_page;
	$url_create =  add_query_arg([
			'create' => '',
			'page'=> $oeb_page
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
			<h2>Verfügbare Badges</h2>
			<?php foreach($oeb_badges as $badge): ?>
				<img src="<?= $badge['image'] ?>" width="96">
			<?php endforeach; ?>
		<?php else: ?>
			<p>Verbindung hergestellt, aber bisher keine Badges angelegt</p>
		<?php endif; ?>
	<?php else: ?>
		<p>Bisher keine Verbindungen eingerichtet.<p>
		<p><a href="<?= $url_create ?>">Verbindung anlegen</a><p>
	<?php endif; ?>
</div>