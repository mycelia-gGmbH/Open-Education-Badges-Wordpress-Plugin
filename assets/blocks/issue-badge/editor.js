(function() {
	const wp = 	window.wp;
	const blocks = window.wp.blocks;
	const registerBlockType = blocks.registerBlockType;

	const html = window.htm.bind(wp.element.createElement);

	registerBlockType('oeb/issue-badge', {
		apiVersion: 2,
		title: 'OpenEducationBadges: Badge vergeben',
		category: 'OpenEducationBadges',

		edit: function(props) {
			return html`<div style="${{'border': '1px solid black', 'padding': '12px 24px'}}">Open Education Badges Issue Block</div>`;
		},
	} );
})();
