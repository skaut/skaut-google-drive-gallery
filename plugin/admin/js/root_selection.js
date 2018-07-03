'use strict';
jQuery( document ).ready( function( $ ) {
	function listGdriveDir( path ) {
		$( '#sgdg_root_selection_body' ).html( '' );
		$( '#submit' ).attr( 'disabled', 'disabled' );
		$.get( sgdgRootpathLocalize.ajax_url, {
			_ajax_nonce: sgdgRootpathLocalize.nonce, // eslint-disable-line camelcase
			action: 'list_gdrive_dir',
			path: path
			}, function( data ) {
				var i;
				var html = '';
				var len = data.contents.length;
				if ( 0 < path.length ) {
					html += '<tr><td class="row-title"><label>..</label></td></tr>';
				}
				for ( i = 0; i < len; i++ ) {
					html += '<tr class="';
					if ( ( 0 === path.length && 1 === i % 2 ) || ( 0 < path.length && 0 === i % 2 ) ) {
						html += 'alternate';
					}
					html += '"><td class="row-title"><label data-id="' + data.contents[i].id + '">' + data.contents[i].name + '</label></td></tr>';
				}
				$( '#sgdg_root_selection_body' ).html( html );
				html = '';
				if ( 0 === path.length ) {
					html = sgdgRootpathLocalize.team_drive_list;
				} else {
					$( '#submit' ).removeAttr( 'disabled' );
				}
				len = path.length;
				for ( i = 0; i < len; i++ ) {
					if ( 0 < i ) {
						html += ' > ';
					}
					html += data.path[i];
				}
				$( '.sgdg_root_selection_path' ).html( html );
				$( '#sgdg_root_selection_body label' ).click( function() {
					var newId = $( this ).attr( 'data-id' );
					if ( newId ) {
						path.push( newId );
					} else {
						path.pop();
					}
					listGdriveDir( path );
				});
				$( '#sgdg_root_path' ).val( JSON.stringify( path ) );
			}
		);
	}

	listGdriveDir( sgdgRootpathLocalize.root_dir );
});
