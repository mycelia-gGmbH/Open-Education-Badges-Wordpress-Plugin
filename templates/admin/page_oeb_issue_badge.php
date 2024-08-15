<?php
	$url_page = '?page='. $oeb_page;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		Badge vergeben
	</h1>

	<img src="<?= $oeb_badge['image'] ?>" width="120">
	<p><?= $oeb_badge['name'] ?></p>

	<form
		method="post"
		name="issue"
		id="oeb_issue_badge"
		class="validate"
		novalidate="novalidate"
		>

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
</div>