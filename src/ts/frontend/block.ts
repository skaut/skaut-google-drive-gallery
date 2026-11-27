import type { ShortcodeAttrs } from '@wordpress/shortcode';

import { getCategories, registerBlockType } from '@wordpress/blocks';

import { SgdgBlockIconComponent } from './block/SgdgBlockIconComponent';
import { SgdgEditorComponent } from './block/SgdgEditorComponent';

function extractFromShortcode(attributes: ShortcodeAttrs): Array<string> {
	const path = attributes.named['path'];
	if (path === undefined || path === '') {
		return [];
	}
	return path.replace(/^\/+|\/+$/gu, '').split('/');
}

function renderFrontend(): null {
	return null;
}

registerBlockType('skaut-google-drive-gallery/gallery', {
	attributes: {
		breadcrumbs_root: {
			type: 'string',
		},
		dir_counts: {
			type: 'string',
		},
		dir_ordering_by: {
			type: 'string',
		},
		dir_ordering_order: {
			type: 'string',
		},
		grid_height: {
			type: 'number',
		},
		grid_spacing: {
			type: 'number',
		},
		image_ordering_by: {
			type: 'string',
		},
		image_ordering_order: {
			type: 'string',
		},
		page_autoload: {
			type: 'string',
		},
		page_size: {
			type: 'number',
		},
		path: {
			default: [],
			type: 'array',
		},
		preview_loop: {
			type: 'string',
		},
		preview_size: {
			type: 'number',
		},
	},
	// The media category only exists since WordPress 5.5. On 5.0-5.4, an unknown
	// category is a hard error and the block would not register at all, so fall
	// back to a category that exists on those versions.
	category: getCategories().some((category) => 'media' === category.slug)
		? 'media'
		: 'common',
	description: sgdgBlockLocalize.block_description,
	edit: SgdgEditorComponent,
	icon: SgdgBlockIconComponent,
	save: renderFrontend,
	title: sgdgBlockLocalize.block_name,
	transforms: {
		from: [
			{
				attributes: {
					path: {
						shortcode: extractFromShortcode,
						type: 'string',
					},
				},
				priority: 15,
				tag: 'sgdg',
				type: 'shortcode',
			},
		],
	},
});
