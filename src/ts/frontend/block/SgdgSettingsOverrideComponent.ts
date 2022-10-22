import { createElement as el, Component } from '@wordpress/element';
import { PanelBody } from '@wordpress/components';

import { SgdgBooleanSettingsComponent } from './SgdgBooleanSettingsComponent';
import { SgdgEditorComponent } from './SgdgEditorComponent';
import { SgdgIntegerSettingsComponent } from './SgdgIntegerSettingsComponent';
import { SgdgOrderingSettingsComponent } from './SgdgOrderingSettingsComponent';

interface SgdgSettingsOverrideComponentProps {
	editor: SgdgEditorComponent;
}

export class SgdgSettingsOverrideComponent extends Component<SgdgSettingsOverrideComponentProps> {
	public render(): React.ReactNode {
		return el(PanelBody, {
			title: sgdgBlockLocalize.settings_override,
			className: 'sgdg-block-settings',
			children: [
				el('h3', null, sgdgBlockLocalize.grid_section_name),
				el(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'grid_height',
				}),
				el(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'grid_spacing',
				}),
				el(SgdgBooleanSettingsComponent, {
					editor: this.props.editor,
					name: 'dir_counts',
				}),
				el(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'page_size',
				}),
				el(SgdgBooleanSettingsComponent, {
					editor: this.props.editor,
					name: 'page_autoload',
				}),
				el(SgdgOrderingSettingsComponent, {
					editor: this.props.editor,
					name: 'image_ordering',
				}),
				el(SgdgOrderingSettingsComponent, {
					editor: this.props.editor,
					name: 'dir_ordering',
				}),
				el('h3', null, sgdgBlockLocalize.lightbox_section_name),
				el(SgdgIntegerSettingsComponent, {
					editor: this.props.editor,
					name: 'preview_size',
				}),
				el(SgdgBooleanSettingsComponent, {
					editor: this.props.editor,
					name: 'preview_loop',
				}),
			],
		});
	}
}
