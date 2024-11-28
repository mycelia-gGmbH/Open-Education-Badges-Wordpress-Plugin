<?php
	$url_page = '?page='. $oeb_page;
?>
<div class="wrap oeb-wrap">
	
	<h1>Badge vergeben</h1>
	
	<div class="oeb-issue-badge">
		<?php if (empty($oeb_badges)): ?>
			<p>Keine Badges verfügbar</p>
		<?php else: ?>
			<p class="oeb-issue-badge__cta"><strong>Bitte einen Badge wählen:</strong></p>
	
			<div class="oeb-badgeslist">
				<?php foreach($oeb_badges as $badge): ?>
					<a href="<?= add_query_arg([
						'badge' => $badge->id,
						'page'=> $oeb_page
					],
					admin_url('admin.php')
				) ?>"  class="oeb-badgelist__item">
	
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
		<?php endif; ?>
	</div>
</div>
