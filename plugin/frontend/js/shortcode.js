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

	function removeQueryField( key ) {
		var newQuery = window.location.search;
		var keyRegex1 = new RegExp( '[?]' + key + '=[^&]*' );
		var keyRegex2 = new RegExp( '&' + key + '=[^&]*' );
		if ( newQuery ) {
			newQuery = newQuery.replace( keyRegex1, '?' );
			newQuery = newQuery.replace( keyRegex2, '' );
		}
		return newQuery;
}

	function renderBreadcrumbs( path ) {
		var html = '<div><a href="' + removeQueryField( 'sgdg-path' ) + '">' + sgdgShortcodeLocalize.breadcrumbs_top + '</a>';
		var field = '';
		$.each( path, function( _, crumb ) {
			field += crumb.id + '/';
			html += ' > <a href="' + addQueryField( 'sgdg-path', field.slice( 0, -1 ) ) + '">' + crumb.name + '</a>';
		});
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
			if ( false !== dir.thumbnail ) {
				html += ' style="background-image: url(\'' + dir.thumbnail + '\');">';
			} else {
				html += '><svg class="sgdg-dir-icon" x="0px" y="0px" focusable="false" viewBox="0 0 24 24" fill="#8f8f8f"><path d="M10 4H4c-1.1 0-2 .9-2 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"></path></svg>';
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

	$.get( sgdgShortcodeLocalize.ajax_url, {
		action: 'list_dir',
		nonce: $( '#sgdg-gallery-container' ).data( 'sgdgNonce' ),
		path: getQueryField( 'sgdg-path' )
	}, function( data ) {
		var html = renderBreadcrumbs( data.path );
		html += '<div id="sgdg-gallery">';
		html += renderDirectories( data.directories );
		html += renderImages( data.images );
		html += '</div>';
		$( '#sgdg-gallery-container' ).html( html );
		$( '#sgdg-gallery' ).imagesLoaded( reflow );
		reflow();
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
