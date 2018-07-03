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
	reflow();

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
