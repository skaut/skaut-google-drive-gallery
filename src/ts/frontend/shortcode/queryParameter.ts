/* exported getQueryParameter, addQueryParameter */

function getQueryParameter( hash: string, name: string ): string {
	const keyValuePair = new RegExp( '[?&]sgdg-' + name + '-' + hash + '=(([^&#]*)|&|#|$)' ).exec( document.location.search );
	if ( ! keyValuePair || ! keyValuePair[ 2 ] ) {
		return '';
	}
	return decodeURIComponent( keyValuePair[ 2 ].replace( /\+/g, ' ' ) );
}

function removeQueryParameter( hash: string, name: string ): string {
	let newQuery = window.location.search;
	const keyRegex1 = new RegExp( '\\?sgdg-' + name + '-' + hash + '=[^&]*' );
	const keyRegex2 = new RegExp( '&sgdg-' + name + '-' + hash + '=[^&]*' );
	if ( newQuery ) {
		newQuery = newQuery.replace( keyRegex1, '?' );
		newQuery = newQuery.replace( keyRegex2, '' );
	}
	return window.location.pathname + newQuery;
}

function addQueryParameter( hash: string, name: string, value: string ): string {
	const query = window.location.search;
	const newField = 'sgdg-' + name + '-' + hash + '=' + value;
	let newQuery = '?' + newField;
	const keyRegex = new RegExp( '([?&])sgdg-' + name + '-' + hash + '=[^&]*' );
	if ( ! value ) {
		return removeQueryParameter( hash, name );
	}

	if ( query ) {
		if ( null !== keyRegex.exec( query ) ) {
			newQuery = query.replace( keyRegex, '$1' + newField );
		} else {
			newQuery = query + '&' + newField;
		}
	}
	return window.location.pathname + newQuery;
}
