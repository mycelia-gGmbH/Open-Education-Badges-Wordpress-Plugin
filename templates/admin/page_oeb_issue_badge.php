<?php
	$url_page = '?page='. $oeb_page;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		Badge vergeben
	</h1>
	<br><br>
	<div><a class="button" href="<?= $url_page ?>">Zurück zur Badge-Auswahl</a></div>

	<div class="oeb-issue-badge postbox" style="padding: 0 12px 12px;">

		<?php if (!empty($_POST)): ?>

			<h2>Badge erfolgreich vergeben.</h2>
			<p><a class="button" href="<?= add_query_arg([
					'page'=> $_GET['page'],
					'badge' => $_GET['badge'],
				],
				admin_url('admin.php')
			); ?>">Weitere vergeben</a></p>

		<?php else: ?>

		<form
			method="post"
			name="issue"
			id="oeb_issue_badge"
			class="validate"
			novalidate="novalidate"
			>

			<table class="form-table">

					<tr>
						<th>Gewählter Badge</th>
						<td>
							<img src="<?= $oeb_badge->image ?>" width="120" title="<?= $oeb_badge->name ?>" alt="<?= $oeb_badge->name ?>">
							<p><?= $oeb_badge->name ?></p>
						</td>
					</tr>

					<tr class="oeb-issue-badge__users">
						<th><label for="oeb_users">Registrierte Benutzer</label></th>
						<td>
							<select name="oeb_users[]" multiple id="oeb_users">
								<?php foreach($users as $user): ?>
									<?php
										$state = in_array($user->user_email, $oeb_badge_recipients) ? 'disabled':'';

										$username = $user->user_email;
										if (!empty($user->user_firstname) || !empty($user->user_lastname)) {
											$username = $user->user_firstname . ' ' . $user->user_lastname . ' (' . $user->user_email . ')';
										}
										if ($state == 'disabled') {
											$username .= ' - bereits erhalten';
										}
									?>
									<option value="<?= $user->ID ?>"><?= $username ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr class="oeb-issue-badge__emails">
						<th><label for="oeb_emails">E-Mail Freitexteingabe</label></th>
						<td>
							<textarea name="oeb_emails" id="oeb_emails"></textarea>
							<br>(Trennzeichen: ,; Leerzeichen)
						</td>
					</tr>
			</table>

			<p><input class="button" type="submit" name="save" value="Badge vergeben"></p>
		</form>

		<?php endif; ?>
	</div>
</div>