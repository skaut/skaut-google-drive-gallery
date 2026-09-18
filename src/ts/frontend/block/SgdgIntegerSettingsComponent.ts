import { createElement } from '@wordpress/element';

import { SgdgSettingsComponent } from './SgdgSettingsComponent';

export class SgdgIntegerSettingsComponent extends SgdgSettingsComponent {
	protected override getValue(element: EventTarget): number | undefined {
		const value = parseInt((element as HTMLInputElement).value, 10);
		if (isNaN(value)) {
			return undefined;
		}
		return value;
	}

	protected renderInput(
		onChange: (e: React.FormEvent) => void
	): React.ReactNode {
		const disabled =
			undefined === this.props.editor.getAttribute(this.props.name);
		return createElement('input', {
			className:
				'sgdg-block-settings-integer components-range-control__number',
			disabled,
			onChange,
			placeholder: sgdgBlockLocalize[this.props.name].default,
			type: 'number',
			value: this.state.value,
		});
	}
}
