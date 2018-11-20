'use strict';
var el = wp.element.createElement;

var SgdgSettingsOverrideComponent = function( attributes ) {
	this.block = attributes.block;
};
SgdgSettingsOverrideComponent.prototype = Object.create( wp.element.Component.prototype );
SgdgSettingsOverrideComponent.prototype.render = function() {
	return el( wp.components.PanelBody, {title: sgdgBlockLocalize.settings_override, className: 'sgdg-block-settings'}, [
		el( 'h3', {}, sgdgBlockLocalize['grid_section_name']),
		el( SgdgIntegerSettingsComponent, {block: this.block, name: 'grid_height'}),
		el( SgdgIntegerSettingsComponent, {block: this.block, name: 'grid_spacing'}),
		el( SgdgBooleanSettingsComponent, {block: this.block, name: 'dir_counts'}),
		el( SgdgOrderingSettingsComponent, {block: this.block, name: 'image_ordering'}),
		el( 'h3', {}, sgdgBlockLocalize['lightbox_section_name']),
		el( SgdgIntegerSettingsComponent, {block: this.block, name: 'preview_size'}),
		el( SgdgIntegerSettingsComponent, {block: this.block, name: 'preview_speed'}),
		el( SgdgBooleanSettingsComponent, {block: this.block, name: 'preview_arrows'}),
		el( SgdgBooleanSettingsComponent, {block: this.block, name: 'preview_close_button'}),
		el( SgdgBooleanSettingsComponent, {block: this.block, name: 'preview_loop'}),
		el( SgdgBooleanSettingsComponent, {block: this.block, name: 'preview_activity_indicator'})
	]);
};
