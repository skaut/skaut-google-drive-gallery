import { createElement } from '@wordpress/element';

import { SgdgSettingsComponent } from './SgdgSettingsComponent';

export class SgdgBooleanSettingsComponent extends SgdgSettingsComponent {
	protected override getValue(element: HTMLInputElement): string {
		return element.checked ? 'true' : 'false';
	}

	protected renderInput(
		onChange: (e: React.ChangeEvent<HTMLInputElement>) => void
	): React.ReactNode {
		const disabled =
			undefined === this.props.editor.getAttribute(this.props.name);
		return createElement('input', {
			checked: 'true' === this.state.value,
			className: 'sgdg-block-settings-boolean',
			disabled,
			onChange,
			type: 'checkbox',
		});
	}
}
