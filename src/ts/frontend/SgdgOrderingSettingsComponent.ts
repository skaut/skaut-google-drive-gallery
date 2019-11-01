'use strict';
const el = wp.element.createElement;

class SgdgOrderingSettingsComponent extends wp.element.Component<any, any> {
	private block: any;
	private name: any;

	public constructor( props: any ) {
		super( props );
		let valueBy, valueOrder;
		this.block = props.block;
		this.name = props.name;
		valueBy = this.block.getAttribute( this.name + '_by' );
		valueOrder = this.block.getAttribute( this.name + '_order' );
		if ( undefined === valueBy ) {
			valueBy = sgdgBlockLocalize[ this.name ].default_by;
		}
		if ( undefined === valueOrder ) {
			valueOrder = sgdgBlockLocalize[ this.name ].default_order;
		}
		this.state = { valueBy, valueOrder };
	}

	public render() {
		const that = this;
		const valueBy = this.block.getAttribute( this.name + '_by' );
		const valueOrder = this.block.getAttribute( this.name + '_order' );
		return el( 'div', { className: 'sgdg-block-settings-row' }, [
			el( wp.components.ToggleControl, { checked: undefined !== valueBy && undefined !== valueOrder, className: 'sgdg-block-settings-checkbox', onChange() {
				that.toggle();
			} } ),
			el( 'span', { className: 'sgdg-block-settings-description' }, [
				sgdgBlockLocalize[ this.name ].name,
				':',
			] ),
			el( 'select', { className: 'sgdg-block-settings-select', disabled: undefined === valueOrder, onChange( e: React.FormEvent<Element> ) {
				that.changeOrder( e );
			}, placeholder: sgdgBlockLocalize[ this.name ].default_order, type: 'number', value: this.state.valueOrder }, [
				el( 'option', { selected: 'ascending' === this.state.valueOrder, value: 'ascending' }, sgdgBlockLocalize.ordering_option_ascending ),
				el( 'option', { selected: 'descending' === this.state.valueOrder, value: 'descending' }, sgdgBlockLocalize.ordering_option_descending ),
			] ),
			el( 'label', { className: 'sgdg-block-settings-radio', for: this.name + '_by_time' }, [
				el( 'input', { checked: 'time' === this.state.valueBy, disabled: undefined === valueBy, id: this.name + '_by_time', name: this.name + '_by', onChange( e ) {
					that.changeBy( e );
				}, type: 'radio', value: 'time' } ),
				sgdgBlockLocalize.ordering_option_by_time
			] ),
			el( 'label', { className: 'sgdg-block-settings-radio', for: this.name + '_by_name' }, [
				el( 'input', { checked: 'name' === this.state.valueBy, disabled: undefined === valueBy, id: this.name + '_by_name', name: this.name + '_by', onChange( e ) {
					that.changeBy( e );
				}, type: 'radio', value: 'name' } ),
				sgdgBlockLocalize.ordering_option_by_name
			] ),
		] );
	}

	private toggle() {
		this.block.setAttribute( this.name + '_by', undefined !== this.block.getAttribute( this.name + '_by' ) ? undefined : this.state.valueBy );
		this.block.setAttribute( this.name + '_order', undefined !== this.block.getAttribute( this.name + '_order' ) ? undefined : this.state.valueOrder );
	}

	private changeBy( e: React.FormEvent<Element> ) {
		const target = e.target as HTMLInputElement;
		this.setState( { valueBy: target.value } );
		this.block.setAttribute( this.name + '_by', target.value );
	}

	private changeOrder( e: React.FormEvent<Element> ) {
		const target = e.target as HTMLSelectElement;
		this.setState( { valueOrder: target.value } );
		this.block.setAttribute( this.name + '_order', target.value );
	}
}
