<div class="wrap">
	<h1 class="wp-heading-inline">
		Verbindung anlegen
	</h1>

	<form
		method="post"
		name="create"
		id="oeb_connections_create"
		class="validate"
		novalidate="novalidate"
		>
		<!-- <input name="action" type="hidden" value="create" /> -->
		<input
			type="hidden"
			id="_wpnonce_create-user"
			name="_wpnonce_create-user"
			value="3c077caec4"
		/>

		<table class="form-table" role="presentation">
			<tr class="form-field form-required">
				<th scope="row">
					<label for="oeb_connection_name">Bezeichnung</label>
				</th>
				<td>
					<input
						name="oeb_connection_name"
						type="text"
						value="<?= $oeb_connection_name ?>"
						aria-required="true"
						autocorrect="off"
						autocomplete="off"
						required
					/>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="oeb_connection_clientid">Client ID</label>
				</th>
				<td>
					<input
						name="oeb_connection_clientid"
						type="text"
						value="<?= $oeb_connection_clientid ?>"
						aria-required="true"
						autocorrect="off"
						autocomplete="off"
						required
					/>
				</td>
			</tr>
			<tr class="form-field form-required">
				<th scope="row">
					<label for="oeb_connection_clientsecret">Client Secret</label>
				</th>
				<td>
					<input
						name="oeb_connection_clientsecret"
						type="text"
						value="<?= $oeb_connection_clientsecret ?>"
						aria-required="true"
						autocorrect="off"
						autocomplete="off"
						required
					/>
				</td>
			</tr>
		</table>

		<?php if (!empty($oeb_issuers)): ?>
			<h2>Aktive Institutionen</h2>
			<table class="wp-list-table widefat" style="max-width: 500px;">
				<tr>
					<th style="width:32px;">Aktiv</th>
					<th>Institution</th>
				</tr>
				<?php foreach($oeb_issuers as $idx => $issuer): ?>
				<tr>
					<td>
						<input 
							type="checkbox"
							id="oeb_connection_issuers_<?= $idx ?>"
							name="oeb_connection_issuers[]"
							value="<?= $issuer['entityId'] ?>"
							<?= in_array($issuer['entityId'], $oeb_connection_issuers) ? 'checked' : '' ?>
						>
					</td>
					<td><label for="oeb_connection_issuers_<?= $idx ?>"><?= $issuer['name'] . ' (' . $issuer['entityId'] . ')'  ?></label></td>
				</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>

		<p class="submit">
			<button type="submit" name="save"class="button button-primary" value="Speichern">Speichern</button>
			<button type="submit" name="delete" class="button button-secondary" value="Verbindung Löschen">Verbindung Löschen</button>
		</p>


	</form>
</div>