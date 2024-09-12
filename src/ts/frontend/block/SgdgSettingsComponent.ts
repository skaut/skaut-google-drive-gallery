import { ToggleControl } from '@wordpress/components';
import { Component, createElement } from '@wordpress/element';

import type { SgdgEditorComponent } from './SgdgEditorComponent';

interface SgdgSettingsComponentProps {
	readonly editor: SgdgEditorComponent;
	readonly name: BlockOptions;
}

interface SgdgSettingsComponentState {
	value: number | string | undefined;
}

export abstract class SgdgSettingsComponent extends Component<
	SgdgSettingsComponentProps,
	SgdgSettingsComponentState
> {
	public constructor(props: SgdgSettingsComponentProps) {
		super(props);
		const { editor, name } = this.props;
		let value = editor.getAttribute(name) as string | undefined;
		if (undefined === value) {
			value = sgdgBlockLocalize[name].default;
		}
		this.state = { value };
	}

	public override render(): React.ReactNode {
		const { editor, name } = this.props;
		const disabled = undefined === editor.getAttribute(name);
		return createElement('div', { className: 'sgdg-block-settings-row ' }, [
			createElement(ToggleControl, {
				checked: !disabled,
				label: createElement(
					'span',
					{ className: 'sgdg-block-settings-description' },
					[sgdgBlockLocalize[name].name, ':']
				),
				className: 'sgdg-block-settings-checkbox',
				onChange: () => {
					this.toggle();
				},
			}),
			this.renderInput(),
		]);
	}

	protected change(e: React.FormEvent): void {
		const { editor, name } = this.props;
		const value = this.getValue(e.target);
		this.setState({ value });
		editor.setAttribute(name, value ?? sgdgBlockLocalize[name].default);
	}

	private toggle(): void {
		const { editor, name } = this.props;
		const { value } = this.state;
		editor.setAttribute(
			name,
			undefined !== editor.getAttribute(name) ? undefined : value
		);
	}

	protected abstract renderInput(): React.ReactNode;

	protected abstract getValue(
		element: EventTarget
	): number | string | undefined;
}
