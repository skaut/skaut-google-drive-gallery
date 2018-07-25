'use strict';
jQuery( document ).ready( function( $ ) {
	var reflow = function() {
		var val, bbox, positions, sizes, containerPosition;
		var j = 0;
		var loaded = [];
		var ratios = [];
		$( '#sgdg-gallery' ).children().each( function( i ) {
			$( this ).css( 'position', 'initial' );
			$( this ).css( 'display', 'inline-block' );
			val = this.getBoundingClientRect().width / this.getBoundingClientRect().height;
			if ( 0 < $( this ).find( 'svg' ).length ) {
				bbox = $( this ).find( 'svg' )[0].getBBox();
				val = bbox.width / bbox.height;
			}
			if ( $( this ).hasClass( 'sgdg-grid-square' ) ) {
				val = 1;
			}
			if ( isNaN( val ) ) {
				loaded[i] = false;
			} else {
				loaded[i] = true;
				ratios.push( val );
			}
			$( this ).css( 'position', 'absolute' );
		});
		positions = require( 'justified-layout' )( ratios, {
			containerWidth: $( '#sgdg-gallery' ).width(),
			boxSpacing: parseInt( sgdgShortcodeLocalize.grid_spacing ),
			targetRowHeight: parseInt( sgdgShortcodeLocalize.grid_height )
		});
		$( '#sgdg-gallery' ).children().each( function( i ) {
			if ( ! loaded[i]) {
				$( this ).css( 'display', 'none' );
				return;
			}
			sizes = positions.boxes[j];
			j++;
			containerPosition = $( '#sgdg-gallery' ).position();
			$( this ).css( 'top', sizes.top + containerPosition.top );
			$( this ).css( 'left', sizes.left + containerPosition.left );
			$( this ).width( sizes.width );
			$( this ).height( sizes.height );
		});
		$( '#sgdg-gallery' ).height( positions.containerHeight );
	};
	$( window ).resize( reflow );
	$( '#sgdg-gallery' ).imagesLoaded( reflow );

	function getQueryField( key ) {
		var keyValuePair = new RegExp( '[?&]' + key + '(=([^&#]*)|&|#|$)' ).exec( document.location.search );
		if ( ! keyValuePair || ! keyValuePair[2]) {
			return undefined;
		}
		return decodeURIComponent( keyValuePair[2].replace( /\+/g, ' ' ) );
	}

	function addQueryField( key, value ) {
		var query = window.location.search;
		var newField = key + '=' + value;
		var newQuery = '?' + newField;
		var keyRegex = new RegExp( '([?&])' + key + '=[^&]*' );

		if ( query ) {
			if ( null !== query.match( keyRegex ) ) {
				newQuery = query.replace( keyRegex, '$1' + newField );
			} else {
				newQuery = query + '&' + newField;
			}
		}
		return newQuery;
		}

	function renderBreadcrumbs( path ) {
		var html = '<div><a href="' + window.location + '">' + sgdgShortcodeLocalize.breadcrumbs_top + '</a>'; // TODO: href
		// TODO: Breadcrumbs
		html += '</div>';
		return html;
	}

	function renderDirectories( directories ) {
		var html = '';
		$.each( directories, function( _, dir ) {
			var currentPath = getQueryField( 'sgdg-path' );
			html += '<a class="sgdg-grid-a sgdg-grid-square" href="';
			html += addQueryField( 'sgdg-path', ( currentPath ? currentPath + '/' : '' ) + dir.id );
			html += '"';
			if ( true ) { // TODO: SVG icon
				html += ' style="background-image: url(\'' + dir.thumbnail + '\');">';
			}
			html += '<div class="sgdg-dir-overlay"><div class="sgdg-dir-name">' + dir.name + '</div>';
			if ( dir.dircount ) {
				html += dir.dircount;
			}
			if ( dir.imagecount ) {
				html += dir.imagecount;
			}
			html += '</div></a>';
		});
		return html;
	}

	function renderImages( images ) {
		var html = '';
		$.each( images, function( _, image ) {
			html += '<a class="sgdg-grid-a" data-imagelightbox="a"';
			html += 'data-ilb2-id="' + image.id + '"';
			html += ' href="' + image.image + '"><img class="sgdg-grid-img" src="' + image.thumbnail + '"></a>';
		});
		return html;
	}

	$( '#sgdg-gallery-container' ).each( function( i ) {
		$.get( sgdgShortcodeLocalize.ajax_url, {
			action: 'list_dir',
			nonce: $( this ).data( 'sgdgNonce' ),
			path: getQueryField( 'sgdg-path' )
		}, function( data ) {
			var html = renderBreadcrumbs( data.path );
			html += '<div id="sgdg-gallery">';
			html += renderDirectories( data.directories );
			html += renderImages( data.images );
			html += '</div>';
			$( '#sgdg-gallery-container' ).html( html );
			reflow();
		});
	});

	$( 'a[data-imagelightbox]' ).imageLightbox({
		allowedTypes: '',
		animationSpeed: parseInt( sgdgShortcodeLocalize.preview_speed, 10 ),
		activity: ( 'true' === sgdgShortcodeLocalize.preview_activity ),
		arrows: ( 'true' === sgdgShortcodeLocalize.preview_arrows ),
		button: ( 'true' === sgdgShortcodeLocalize.preview_closebutton ),
		fullscreen: true,
		history: false,
		overlay: true,
		quitOnEnd: ( 'true' === sgdgShortcodeLocalize.preview_quitOnEnd )
	});

});
