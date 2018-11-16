'use strict';
var el = wp.element.createElement;

var SgdgSettingsComponent = function( attributes ) {
	var value;
	this.block = attributes.block;
	this.name = attributes.name;
	value = this.block.getAttribute( this.name );
	if ( undefined === value ) {
		value = sgdgBlockLocalize[this.name].default;
	}
	this.state = {value: value};
};
SgdgSettingsComponent.prototype = Object.create( wp.element.Component.prototype );
SgdgSettingsComponent.prototype.render = function() {
	var that = this;
	var value = this.block.getAttribute( this.name );
	return [
		el( wp.components.ToggleControl, {checked: undefined !== value, className: 'sgdg-block-settings-checkbox', onChange: function( e ) {
			that.toggle();
		}}),
		sgdgBlockLocalize[this.name].name,
		':',
		this.renderInput()
	];
};
SgdgSettingsComponent.prototype.toggle = function() {
	this.block.setAttribute( this.name, undefined !== this.block.getAttribute( this.name ) ? undefined : this.state.value );
};
SgdgSettingsComponent.prototype.change = function( e ) {
	var value = parseInt( e.nativeEvent.target.value );
	this.setState({value: isNaN( value ) ? undefined : value});
	this.block.setAttribute( this.name, isNaN( value ) ? sgdgBlockLocalize[this.name].default : value );
};
