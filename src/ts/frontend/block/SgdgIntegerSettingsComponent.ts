import { createElement } from '@wordpress/element';

import { SgdgSettingsComponent } from './SgdgSettingsComponent';

export class SgdgIntegerSettingsComponent extends SgdgSettingsComponent {
	protected renderInput(): React.ReactNode {
		const disabled =
			undefined === this.props.editor.getAttribute(this.props.name);
		return createElement('input', {
			className:
				'sgdg-block-settings-integer components-range-control__number',
			disabled,
			onChange: (e: React.FormEvent) => {
				this.change(e);
			},
			placeholder: sgdgBlockLocalize[this.props.name].default,
			type: 'number',
			value: this.state.value,
		});
	}

	// eslint-disable-next-line @typescript-eslint/class-methods-use-this -- Inherited method
	protected getValue(element: EventTarget): number | undefined {
		const value = parseInt((element as HTMLInputElement).value);
		if (isNaN(value)) {
			return undefined;
		}
		return value;
	}
}
