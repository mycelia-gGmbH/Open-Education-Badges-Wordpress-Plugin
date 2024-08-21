<?php
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

if (!current_user_can('oeb_issue') && !current_user_can('manage_options')) {
	return;
}

$oeb_badges = Utils::get_all_badges();
$oeb_badge_slug = $_GET['oeb_badge'] ?? '';
$oeb_badge = array_filter($oeb_badges, function($badge) use($oeb_badge_slug) {
	return $badge['slug'] == $oeb_badge_slug;
});
$oeb_badge = reset($oeb_badge);

?>

<div class="oeb-issue-badge">
	<h3>Badge vergeben</h3>

	<?php if (!isset($_GET['oeb_issue']) || empty($oeb_badge_slug)): ?>

		<?php if (empty($oeb_badges)): ?>
			<p>Keine Badges verfügbar</p>
		<?php else: ?>
			<p><strong>Bitte einen Badge wählen:</strong></p>

			<?php foreach($oeb_badges as $badge): ?>
				<a href="?oeb_issue&oeb_badge=<?= $badge['slug'] ?>" style="display: inline-block;"><img src="<?= $badge['image'] ?>" width="96">
				<p><?= $badge['name'] ?></p>
			</a>
			<?php endforeach; ?>
		<?php endif; ?>

	<?php elseif (empty($_POST['oeb_users']) && empty($_POST['oeb_emails'])): ?>

		<?php
			$users = get_users();
		?>

		<img src="<?= $oeb_badge['image'] ?>" width="120">
		<p><?= $oeb_badge['name'] ?></p>

		<form
			method="post"
			name="issue"
			id="oeb_issue_badge"
			class="validate"
			novalidate="novalidate"
			>

			<?php wp_nonce_field('oeb-issue-badge-'.$oeb_badge_slug); ?>

			<div>
				<label for="oeb_users">Wordpress-Benutzer</label><br>
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

			<div>
				<label for="oeb_emails">E-Mail Freitexteingabe (Trennzeichen: ,; Leerzeichen)</label><br>
				<textarea name="oeb_emails" id="oeb_emails"></textarea>
			</div>

			<p><input type="submit" name="save" value="Badge vergeben"></p>
		</form>

	<?php else: ?>

		<?php
			if (wp_verify_nonce($_REQUEST['_wpnonce'], 'oeb-issue-badge-'.$oeb_badge_slug)) {

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
							// TODO e-mail validation?
							return !empty($email);
						}
					));
				}

				Utils::issue_by_badge($_GET['badge'], $emails);

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