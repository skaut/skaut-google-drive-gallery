import { ToggleControl } from '@wordpress/components';
import { Component, createElement } from '@wordpress/element';

import type { SgdgEditorComponent } from './SgdgEditorComponent';

interface SgdgOrderingSettingsComponentProps {
	readonly editor: SgdgEditorComponent;
	readonly name: BlockOrderingOptions;
}

interface SgdgOrderingSettingsComponentState {
	valueBy: string;
	valueOrder: string;
}

export class SgdgOrderingSettingsComponent extends Component<
	SgdgOrderingSettingsComponentProps,
	SgdgOrderingSettingsComponentState
> {
	public constructor(props: SgdgOrderingSettingsComponentProps) {
		super(props);
		const { editor, name } = this.props;
		let valueBy = editor.getAttribute(name + '_by') as string | undefined;
		let valueOrder = editor.getAttribute(name + '_order') as
			| string
			| undefined;
		if (undefined === valueBy) {
			valueBy = sgdgBlockLocalize[name].default_by;
		}
		if (undefined === valueOrder) {
			valueOrder = sgdgBlockLocalize[name].default_order;
		}
		this.state = { valueBy, valueOrder };
	}

	public render(): React.ReactNode {
		const { editor, name } = this.props;
		const { valueBy, valueOrder } = this.state;
		const disabledBy = undefined === editor.getAttribute(name + '_by');
		const disabledOrder =
			undefined === editor.getAttribute(name + '_order');
		return createElement('div', { className: 'sgdg-block-settings-row' }, [
			createElement(ToggleControl, {
				checked: !disabledBy && !disabledOrder,
				className: 'sgdg-block-settings-checkbox',
				label: createElement(
					'span',
					{ className: 'sgdg-block-settings-description' },
					[sgdgBlockLocalize[name].name, ':']
				),
				onChange: () => {
					this.toggle();
				},
			}),
			createElement(
				'select',
				{
					className: 'sgdg-block-settings-select',
					disabled: disabledOrder,
					onChange: (e: React.FormEvent) => {
						this.changeOrder(e);
					},
					placeholder: sgdgBlockLocalize[name].default_order,
					type: 'number',
					value: valueOrder,
				},
				[
					createElement(
						'option',
						{
							selected: 'ascending' === valueOrder,
							value: 'ascending',
						},
						sgdgBlockLocalize.ordering_option_ascending
					),
					createElement(
						'option',
						{
							selected: 'descending' === valueOrder,
							value: 'descending',
						},
						sgdgBlockLocalize.ordering_option_descending
					),
				]
			),
			createElement(
				'label',
				{
					className: 'sgdg-block-settings-radio',
					for: name + '_by_time',
				},
				[
					createElement('input', {
						checked: 'time' === valueBy,
						disabled: disabledBy,
						id: name + '_by_time',
						name: name + '_by',
						onChange: (e) => {
							this.changeBy(e);
						},
						type: 'radio',
						value: 'time',
					}),
					sgdgBlockLocalize.ordering_option_by_time,
				]
			),
			createElement(
				'label',
				{
					className: 'sgdg-block-settings-radio',
					for: name + '_by_name',
				},
				[
					createElement('input', {
						checked: 'name' === valueBy,
						disabled: disabledBy,
						id: name + '_by_name',
						name: name + '_by',
						onChange: (e) => {
							this.changeBy(e);
						},
						type: 'radio',
						value: 'name',
					}),
					sgdgBlockLocalize.ordering_option_by_name,
				]
			),
		]);
	}

	private toggle(): void {
		const { editor, name } = this.props;
		const { valueBy, valueOrder } = this.state;
		editor.setAttribute(
			name + '_by',
			undefined !== editor.getAttribute(name + '_by')
				? undefined
				: valueBy
		);
		editor.setAttribute(
			name + '_order',
			undefined !== editor.getAttribute(name + '_order')
				? undefined
				: valueOrder
		);
	}

	private changeBy(e: React.FormEvent): void {
		const { editor, name } = this.props;
		const target = e.target as HTMLInputElement;
		this.setState({ valueBy: target.value });
		editor.setAttribute(name + '_by', target.value);
	}

	private changeOrder(e: React.FormEvent): void {
		const { editor, name } = this.props;
		const target = e.target as HTMLSelectElement;
		this.setState({ valueOrder: target.value });
		editor.setAttribute(name + '_order', target.value);
	}
}
