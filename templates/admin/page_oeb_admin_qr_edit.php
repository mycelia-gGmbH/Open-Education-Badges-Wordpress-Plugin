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

<div class="wrap">
	<h1 class="wp-heading-inline">
		Open Education Badges
	</h1>

	<?php if (!empty($oeb_connections)): ?>

		<?php if (!empty($oeb_badge_entity_id)): 
			$badge = $oeb_badge_object;
			?>

			<br><br>
			<div class="postbox" style="padding: 0 12px 12px;">

				<h2>QR-Code-Vergabe erstellen für</h2>

				<img width="100" src="<?= $badge->image ?>">
				<p><?= $badge->name ?></p>

				<form
					method="post"
					name="qr"
					id="oeb_qr"
					class="validate"
				>
					<table class="form-table">
					<?php wp_nonce_field('oeb-create-qr-'.$badge->id); ?>
						<tr><th><label for="oeb_qr_title">Titel</label></th><td><input required name="oeb_qr_title" value="<?= $oeb_qr_title ?>"></td></tr>
						<tr><th><label for="oeb_qr_createdBy">Name Ersteller:in</label></th><td><input required name="oeb_qr_createdBy" value="<?= $oeb_qr_createdBy ?>"></td></tr>
						<tr><th><label for="oeb_qr_valid_from">Start-Datum</label></th><td><input name="oeb_qr_valid_from" type="date" value="<?= $oeb_qr_valid_from ?>"></td></tr>
						<tr><th><label for="oeb_qr_expires_at">End-Datum</label></th><td><input name="oeb_qr_expires_at" type="date" value="<?= $oeb_qr_expires_at ?>"></td></tr>
					</table>
					<input type="submit" class="button" name="save" value="Speichern">
				</form>

			</div>

			
		<?php endif; ?>
	<?php endif; ?>
</div>