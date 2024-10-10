<?php
	$url_page = '?page='. $oeb_page;
	$loglevel_selected = $oeb_settings['loglevel'];
	if (empty($loglevel_selected)) {
		$loglevel_selected = 'error';
	}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		Einstellungen
	</h1>

	<form
		method="post"
		name="settings"
		id="oeb_form_settings"
		class="validate"
		novalidate="novalidate"
		>

		<table class="form-table">
			<tr>
				<th>
					<label for="form-oeb-loglevel">Log-Level</label>
				</th>
				<td>
					<select id="form-oeb-loglevel" name="loglevel">
						<option <?= $loglevel_selected == 'error' ? 'selected' : '' ?> value="error">Fehler</option>
						<option <?= $loglevel_selected == 'info' ? 'selected' : '' ?> value="info">Info</option>
						<option <?= $loglevel_selected == 'debug' ? 'selected' : '' ?> value="debug">Debug</option>
					</select>
					<p class="description">
						Aufzeichnungen liegen im Upload-Verzeichnis im Ordner "OpenEducationBadges"
					</p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Änderungen speichern">
		</p>
	</form>

</div>