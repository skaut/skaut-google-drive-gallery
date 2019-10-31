'use strict';
var el = wp.element.createElement;

class SgdgIntegerSettingsComponent extends SgdgSettingsComponent {
	protected renderInput() {
		var that = this;
		var value = this.block.getAttribute( this.name );
		return el( 'input', {className: 'sgdg-block-settings-integer components-range-control__number', disabled: undefined === value, onChange: function( e ) {
				that.change( e );
			}, placeholder: sgdgBlockLocalize[this.name].default, type: 'number', value: this.state.value});
	}

	protected getValue( element ) {
		var value = parseInt( element.value );
		if ( isNaN( value ) ) {
			return undefined;
		}
		return value;
	}
}
