import Choices from "choices.js";

document.addEventListener("DOMContentLoaded", () => {
	const oeb_users = document.getElementById('oeb_users');
	if (oeb_users) {
		new Choices(oeb_users, {
			removeItemButton: true,
		});
	}
});
