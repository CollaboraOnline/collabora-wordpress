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
	Flex,
	FlexItem,
	PanelBody,
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
		callback( selected.id );
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
	const { id, mode } = attributes;
	let action = 'View';
	if ( mode === 'edit' ) {
		action = 'Edit';
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Parameters', 'collabora-wordpress' ) }>
					<Flex>
						<FlexItem>
							<TextControl
								label={ __(
									'Document',
									'collabora-wordpress'
								) }
								value={ id || '' }
								onChange={ ( value ) =>
									setAttributes( { id: value } )
								}
							/>
						</FlexItem>
						<FlexItem>
							<Button
								variant="primary"
								onClick={ () => {
									requestDocument( ( value ) =>
										setAttributes( { id: value.toString() } )
									);
								} }
							>
								Select
							</Button>
						</FlexItem>
					</Flex>
					<TextControl
						label={ __( 'Mode', 'collabora-wordpress' ) }
						value={ mode || 'view' }
						onChange={ ( value ) =>
							setAttributes( { mode: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>{ action + ' COOL document=' + id }</p>
		</>
	);
}
