"use strict";
jQuery( document ).ready(function($) {
	var blockStyle = { backgroundColor: '#900', color: '#fff', padding: '20px' };

	wp.blocks.registerBlockType( 'skaut-google-drive-gallery/gallery', {
		title: 'Google Drive gallery', // TODO: i18n
		icon: 'format-gallery',
		category: 'common',
		edit: function() {
			return wp.element.createElement( 'p', { style: blockStyle }, 'Hello editor.' );
		},
		save: function() {
			return wp.element.createElement( 'p', { style: blockStyle }, 'Hello saved content.' );
		},
	} );
});
