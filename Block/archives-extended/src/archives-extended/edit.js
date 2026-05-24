/**
 * Editor UI for the Archives Extended block.
 *
 * Preview uses ServerSideRender so the editor reflects exactly what the PHP
 * render callback produces, including post-type-specific archive listings.
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
	Disabled,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

const TYPE_OPTIONS = [
	{ label: __( 'Yearly', 'archives-extended' ), value: 'yearly' },
	{ label: __( 'Monthly', 'archives-extended' ), value: 'monthly' },
	{ label: __( 'Weekly', 'archives-extended' ), value: 'weekly' },
	{ label: __( 'Daily', 'archives-extended' ), value: 'daily' },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		displayAsDropdown,
		showLabel,
		showPostCounts,
		type,
		postType,
		extraClasses,
		itemClasses,
	} = attributes;

	const postTypes = useSelect(
		( select ) => select( coreStore ).getPostTypes( { per_page: -1 } ),
		[]
	);

	const postTypeOptions = useMemo( () => {
		const fallback = [
			{ label: __( 'Posts', 'archives-extended' ), value: 'post' },
		];

		if ( ! postTypes ) {
			return fallback;
		}

		const filtered = postTypes
			.filter(
				( pt ) =>
					pt.viewable &&
					( pt.has_archive === true || pt.slug === 'post' )
			)
			.map( ( pt ) => ( {
				label: pt.name ?? pt.slug,
				value: pt.slug,
			} ) );

		// Ensure `post` is always selectable, mirroring the PHP side.
		if ( ! filtered.some( ( opt ) => opt.value === 'post' ) ) {
			filtered.unshift( {
				label: __( 'Posts', 'archives-extended' ),
				value: 'post',
			} );
		}

		return filtered;
	}, [ postTypes ] );

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'archives-extended' ) }>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Display as dropdown',
							'archives-extended'
						) }
						checked={ displayAsDropdown }
						onChange={ ( value ) =>
							setAttributes( { displayAsDropdown: value } )
						}
					/>
					{ displayAsDropdown && (
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __(
								'Show label',
								'archives-extended'
							) }
							checked={ showLabel }
							onChange={ ( value ) =>
								setAttributes( { showLabel: value } )
							}
						/>
					) }
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Show post counts',
							'archives-extended'
						) }
						checked={ showPostCounts }
						onChange={ ( value ) =>
							setAttributes( { showPostCounts: value } )
						}
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Group by', 'archives-extended' ) }
						value={ type }
						options={ TYPE_OPTIONS }
						onChange={ ( value ) =>
							setAttributes( { type: value } )
						}
					/>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Post type', 'archives-extended' ) }
						value={ postType }
						options={ postTypeOptions }
						onChange={ ( value ) =>
							setAttributes( { postType: value } )
						}
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __(
							'Additional container classes',
							'archives-extended'
						) }
						value={ extraClasses }
						onChange={ ( value ) =>
							setAttributes( { extraClasses: value } )
						}
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __(
							'Additional list item classes',
							'archives-extended'
						) }
						value={ itemClasses }
						onChange={ ( value ) =>
							setAttributes( { itemClasses: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						skipBlockSupportAttributes
					/>
				</Disabled>
			</div>
		</>
	);
}
