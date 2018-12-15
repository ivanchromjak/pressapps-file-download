jQuery( document ).ready( function($) {

	// Add upload support to main form
	$( 'form#post' ).attr( 'enctype', 'multipart/form-data' );

	// Fake increment of the download count
	$( '.pafd-download-link').click( function( e ) {
		var $this = $( this );
		var $counter = $this.parent().find( '.pafd-download-counter' );
		$counter.text( 1 + parseInt( $counter.text() ) );
	} );

	// Confirm revision deletion
	$( '.pafd-delete-link' ).click( function( e ) {
		var $this = $( this );
		if( window.confirm( pafd_admin_file_post.confirm_revision_deletion ) ) {
			// Confirmed
			;
		} else {
			// Not confirmed
			e.preventDefault();
		}
	} );

	$( '#pafd-file' ).change( function() {

		var $this = $( this );
		var $file_name = $( '#pafd-file-name' );
		var $seleceted_file_name = $( this ).val();

		if( $seleceted_file_name ) {
			$file_name
				.html( pafd_admin_file_post.selected_file + ': <strong>' + $seleceted_file_name + '</strong> (<a href="#" id="pafd-file-remove" >' + pafd_admin_file_post.remove + '</a>)' )
				.show( 'fast' )
			;
		} else {
			$file_name
				.text( '' )
				.hide( 'fast' )
			;
		}
	} );

	$( document ).on( 'click', '#pafd-file-remove', function( e ) {

		console.log( 'removing file' );

		e.preventDefault();
		$( '#pafd-file' )
			.val( '' )
			.change()
		;
	} );
} );
