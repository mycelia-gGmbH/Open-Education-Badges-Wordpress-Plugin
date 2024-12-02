<?php
Use DisruptiveElements\OpenEducationBadges\Util\Utils;

if (!empty($block->attributes['display'])) {
	$display_fields = explode('-', $block->attributes['display']);
} else {
	$display_fields = ["image", "title"];
}
$layout = $block->attributes['layout'];
if (empty($layout)) {
	$layout = '25';
}

$user = wp_get_current_user();
if (!empty($user)):
	$badges = Utils::list_badges_by_email($user->user_email);
	if (!empty($badges)):
?>

<div class="oeb-badgelist oeb-badgelist--layout<?= $layout ?><?= in_array('shortdesc', $display_fields) ? ' oeb-badgelist--layout-card' : '' ?>">
	<?php if (!empty($block->attributes['headline'])): ?>
		<p><?= $block->attributes['headline'] ?></p>
	<?php endif; ?>
	<div class="oeb-badgelist__wrap">
	<?php foreach($badges as $badge): ?>
		<div class="oeb-badgelist__item">
			<a target="_blank" href="<?= $badge->api_data['openBadgeId'] ?>">
				<?php if (in_array('image', $display_fields)): ?>
				<div class="oeb-badgelist__image">
					<img src="<?= $badge->image ?>" width="120" title="<?= $badge->name ?>"  alt="<?= $badge->name ?>">
				</div>
				<?php endif; ?>
				<div class="oeb-badgelist__content">
					<?php if (in_array('title', $display_fields)): ?>
						<div class="oeb-badgelist__title">
							<?= $badge->name ?>
						</div>
					<?php endif; ?>
					<?php if (in_array('shortdesc', $display_fields)): ?>
						<div class="oeb-badgelist__desc">
							<?= wp_trim_words($badge->description, 20) ?>
						</div>
					<?php endif; ?>
				</div>
			</a>
		</div>
	<?php endforeach; ?>
	</div>
</div>

	<?php endif; ?>
<?php endif; ?>
