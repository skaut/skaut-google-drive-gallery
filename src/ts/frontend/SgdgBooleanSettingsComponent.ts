'use strict';
var el = wp.element.createElement;

class SgdgBooleanSettingsComponent extends SgdgSettingsComponent {
	protected renderInput() {
		var that = this;
		var value = this.block.getAttribute( this.name );
		return el( 'input', {checked: 'true' === this.state.value, className: 'sgdg-block-settings-boolean', disabled: undefined === value, onChange: function( e: React.FormEvent<Element> ) {
			that.change( e );
		}, type: 'checkbox'});
	}

	protected getValue( element: EventTarget ) {
		return (element as HTMLInputElement).checked ? 'true' : 'false';
	};
}
