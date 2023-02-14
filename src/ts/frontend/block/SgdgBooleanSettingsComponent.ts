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
			onChange: (e: React.FormEvent<Element>) => {
				this.change(e);
			},
			type: 'checkbox',
		});
	}

	protected getValue(element: EventTarget): string {
		return (element as HTMLInputElement).checked ? 'true' : 'false';
	}
}
