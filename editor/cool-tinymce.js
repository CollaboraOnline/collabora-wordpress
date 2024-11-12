/*
 * Copyright the Collabora Online contributors.
 *
 * SPDX-License-Identifier: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

(function () {
	let cool_button_click = function (editor) {
		let cool_requester = wp.media(
			{
				title: wp.i18n.__('Select or Upload Office Document'),
				library: {
					type: [ 'application/*' ]
				}
			}
		);
		cool_requester.on('select', function () {
			const selected = cool_requester.state().get( 'selection' ).first();
			const id = selected.id;
			// XXX fixme when we have block support.
			// let content = "<!-- wp:collabora-wordpress/cool -->\n";
			let content = `[cool id=${id} mode=view]\n`;
			// content += "<!-- /wp:collabora-wordpress/cool -->\n";
			editor.insertContent(content);
		});
		cool_requester.open();
	};

	let cool_plugin = function (editor) {
		editor.addButton(
			'cool-shortcode-button',
			{
				tooltip: wp.i18n.__('Add Collabora Online document'),
				onclick: cool_button_click.bind(null, editor),
			}
		);
	};

	tinymce.PluginManager.add('cool-shortcode-button', cool_plugin);
})();
