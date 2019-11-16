interface ShortcodeRegistry {
	shortcodes: Record<string, Shortcode>;
	init(): void;
	reflowAll(): void;
	onLightboxNavigation( e: JQuery ): void;
	removePageFromHistory(): void;
}

const ShortcodeRegistry: ShortcodeRegistry = {
	shortcodes: {},

	init(): void {
		$( '.sgdg-gallery-container' ).each( ( _, container ) => {
			const hash = $( container ).data( 'sgdgHash' );
			this.shortcodes[ hash.substr( 0, 8 ) ] = new Shortcode( container, hash );
		} );

		$( document ).on( 'start.ilb2 next.ilb2 previous.ilb2', ( _, e ) => this.onLightboxNavigation( e ) );
		$( document ).on( 'quit.ilb2', () => this.removePageFromHistory() );
	},

	reflowAll(): void {
		$.each( this.shortcodes, function( _, shortcode ) {
			shortcode.reflow();
		} );
	},

	onLightboxNavigation( e: JQuery ): void {
		const hash = $( e ).data( 'imagelightbox' );
		this.shortcodes[ hash ].onLightboxNavigation( e );
	},

	removePageFromHistory(): void {
		history.replaceState( history.state, '', removeQueryParameter( '[^-]+', 'page' ) );
	},
};
