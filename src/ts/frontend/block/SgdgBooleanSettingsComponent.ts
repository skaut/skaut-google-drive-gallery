import { createElement } from '@wordpress/element';

import { SgdgSettingsComponent } from './SgdgSettingsComponent';

export class SgdgBooleanSettingsComponent extends SgdgSettingsComponent {
	protected renderInput(): React.ReactNode {
		const disabled =
			undefined === this.props.editor.getAttribute(this.props.name);
		return createElement('input', {
			checked: 'true' === this.state.value,
			className: 'sgdg-block-settings-boolean',
			disabled,
			onChange: (e: React.FormEvent) => {
				this.change(e);
			},
			type: 'checkbox',
		});
	}

	// eslint-disable-next-line @typescript-eslint/class-methods-use-this -- Inherited method
	protected getValue(element: EventTarget): string {
		return (element as HTMLInputElement).checked ? 'true' : 'false';
	}
}
