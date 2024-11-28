(function() {
	const wp = 	window.wp;
	const blocks = window.wp.blocks;
	const registerBlockType = blocks.registerBlockType;

	const html = window.htm.bind(wp.element.createElement);

	registerBlockType('oeb/list-user-badges', {
		apiVersion: 2,
		title: 'OpenEducationBadges: Benutzer Badges anzeigen',
		category: 'OpenEducationBadges',

		edit: function(props) {
			return html`<div style="${{'border': '1px solid black', 'padding': '12px 24px'}}">
				<p><strong style="${{'font-size': '1.4em'}}">Open Education Badges List User Badges</strong></p>
				<${wp.components.SelectControl} label="Anzeigen"
					value="${props.attributes.display}"
					onChange="${display => props.setAttributes({display})}"
					options="${[
						{label: 'Bild und Name', value: 'image-title'},
						{label: 'Bild, Name, Kurzbeschreibung', value: 'image-title-shortdesc'},
						{label: 'Nur Bild', value: 'image'},
						{label: 'Nur Name', value: 'name'},
					]}"
				/>
				<${wp.components.SelectControl} label="Layout"
					value="${props.attributes.layout}"
					onChange="${layout => props.setAttributes({layout})}"
					options="${[
						{label: '4 Spalten', value: '25'},
						{label: '3 Spalten', value: '33'},
						{label: '2 Spalten', value: '50'},
						{label: '1 Spalte', value: '100'},
					]}"
				/>
			</div>`;
		},
		attributes: {
			display: {
				type: 'string'
			},
			layout: {
				type: 'string'
			}
		}
	} );
})();
