/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';

import {
	Button,
	PanelBody,
	SelectControl,
	TextControl,
} from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

function requestDocument( callback ) {
	const coolRequester = wp.media( {
		title: wp.i18n.__( 'Select or Upload Office Document' ),
		library: {
			type: [ 'application/*' ],
		},
	} );
	coolRequester.on( 'select', function () {
		const selected = coolRequester.state().get( 'selection' ).first();
		callback( selected.id, selected.attributes.filename );
	} );
	coolRequester.open();
}

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const { id, filename, mode } = attributes;
	let content;
	if ( typeof filename === 'undefined' ) {
		content = wp.i18n.__( 'Please select a document.' );
	} else {
		let action = wp.i18n.__( 'View document' );
		if ( mode === 'edit' ) {
			action = wp.i18n.__( 'Edit document' );
		}

		content = `${ action } "${ filename }".`;
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Parameters', 'collabora-online' ) }>
					<TextControl
						label={ __( 'Document', 'collabora-online' ) }
						value={ id || '' }
						onChange={ ( value ) => setAttributes( { id: value } ) }
					/>
					<Button
						variant="primary"
						onClick={ () => {
							requestDocument( ( selId, selFilename ) =>
								setAttributes( {
									id: selId.toString(),
									filename: selFilename,
								} )
							);
						} }
					>
						Select
					</Button>
					<SelectControl
						label={ __( 'Mode', 'collabora-online' ) }
						value={ mode || 'view' }
						options={ [
							{
								value: 'view',
								label: __( 'View', 'collabora-online' ),
							},
							{
								value: 'review',
								label: __( 'Review', 'collabora-online' ),
							},
							{
								value: 'edit',
								label: __( 'Edit', 'collabora-online' ),
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { mode: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>{ `${ content }` }</p>
		</>
	);
}
