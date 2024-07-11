import { PanelBody } from '@wordpress/components';
import { Component, createElement } from '@wordpress/element';

import { SgdgBooleanSettingsComponent } from './SgdgBooleanSettingsComponent';
import type { SgdgEditorComponent } from './SgdgEditorComponent';
import { SgdgIntegerSettingsComponent } from './SgdgIntegerSettingsComponent';
import { SgdgOrderingSettingsComponent } from './SgdgOrderingSettingsComponent';

interface SgdgSettingsOverrideComponentProps {
	readonly editor: SgdgEditorComponent;
}

export class SgdgSettingsOverrideComponent extends Component<SgdgSettingsOverrideComponentProps> {
	public render(): React.ReactNode {
		return createElement(PanelBody, {
			title: sgdgBlockLocalize.settings_override,
			className: 'sgdg-block-settings',
			children: [
				createElement('h3', null, sgdgBlockLocalize.grid_section_name),
				createElement(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'grid_height',
				}),
				createElement(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'grid_spacing',
				}),
				createElement(SgdgBooleanSettingsComponent, {
					editor: this.props.editor,
					name: 'dir_counts',
				}),
				createElement(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'page_size',
				}),
				createElement(SgdgBooleanSettingsComponent, {
					editor: this.props.editor,
					name: 'page_autoload',
				}),
				createElement(SgdgOrderingSettingsComponent, {
					editor: this.props.editor,
					name: 'image_ordering',
				}),
				createElement(SgdgOrderingSettingsComponent, {
					editor: this.props.editor,
					name: 'dir_ordering',
				}),
				createElement(
					'h3',
					null,
					sgdgBlockLocalize.lightbox_section_name
				),
				createElement(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'preview_size',
				}),
				createElement(SgdgBooleanSettingsComponent, {
					editor: this.props.editor,
					name: 'preview_loop',
				}),
			],
		});
	}
}
