/* exported SgdgEditorComponent */

class SgdgEditorComponent extends wp.element.Component<any, any> {
	public constructor( props: any ) {
		super( props );
		this.state = { error: undefined, list: undefined };
	}

	public componentDidMount() {
		this.ajax();
	}

	public render() {
		const el = wp.element.createElement;
		const children = [];
		const path: Array<React.ReactNode> = [ el( 'a', { onClick: ( e: Event ) => {
			this.pathClick( this, e );
		} }, sgdgBlockLocalize.root_name ) ];
		let lineClass;
		if ( this.state.error ) {
			return el( 'div', { class: 'notice notice-error' }, el( 'p', null, this.state.error ) );
		}
		if ( this.state.list ) {
			if ( 0 < this.getAttribute( 'path' ).length ) {
				children.push( el( 'tr', null, el( 'td', { class: 'row-title' }, el( 'label', { onClick: ( e: Event ) => {
					this.labelClick( this, e );
				} }, '..' ) ) ) );
			}
			for ( let i = 0; i < this.state.list.length; i++ ) {
				lineClass = ( 0 === this.getAttribute( 'path' ).length && 1 === i % 2 ) || ( 0 < this.getAttribute( 'path' ).length && 0 === i % 2 ) ? 'alternate' : '';
				children.push( el( 'tr', { class: lineClass }, el( 'td', { class: 'row-title' }, el( 'label', { onClick: ( e: Event ) => {
					this.labelClick( this, e );
				} }, this.state.list[ i ] ) ) ) );
			}
			for ( let i = 0; i < this.getAttribute( 'path' ).length; i++ ) {
				path.push( ' > ' );
				path.push( el( 'a', { 'data-id': this.getAttribute( 'path' )[ i ], onClick: ( e: Event ) => {
					this.pathClick( this, e );
				} }, this.getAttribute( 'path' )[ i ] ) );
			}
		}
		return el( wp.element.Fragment, null, [
			el( wp.editor.InspectorControls, null,
				el( SgdgSettingsOverrideComponent, { block: this } )
			),
			el( 'table', { class: 'widefat' }, [
				el( 'thead', null,
					el( 'tr', null,
						el( 'th', { class: 'sgdg-block-editor-path' }, path )
					)
				),
				el( 'tbody', null, children ),
				el( 'tfoot', null,
					el( 'tr', null,
						el( 'th', { class: 'sgdg-block-editor-path' }, path )
					)
				),
			] ),
		] );
	}

	private ajax() {
		$.get( sgdgBlockLocalize.ajax_url, {
			_ajax_nonce: sgdgBlockLocalize.nonce, // eslint-disable-line @typescript-eslint/camelcase
			action: 'list_gallery_dir',
			path: this.getAttribute( 'path' ),
		}, ( data ) => {
			if ( data.directories ) {
				this.setState( { list: data.directories } );
			} else if ( data.error ) {
				this.setState( { error: data.error } );
			}
		} );
	}

	private pathClick( that: SgdgEditorComponent, e: Event ) {
		let path = that.getAttribute( 'path' );
		path = path.slice( 0, path.indexOf( $( e.currentTarget! ).data( 'id' ) ) + 1 );
		that.setAttribute( 'path', path );
		that.setState( { error: undefined, list: undefined }, that.ajax );
	}

	private labelClick( that: SgdgEditorComponent, e: Event ) {
		const newDir = $( e.currentTarget! ).text();
		let path;
		if ( '..' === newDir ) {
			path = that.getAttribute( 'path' ).slice( 0, that.getAttribute( 'path' ).length - 1 );
		} else {
			path = that.getAttribute( 'path' ).concat( newDir );
		}
		that.setAttribute( 'path', path );
		that.setState( { error: undefined, list: undefined }, that.ajax );
	}

	private getAttribute( name: string ) {
		return this.props.attributes[ name ];
	}

	private setAttribute( name: string, value: string ) {
		const attr: any = {};
		attr[ name ] = value;
		this.props.setAttributes( attr );
	}
}
