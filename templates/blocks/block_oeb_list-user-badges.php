<?php
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

$user = wp_get_current_user();
if (!empty($user)):
	$badges = Utils::list_badges_by_email($user->user_email);
	if (!empty($badges)):
?>

<div class="oeb-badgelist">
	<?php foreach($badges as $badge): ?>
		<div class="oeb-badgelist__item">
			<div class="oeb-badgelist__image">
				<img src="<?= $badge['image'] ?>" width="120" title="<?= $badge['name'] ?>"  alt="<?= $badge['name'] ?>">
			</div>

			<div class="oeb-badgelist__title">
				<p><?= $badge['name'] ?></p>
			</div>
		</div>
	<?php endforeach; ?>
</div>

	<?php endif; ?>
<?php endif; ?>