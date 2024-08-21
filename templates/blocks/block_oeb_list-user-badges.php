<?php
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

$user = wp_get_current_user();
if (!empty($user)):
	$badges = Utils::list_badges_by_email($user->user_email);
	if (!empty($badges)):
?>

<div>
		<h3>Erhaltene Badges</h3>
		<?php foreach($badges as $badge): ?>
			<div>
				<img src="<?= $badge['image'] ?>" width="120">
				<p><?= $badge['name'] ?></p>
			</div>
		<?php endforeach; ?>
</div>

	<?php endif; ?>
<?php endif; ?>