/**
 * Spdx-License: MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import metadata from './block.json';

const icon = (
	<svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
		<linearGradient
			id="a"
			gradientUnits="userSpaceOnUse"
			x1="13"
			x2="1"
			href="#b"
			y1="16"
			y2="0"
		/>
		<linearGradient id="b">
			<stop offset="0" stop-color="#0369a3" />
			<stop offset="1" stop-color="#1c99e0" />
		</linearGradient>
		<linearGradient
			id="c"
			gradientTransform="matrix(.999981 0 0 .999625 -109.99781 -976.00286)"
			gradientUnits="userSpaceOnUse"
			x1="124.00017"
			x2="111.99994"
			href="#b"
			y1="992.375"
			y2="976.36902"
		/>
		<path
			d="m1.8125.00586c-.458392.0875-.82072.53358-.8125 1v13.99414c.00005.52339.47643.99995 1 1h12c .52357-.00005.99995-.47661 1-1v-7.99609c.006-.26396-.0975-.52904-.28125-.71875l-5-5.99805c-.189776-.18363-.454695-.28737-.71875-.28125h-7c-.0623-.006-.125182-.006-.1875 0zm9.53125 0c-.331493.10559-.443055.60775-.1875.84375l3 2.99805c.277145.26269.82915.0378.84375-.34375v-2.99805c-.00003-.26169-.238215-.49997-.5-.5h-3c-.0517-.008-.104591-.008-.15625 0z"
			fill="url(#c)"
		/>
		<path d="m2 1v14h12v-8l-5-6z" fill="#fff" />
		<path
			d="m4 6v1h6v-1zm0 2v1h8v-1zm0 2v1h8v-1zm0 2v1h6v-1z"
			fill="url(#a)"
		/>
	</svg>
);

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	icon,
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
