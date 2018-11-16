'use strict';
var el = wp.element.createElement;

var SgdgSettingsOverrideComponent = function( attributes ) {
	this.block = attributes.block;
};
SgdgSettingsOverrideComponent.prototype = Object.create( wp.element.Component.prototype );
SgdgSettingsOverrideComponent.prototype.render = function() {
	return el( wp.components.PanelBody, {title: sgdgBlockLocalize.settings_override, className: 'sgdg-block-settings'}, [
		el( SgdgIntegerSettingsComponent, {block: this.block, name: 'grid_height'}),
		el( SgdgIntegerSettingsComponent, {block: this.block, name: 'grid_spacing'})
	]);
};
