import type { ShortcodeAttrs } from '@wordpress/shortcode';

import { registerBlockType } from '@wordpress/blocks';

import { SgdgBlockIconComponent } from './block/SgdgBlockIconComponent';
import { SgdgEditorComponent } from './block/SgdgEditorComponent';

function extractFromShortcode(attributes: ShortcodeAttrs): Array<string> {
	const path = attributes.named['path'];
	if (path === undefined || path === '') {
		return [];
	}
	return path.replace(/^\/+|\/+$/g, '').split('/');
}

function renderFrontend(): null {
	return null;
}

registerBlockType('skaut-google-drive-gallery/gallery', {
	attributes: {
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
	category: 'media',
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
