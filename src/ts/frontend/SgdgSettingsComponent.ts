/* exported SgdgSettingsComponent */

const el = wp.element.createElement;

abstract class SgdgSettingsComponent extends wp.element.Component<any, any> {
	protected block: any;
	protected name: any;

	public constructor( props: any ) {
		super( props );
		this.block = props.block;
		this.name = props.name;
		let value = this.block.getAttribute( this.name );
		if ( undefined === value ) {
			value = sgdgBlockLocalize[ this.name ].default;
		}
		this.state = { value };
	}

	public render() {
		const that = this;
		const value = this.block.getAttribute( this.name );
		return el( 'div', { className: 'sgdg-block-settings-row ' }, [
			el( wp.components.ToggleControl, { checked: undefined !== value, className: 'sgdg-block-settings-checkbox', onChange() {
				that.toggle();
			} } ),
			el( 'span', { className: 'sgdg-block-settings-description' }, [
				sgdgBlockLocalize[ this.name ].name,
				':',
			] ),
			this.renderInput(),
		] );
	}

	protected abstract renderInput(): void;

	private toggle() {
		this.block.setAttribute( this.name, undefined !== this.block.getAttribute( this.name ) ? undefined : this.state.value );
	}

	protected change( e: React.FormEvent<Element> ) {
		const value = this.getValue( e.target! );
		this.setState( { value } );
		this.block.setAttribute( this.name, undefined === value ? sgdgBlockLocalize[ this.name ].default : value );
	}

	protected abstract getValue( element: EventTarget ): any;
}
