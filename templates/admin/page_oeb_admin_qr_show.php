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

		<?php if (!empty($oeb_badge_entity_id)): 
			$badge = $oeb_badge_object;
			?>

			<br><br>
			<div class="postbox" style="padding: 0 12px 12px;">

				<h2>QR-Code für</h2>

				<img width="100" src="<?= $badge->image ?>">
				<p><?= $badge->name ?></p>

				<img src="<?= $qrcode_svg ?>" width="600">

				<br><a class="button" download="<?= sanitize_title($badge->name) ?>_qr.svg" href="<?= $qrcode_svg ?>">Download SVG</a>
				<a class="button" download="<?= sanitize_title($badge->name) ?>_qr.png" href="<?= $qrcode_png ?>">Download PNG</a>

			</div>

			
		<?php endif; ?>
	<?php endif; ?>
</div>