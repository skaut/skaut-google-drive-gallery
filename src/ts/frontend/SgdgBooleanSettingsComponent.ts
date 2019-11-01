/* exported SgdgBooleanSettingsComponent */

class SgdgBooleanSettingsComponent extends SgdgSettingsComponent {
	protected renderInput() {
		const el = wp.element.createElement;
		const value = this.block.getAttribute( this.name );
		return el( 'input', { checked: 'true' === this.state.value, className: 'sgdg-block-settings-boolean', disabled: undefined === value, onChange: ( e: React.FormEvent<Element> ) => {
			this.change( e );
		}, type: 'checkbox' } );
	}

	protected getValue( element: EventTarget ) {
		return ( element as HTMLInputElement ).checked ? 'true' : 'false';
	}
}
