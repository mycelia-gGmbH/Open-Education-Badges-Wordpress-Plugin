<?php
	$url_page = '?page='. $oeb_page;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		Badge vergeben
	</h1>

	<div class="oeb-issue-badge">
		<div><a href="<?= $url_page ?>">Zurück zur Badge-Auswahl</a></div>

		<div class="oeb-issue-badge__chosen">
			<div class="oeb-badgelist__image">
				<img src="<?= $oeb_badge->image ?>" width="120" title="<?= $badge->name ?>" alt="<?= $badge->name ?>">
			</div>

			<div class="oeb-badgelist__title">
				<p><?= $oeb_badge->name ?></p>
			</div>
		</div>

		<form
			method="post"
			name="issue"
			id="oeb_issue_badge"
			class="validate"
			novalidate="novalidate"
			>

			<div class="oeb-issue-badge__users">
				<label for="oeb_users">Wordpress-Benutzer</label>
				<select name="oeb_users[]" multiple id="oeb_users">
					<?php foreach($users as $user): ?>
						<?php
							$username = $user->user_email;
							if (!empty($user->user_firstname) || !empty($user->user_lastname)) {
								$username = $user->user_firstname . ' ' . $user->user_lastname . ' (' . $user->user_email . ')';
							}
						?>
						<option value="<?= $user->ID ?>"><?= $username ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="oeb-issue-badge__emails">
				<label for="oeb_emails">E-Mail Freitexteingabe (Trennzeichen: ,; Leerzeichen)</label>
				<textarea name="oeb_emails" id="oeb_emails"></textarea>
			</div>

			<p><input type="submit" name="save" value="Badge vergeben"></p>
		</form>
	</div>
</div>