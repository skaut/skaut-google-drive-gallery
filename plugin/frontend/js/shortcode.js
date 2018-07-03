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
			boxSpacing: parseInt( sgdg_shortcode_localize.grid_spacing ),
			targetRowHeight: parseInt( sgdg_shortcode_localize.grid_height )
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
		animationSpeed: parseInt( sgdg_shortcode_localize.preview_speed, 10 ),
		activity: ( 'true' === sgdg_shortcode_localize.preview_activity ),
		arrows: ( 'true' === sgdg_shortcode_localize.preview_arrows ),
		button: ( 'true' === sgdg_shortcode_localize.preview_closebutton ),
		fullscreen: true,
		history: true,
		overlay: true,
		quitOnEnd: ( 'true' === sgdg_shortcode_localize.preview_quitOnEnd )
	});

});
