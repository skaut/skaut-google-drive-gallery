'use strict';
var el = wp.element.createElement;

abstract class SgdgSettingsComponent extends wp.element.Component<any, any> {
	protected block: any;
	protected name: any;

	public constructor( props ) {
		super( props );
		var value;
		this.block = props.block;
		this.name = props.name;
		value = this.block.getAttribute( this.name );
		if ( undefined === value ) {
			value = sgdgBlockLocalize[this.name].default;
		}
		this.state = {value: value};
	}

	public render() {
		var that = this;
		var value = this.block.getAttribute( this.name );
		return el( 'div', {className: 'sgdg-block-settings-row'}, [
			el( wp.components.ToggleControl, {checked: undefined !== value, className: 'sgdg-block-settings-checkbox', onChange: function() {
				that.toggle();
			}}),
			el( 'span', {className: 'sgdg-block-settings-description'}, [
				sgdgBlockLocalize[this.name].name,
				':'
			]),
			this.renderInput()
		]);
	}

	protected abstract renderInput();

	private toggle() {
		this.block.setAttribute( this.name, undefined !== this.block.getAttribute( this.name ) ? undefined : this.state.value );
	}

	protected change( e ) {
		var value = this.getValue( e.target );
		this.setState({value: value});
		this.block.setAttribute( this.name, undefined === value ? sgdgBlockLocalize[this.name].default : value );
	}

	protected abstract getValue( _: any );
}
