<?php
	$url_page = '?page='. $oeb_page;
	$url_create =  add_query_arg([
			'create' => '',
			'page'=> $oeb_page
		],
		admin_url('admin.php')
	);
?>

<div class="wrap oeb-wrap">
	<h1 class="wp-heading-inline">
		Verbindungen <!-- <span class="title-count theme-count">2</span>-->
	</h1>

	<a
		href="<?= $url_create ?>"
		style="margin-top: 8px;"
		class="hide-if-no-js page-title-action button">Verbindung hinzufügen</a>
		<br>
		<br>
	<hr class="wp-header-end" />

	<?php if (!empty($oeb_connections)): ?>

	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th scope="col" id="name" class="manage-column column-name column-primary">Bezeichnung</th>
				<th scope="col" id="description" class="manage-column column-description">Daten</th>
			</tr>
		</thead>

		<tbody>
			<?php foreach($oeb_connections as $connection): ?>
			<tr class="active">
				<td class="plugin-title column-primary"><strong><?= $connection['name'] ?></strong>
					<div class="row-actions visible">
						<span class="deactivate">
							<a href="<?= $url_page ?>&oeb_connection=<?=$connection['id'] ?>">Bearbeiten</a>
							<?php /* <a href="<?= $url_page ?>&oeb_connection=1&disable">Deaktivieren</a>*/ ?>
						</span>
					</div>
				</td>
				<td class="column-description desc">
					<div class="">
						Client ID: <?= $connection['client_id'] ?><br>
						Client Secret: <?= $connection['client_secret'] ?>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>

		</tbody>

	</table>

	<?php else: ?>
		<table class="wp-list-table widefat">
			<tr>
				<td>
					<h2>keine bestehenden Verbindungen</h2>
				</td>
			</tr>
		</table>
	<?php endif; ?>

</div>