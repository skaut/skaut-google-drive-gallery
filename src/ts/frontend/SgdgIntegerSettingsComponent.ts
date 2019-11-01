/* exported SgdgIntegerSettingsComponent */

const el = wp.element.createElement;

class SgdgIntegerSettingsComponent extends SgdgSettingsComponent {
	protected renderInput() {
		const value = this.block.getAttribute( this.name );
		return el( 'input', { className: 'sgdg-block-settings-integer components-range-control__number', disabled: undefined === value, onChange: ( e ) => {
			this.change( e );
		}, placeholder: sgdgBlockLocalize[ this.name ].default, type: 'number', value: this.state.value } );
	}

	protected getValue( element: EventTarget ) {
		const value = parseInt( ( element as HTMLInputElement ).value );
		if ( isNaN( value ) ) {
			return undefined;
		}
		return value;
	}
}
