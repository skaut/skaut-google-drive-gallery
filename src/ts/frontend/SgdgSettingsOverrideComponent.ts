/* exported SgdgSettingsOverrideComponent */

const el = wp.element.createElement;

class SgdgSettingsOverrideComponent extends wp.element.Component {
	private block: any;

	public constructor( props: any ) {
		super( props );
		this.block = props.block;
	}

	public render() {
		return el( wp.components.PanelBody, { title: sgdgBlockLocalize.settings_override, className: 'sgdg-block-settings', children: [
			el( 'h3', null, sgdgBlockLocalize.grid_section_name ),
			el( SgdgIntegerSettingsComponent, { block: this.block, name: 'grid_height' } ),
			el( SgdgIntegerSettingsComponent, { block: this.block, name: 'grid_spacing' } ),
			el( SgdgBooleanSettingsComponent, { block: this.block, name: 'dir_counts' } ),
			el( SgdgIntegerSettingsComponent, { block: this.block, name: 'page_size' } ),
			el( SgdgBooleanSettingsComponent, { block: this.block, name: 'page_autoload' } ),
			el( SgdgOrderingSettingsComponent, { block: this.block, name: 'image_ordering' } ),
			el( SgdgOrderingSettingsComponent, { block: this.block, name: 'dir_ordering' } ),
			el( 'h3', null, sgdgBlockLocalize.lightbox_section_name ),
			el( SgdgIntegerSettingsComponent, { block: this.block, name: 'preview_size' } ),
			el( SgdgBooleanSettingsComponent, { block: this.block, name: 'preview_loop' } ),
		] } );
	}
}
