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

	<?php elseif (!isset($_POST['oeb_users'])): ?>

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

			<select name="oeb_users[]" multiple>
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

			<p><input type="submit" name="save" value="Badge vergeben"></p>
		</form>

	<?php else: ?>

		<?php
			if (wp_verify_nonce($_REQUEST['_wpnonce'], 'oeb-issue-badge-'.$oeb_badge_slug)) {
				$users = get_users([
					'include' => $_POST['oeb_users']
				]);

				$result = Utils::issue_by_badge($oeb_badge_slug, $users);
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