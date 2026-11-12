import { createElement } from '@wordpress/element';

import { SgdgSettingsComponent } from './SgdgSettingsComponent';

export class SgdgStringSettingsComponent extends SgdgSettingsComponent {
	protected override getValue(element: HTMLInputElement): string {
		return element.value;
	}

	protected renderInput(
		onChange: (e: React.ChangeEvent<HTMLInputElement>) => void
	): React.ReactNode {
		const disabled =
			undefined === this.props.editor.getAttribute(this.props.name);
		return createElement('input', {
			className: 'sgdg-block-settings-string',
			disabled,
			onChange,
			placeholder: sgdgBlockLocalize[this.props.name].default,
			type: 'text',
			value: this.state.value,
		});
	}
}
