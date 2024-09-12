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
	public override render(): React.ReactNode {
		const { editor } = this.props;
		return createElement(PanelBody, {
			title: sgdgBlockLocalize.settings_override,
			className: 'sgdg-block-settings',
			children: [
				createElement('h3', null, sgdgBlockLocalize.grid_section_name),
				createElement(SgdgIntegerSettingsComponent, {
					editor,
					name: 'grid_height',
				}),
				createElement(SgdgIntegerSettingsComponent, {
					editor,
					name: 'grid_spacing',
				}),
				createElement(SgdgBooleanSettingsComponent, {
					editor,
					name: 'dir_counts',
				}),
				createElement(SgdgIntegerSettingsComponent, {
					editor,
					name: 'page_size',
				}),
				createElement(SgdgBooleanSettingsComponent, {
					editor,
					name: 'page_autoload',
				}),
				createElement(SgdgOrderingSettingsComponent, {
					editor,
					name: 'image_ordering',
				}),
				createElement(SgdgOrderingSettingsComponent, {
					editor,
					name: 'dir_ordering',
				}),
				createElement(
					'h3',
					null,
					sgdgBlockLocalize.lightbox_section_name
				),
				createElement(SgdgIntegerSettingsComponent, {
					editor,
					name: 'preview_size',
				}),
				createElement(SgdgBooleanSettingsComponent, {
					editor,
					name: 'preview_loop',
				}),
			],
		});
	}
}
