$( '.bs-pt-item' ).on( 'click', function ( e ) {
	e.preventDefault();
	window.location.href = $( this ).find( 'a.bs-pt-link' ).attr( 'href' );
	return false;
} );
