import justifiedLayout = require( 'justified-layout' );

jQuery( document ).ready( function( $ ) {
	const loading: Array<string> = [];

	function reflow( element: JQuery ): void {
		let j = 0;
		const loaded: Array<boolean> = [];
		const ratios: Array<number> = [];
		let bbox, sizes, val;
		element.find( '.sgdg-gallery' ).children().each( function( i ) {
			$( this ).css( 'display', 'inline-block' );
			val = ( this.firstChild as HTMLImageElement ).naturalWidth / ( this.firstChild as HTMLImageElement ).naturalHeight;
			if ( 0 < $( this ).find( 'svg' ).length ) {
				bbox = ( $( this ).find( 'svg' )[ 0 ] as unknown as SVGGraphicsElement ).getBBox();
				val = bbox.width / bbox.height;
			}
			if ( $( this ).hasClass( 'sgdg-grid-square' ) ) {
				val = 1;
			}
			if ( isNaN( val ) ) {
				loaded[ i ] = false;
			} else {
				loaded[ i ] = true;
				ratios.push( val );
			}
			$( this ).css( 'position', 'absolute' );
		} );
		if ( 0 < ratios.length ) {
			element.find( '.sgdg-loading' ).remove();
		}
		const positions = justifiedLayout( ratios, {
			containerWidth: element.find( '.sgdg-gallery' ).width(),
			containerPadding: { top: 10, left: 0, right: 0, bottom: 0 },
			boxSpacing: parseInt( sgdgShortcodeLocalize.grid_spacing ),
			targetRowHeight: parseInt( sgdgShortcodeLocalize.grid_height ),
			targetRowHeightTolerance: 0.15,
			edgeCaseMinRowHeight: 0,
		} );
		element.find( '.sgdg-gallery' ).children().each( function( i ) {
			if ( ! loaded[ i ] ) {
				$( this ).css( 'display', 'none' );
				return;
			}
			sizes = positions.boxes[ j ];
			j++;
			const containerPosition = element.find( '.sgdg-gallery' ).position();
			$( this ).css( 'top', sizes.top + containerPosition.top );
			$( this ).css( 'left', sizes.left + containerPosition.left );
			$( this ).width( sizes.width );
			$( this ).height( sizes.height );
		} );
		element.find( '.sgdg-gallery' ).height( positions.containerHeight );
	}

	function renderBreadcrumbs( hash: string, path: Array<PartialDirectory> ): string {
		let html = '<div><a data-sgdg-path="" href="' + removeQueryParameter( hash, 'path' ) + '">' + sgdgShortcodeLocalize.breadcrumbs_top + '</a>';
		let field = '';
		$.each( path, function( _, crumb ) {
			field += crumb.id + '/';
			html += ' > <a data-sgdg-path="' + field.slice( 0, -1 ) + '" href="' + addQueryParameter( hash, 'path', field.slice( 0, -1 ) ) + '">' + crumb.name + '</a>';
		} );
		html += '</div>';
		return html;
	}

	function renderDirectory( hash: string, directory: Directory ): string {
		let html = '';
		let newPath = getQueryParameter( hash, 'path' );
		let iconClass = '';
		newPath = ( newPath ? newPath + '/' : '' ) + directory.id;
		html += '<a class="sgdg-grid-a sgdg-grid-square" data-sgdg-path="' + newPath + '" href="';
		html += addQueryParameter( hash, 'path', newPath );
		html += '"';
		if ( directory.thumbnail ) {
			html += ' style="background-image: url(\'' + directory.thumbnail + '\');">';
		} else {
			html += '><svg class="sgdg-dir-icon" x="0px" y="0px" focusable="false" viewBox="0 0 24 24" fill="#8f8f8f"><path d="M10 4H4c-1.1 0-2 .9-2 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"></path></svg>';
		}
		html += '<div class="sgdg-dir-overlay"><div class="sgdg-dir-name">' + directory.name + '</div>';
		if ( directory.dircount ) {
			html += '<span class="sgdg-count-icon dashicons dashicons-category"></span> ' + directory.dircount + ( 1000 === directory.dircount ? '+' : '' );
		}
		if ( directory.imagecount ) {
			if ( directory.dircount ) {
				iconClass = ' sgdg-count-icon-indent';
			}
			html += '<span class="sgdg-count-icon dashicons dashicons-format-image' + iconClass + '"></span> ' + directory.imagecount + ( 1000 === directory.imagecount ? '+' : '' );
		}
		iconClass = '';
		if ( directory.videocount ) {
			if ( directory.dircount || directory.imagecount ) {
				iconClass = ' sgdg-count-icon-indent';
			}
			html += '<span class="sgdg-count-icon dashicons dashicons-video-alt3' + iconClass + '"></span> ' + directory.videocount;
		}
		html += '</div></a>';
		return html;
	}

	function renderImage( hash: string, page: number, image: Image ): string {
		let html = '<a class="sgdg-grid-a" data-imagelightbox="' + hash + '"';
		html += 'data-ilb2-id="' + image.id + '"';
		html += 'data-ilb2-caption="' + image.description + '"';
		html += 'data-sgdg-page="' + page + '"';
		html += ' href="' + image.image + '"><img class="sgdg-grid-img" src="' + image.thumbnail + '"></a>';
		return html;
	}

	function renderVideo( hash: string, page: number, video: Video ): string {
		let html = '<a class="sgdg-grid-a" data-imagelightbox="' + hash + '"';
		html += 'data-ilb2-id="' + video.id + '"';
		html += 'data-sgdg-page="' + page + '"';
		html += ' data-ilb2-video=\'' + JSON.stringify( { controls: 'controls', autoplay: 'autoplay', sources: [ { src: video.src, type: video.mimeType } ] } ) + '\'>';
		html += '<img class="sgdg-grid-img" src="' + video.thumbnail + '">';
		html += '</a>';
		return html;
	}

	function renderMoreButton(): string {
		return '<div class="sgdg-more-button"><div>' + sgdgShortcodeLocalize.load_more + '</div></div>';
	}

	function reflowTimer( hash: string ): void {
		reflow( $( '[data-sgdg-hash=' + hash + ']' ) );
		$( '.sgdg-gallery-container[data-sgdg-hash!=' + hash + ']' ).each( function() {
			reflow( $( this ) );
		} );
		if ( -1 !== loading.indexOf( hash ) ) {
			setTimeout( function() {
				reflowTimer( hash );
			}, 1000 );
		}
	}

	class Shortcode {
		private readonly container: JQuery;
		private readonly hash: string;
		private readonly shortHash: string;

		private lightbox: JQuery = $(); // TODO
		private hasMore = false;
		private path = '';
		private lastPage = 1;

		public constructor( container: JQuery, hash: string ) {
			this.container = container;
			this.hash = hash;
			this.shortHash = hash.substr( 0, 8 );
			this.init();
			$( window ).on( 'popstate', () => this.init() );
			$( window ).resize( () => {
				reflow( this.container );
			} );
		}

		public onLightboxNavigation( e: JQuery ): void {
			const page = $( e ).data( 'sgdg-page' );
			const children = $( e ).parent().children().length;
			history.replaceState( history.state, '', addQueryParameter( this.shortHash, 'page', page ) );
			if ( 'true' === sgdgShortcodeLocalize.page_autoload && this.hasMore && $( e ).index() >= Math.min( children - 2, Math.floor( 0.9 * children ) ) ) {
				this.add();
			}
		}

		private init(): void {
			const newPath = getQueryParameter( this.shortHash, 'path' );
			if ( this.path !== newPath ) {
				this.path = newPath;
				this.get();
			}
		}

		private get(): void {
			this.path = getQueryParameter( this.shortHash, 'path' );
			this.lastPage = parseInt( getQueryParameter( this.shortHash, 'page' ) ) || 1;
			this.lightbox = $().imageLightbox( {
				allowedTypes: '',
				animationSpeed: parseInt( sgdgShortcodeLocalize.preview_speed, 10 ),
				activity: ( 'true' === sgdgShortcodeLocalize.preview_activity ),
				arrows: ( 'true' === sgdgShortcodeLocalize.preview_arrows ),
				button: ( 'true' === sgdgShortcodeLocalize.preview_closebutton ),
				fullscreen: true,
				history: true,
				overlay: true,
				caption: ( 'true' === sgdgShortcodeLocalize.preview_captions ),
				quitOnEnd: ( 'true' === sgdgShortcodeLocalize.preview_quitOnEnd ),
			} );
			this.container.find( '.sgdg-gallery' ).replaceWith( '<div class="sgdg-loading"><div></div></div>' );
			this.container.find( '.sgdg-more-button' ).remove();
			$( '.sgdg-gallery-container[data-sgdg-hash!=' + this.hash + ']' ).each( function() {
				reflow( $( this ) ); // TODO
			} );
			$.get( sgdgShortcodeLocalize.ajax_url, {
				action: 'gallery',
				hash: this.hash,
				path: this.path,
				page: this.lastPage,
			}, ( data: GalleryResponse ) => {
				if ( isError( data ) ) {
					this.container.html( data.error );
					return;
				}
				this.getSuccess( data );
			} );
		}

		private getSuccess( data: GallerySuccessResponse ): void {
			const pageLength = ( ( data.directories ? data.directories.length : 0 ) + ( data.images ? data.images.length : 0 ) + ( data.videos ? data.videos.length : 0 ) ) / this.lastPage;
			let html = '';
			let currentPage = 1;
			let remaining = pageLength;
			if ( ( data.path && 0 < data.path.length ) || 0 < data.directories.length ) {
				html += renderBreadcrumbs( this.shortHash, data.path );
			}
			if ( 0 < data.directories.length || 0 < data.images.length || 0 < data.videos.length ) {
				html += '<div class="sgdg-loading">' +
					'<div></div>' +
				'</div>' +
				'<div class="sgdg-gallery">';
				if ( data.directories ) {
					$.each( data.directories, ( _, directory ) => {
						html += renderDirectory( this.shortHash, directory );
						remaining--;
						if ( 0 === remaining ) {
							remaining = pageLength;
							currentPage++;
						}
					} );
				}
				if ( data.images ) {
					$.each( data.images, ( _, image ) => {
						html += renderImage( this.shortHash, currentPage, image );
						remaining--;
						if ( 0 === remaining ) {
							remaining = pageLength;
							currentPage++;
						}
					} );
				}
				if ( data.videos ) {
					$.each( data.videos, ( _, video ) => {
						if ( '' !== document.createElement( 'video' ).canPlayType( video.mimeType ) ) {
							html += renderVideo( this.shortHash, currentPage, video );
						}
						remaining--;
						if ( 0 === remaining ) {
							remaining = pageLength;
							currentPage++;
						}
					} );
				}
				html += '</div>';
				if ( data.more ) {
					html += renderMoreButton();
				}
			} else {
				html += '<div class="sgdg-gallery">' + sgdgShortcodeLocalize.empty_gallery + '</div>';
			}
			this.container.html( html );
			this.hasMore = data.more;
			this.postLoad();
			this.lightbox.openHistory();
		}

		private add(): void {
			this.lastPage += 1;
			this.container.find( '.sgdg-gallery' ).after( '<div class="sgdg-loading">' +
				'<div></div>' +
			'</div>' );
			this.container.find( '.sgdg-more-button' ).remove();
			$.get( sgdgShortcodeLocalize.ajax_url, {
				action: 'page',
				hash: this.hash,
				path: getQueryParameter( this.shortHash, 'path' ),
				page: this.lastPage,
			}, ( data: PageResponse ) => {
				if ( isError( data ) ) {
					this.container.find( '.sgdg-loading' ).replaceWith( data.error );
					this.container.find( '.sgdg-more-button' ).remove();
					return;
				}
				this.addSuccess( data );
			} );
		}

		private addSuccess( data: PageSuccessResponse ): void {
			let html = '';
			$.each( data.directories, ( _, directory ) => {
				html += renderDirectory( this.shortHash, directory );
			} );
			$.each( data.images, ( _, image ) => {
				html += renderImage( this.shortHash, this.lastPage, image );
			} );
			$.each( data.videos, ( _, video ) => {
				html += renderVideo( this.shortHash, this.lastPage, video );
			} );
			this.container.find( '.sgdg-gallery' ).append( html );
			this.hasMore = data.more;
			if ( data.more ) {
				this.container.append( renderMoreButton() );
			}
			this.container.find( '.sgdg-loading' ).remove();
			this.postLoad();
		}

		private postLoad(): void {
			this.container.find( 'a[data-sgdg-path]' ).off( 'click' ).click( () => {
				history.pushState( {}, '', addQueryParameter( this.shortHash, 'path', this.path ) );
				this.get(); // eslint-disable-line @typescript-eslint/no-use-before-define
				return false;
			} );
			this.container.find( '.sgdg-more-button' ).click( () => {
				this.add(); // eslint-disable-line @typescript-eslint/no-use-before-define
				return false;
			} );

			loading.push( this.hash );
			this.container.find( '.sgdg-gallery' ).imagesLoaded( { background: true }, () => {
				loading.splice( loading.indexOf( this.hash ), 1 );
				reflow( this.container );
				$( '.sgdg-gallery-container[data-sgdg-hash!=' + this.hash + ']' ).each( function() {
					reflow( $( this ) );
				} );
			} );
			reflowTimer( this.hash );

			this.lightbox.addToImageLightbox( this.container.find( 'a[data-imagelightbox]' ) );
			if ( 'true' === sgdgShortcodeLocalize.page_autoload ) {
				$( window ).off( 'scroll' ).scroll( ( event ) => {
					const el = $( '.sgdg-more-button' );
					if ( undefined === el.offset() ) {
						return;
					}
					const inView = $( event.currentTarget ).scrollTop()! + $( window ).height()! > el.offset()!.top + el.outerHeight()!;
					if ( inView && -1 === loading.indexOf( this.hash ) ) {
						this.add(); // eslint-disable-line @typescript-eslint/no-use-before-define
					}
				} );
			}
		}
	}

	class ShortcodeRegistry {
		private static shortcodes: Record<string, Shortcode> = {};

		public static init(): void {
			$( '.sgdg-gallery-container' ).each( function() {
				const container = $( this );
				const hash = container.data( 'sgdgHash' );
				ShortcodeRegistry.shortcodes[ hash.substr( 0, 8 ) ] = new Shortcode( container, hash );
			} );

			$( document ).on( 'start.ilb2 next.ilb2 previous.ilb2', ( _, e ) => ShortcodeRegistry.onLightboxNavigation( e ) );
			$( document ).on( 'quit.ilb2', () => ShortcodeRegistry.removePageFromHistory() );
		}

		private static onLightboxNavigation( e: JQuery ): void {
			const hash = $( e ).data( 'imagelightbox' );
			ShortcodeRegistry.shortcodes[ hash ].onLightboxNavigation( e );
		}

		private static removePageFromHistory(): void {
			history.replaceState( history.state, '', removeQueryParameter( '[^-]+', 'page' ) );
		}
	}

	ShortcodeRegistry.init();
} );
