'use strict';
var el = wp.element.createElement;

var SgdgIntegerSettingsComponent = function( attributes ) {
	var value;
	this.block = attributes.block;
	this.name = attributes.name;
	value = this.block.getAttribute( this.name );
	if ( undefined === value ) {
		value = sgdgBlockLocalize[this.name].default;
	}
	this.state = {value: value};
};
SgdgIntegerSettingsComponent.prototype = Object.create( wp.element.Component.prototype );
SgdgIntegerSettingsComponent.prototype.render = function() {
	var that = this;
	var value = this.block.getAttribute( this.name );
	return [
		el( wp.components.ToggleControl, {checked: undefined !== value, className: 'sgdg-block-settings-checkbox', onChange: function( e ) {
			that.toggle();
		}}),
		sgdgBlockLocalize[this.name].name,
		':',
		el( 'input', {className: 'sgdg-block-settings-integer components-range-control__number', disabled: undefined === value, onChange: function( e ) {
			that.change( e );
		}, placeholder: sgdgBlockLocalize[this.name].default, type: 'number', value: this.state.value})
	];
};
SgdgIntegerSettingsComponent.prototype.toggle = function() {
	this.block.setAttribute( this.name, undefined !== this.block.getAttribute( this.name ) ? undefined : this.state.value );
};
SgdgIntegerSettingsComponent.prototype.change = function( e ) {
	var value = parseInt( e.nativeEvent.target.value );
	this.setState({value: isNaN( value ) ? undefined : value});
	this.block.setAttribute( this.name, isNaN( value ) ? sgdgBlockLocalize[this.name].default : value );
};
