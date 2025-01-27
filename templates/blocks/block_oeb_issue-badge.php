<?php
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

if (!current_user_can('oeb_issue') && !current_user_can('manage_options')) {
	return;
}

$oeb_badges = Utils::get_all_badges();
$oeb_badge_entity_id = $_GET['oeb_badge'] ?? '';
$oeb_badge = array_filter($oeb_badges, function($badge) use($oeb_badge_entity_id) {
	return $badge->id == $oeb_badge_entity_id;
});
$oeb_badge = reset($oeb_badge);

?>

<div class="oeb-issue-badge">
	<?php if (!isset($_GET['oeb_issue']) || empty($oeb_badge_entity_id)): ?>

		<?php if (empty($oeb_badges)): ?>
			<p>Keine Badges verfügbar</p>
		<?php else: ?>
			<p class="oeb-issue-badge__cta"><strong>Bitte einen Badge wählen:</strong></p>

			<div class="oeb-badgelist">
				<?php foreach($oeb_badges as $badge): ?>
					<a href="?oeb_issue&oeb_badge=<?= $badge->id ?>" class="oeb-badgelist__item">
						<div class="oeb-badgelist__image">
							<img src="<?= $badge->image ?>" width="96" title="<?= $badge->name ?>"  alt="<?= $badge->name ?>">
						</div>

						<div class="oeb-badgelist__title">
							<p><?= $badge->name ?></p>
						</div>
					</a>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

	<?php elseif (empty($_POST['oeb_users']) && empty($_POST['oeb_emails'])): ?>

		<div><a href="./">Zurück zur Badge-Auswahl</a></div>

		<div class="oeb-issue-badge__chosen">
			<?php
				$users = get_users();
			?>

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

			<?php wp_nonce_field('oeb-issue-badge-'.$oeb_badge_entity_id); ?>

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

	<?php else: ?>

		<?php
			if (wp_verify_nonce($_REQUEST['_wpnonce'], 'oeb-issue-badge-'.$oeb_badge_entity_id)) {

				$emails = [];
				if (!empty($_POST['oeb_users'])) {
					$users = get_users([
						'include' => $_POST['oeb_users']
					]);
					$emails = array_merge($emails, array_map(function($user) { return $user->user_email; }, $users));
				}
				if (!empty($_POST['oeb_emails'])) {
					$emails = array_merge($emails, array_filter(
						preg_split("/[,;\s]/",$_POST['oeb_emails']),
						function($email) {
							return filter_var($email, FILTER_VALIDATE_EMAIL);
						}
					));
				}

				Utils::issue_by_badge($_GET['oeb_badge'], $emails);

				?>
				<p>Badge zugewiesen</p>
				<?php
			} else {
				?>
				<p>Ein Fehler ist aufgetreten</p>
				<?php
			}
		?>

	<?php endif; ?>
</div>