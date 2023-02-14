import { ToggleControl } from '@wordpress/components';
import { Component, createElement } from '@wordpress/element';

import { SgdgEditorComponent } from './SgdgEditorComponent';

interface SgdgSettingsComponentProps {
	editor: SgdgEditorComponent;
	name: BlockOptions;
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
		let value = this.props.editor.getAttribute(this.props.name) as
			| string
			| undefined;
		if (undefined === value) {
			value = sgdgBlockLocalize[this.props.name].default;
		}
		this.state = { value };
	}

	public render(): React.ReactNode {
		const disabled =
			undefined === this.props.editor.getAttribute(this.props.name);
		return createElement('div', { className: 'sgdg-block-settings-row ' }, [
			createElement(ToggleControl, {
				checked: !disabled,
				className: 'sgdg-block-settings-checkbox',
				onChange: () => {
					this.toggle();
				},
			}),
			createElement(
				'span',
				{ className: 'sgdg-block-settings-description' },
				[sgdgBlockLocalize[this.props.name].name, ':']
			),
			this.renderInput(),
		]);
	}

	protected change(e: React.FormEvent<Element>): void {
		const value = this.getValue(e.target);
		this.setState({ value });
		this.props.editor.setAttribute(
			this.props.name,
			undefined === value
				? sgdgBlockLocalize[this.props.name].default
				: value
		);
	}

	private toggle(): void {
		this.props.editor.setAttribute(
			this.props.name,
			undefined !== this.props.editor.getAttribute(this.props.name)
				? undefined
				: this.state.value
		);
	}

	protected abstract renderInput(): React.ReactNode;

	protected abstract getValue(
		element: EventTarget
	): number | string | undefined;
}
