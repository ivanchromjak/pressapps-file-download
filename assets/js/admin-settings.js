jQuery( document ).ready( function( $ ) {
	
	// Setup move up/down arrows
	$( '.pafd-move_up,.pafd-move_down', '#pafd_setting_columns' ).click( function( e ) {

		e.preventDefault();

		var $this = $( this );
		var $movable = $this.closest( '.pafd-movable' );
		var index;

		index = Math.floor( $( '.pafd-move_up,.pafd-move_down', '#pafd_setting_columns' ).index( $this ) / 2 );

		if( $this.hasClass( 'pafd-move_up' ) ) {
			$movable.insertBefore( $movable.prev( '.pafd-movable' ) );
		} else {
			$movable.insertAfter( $movable.next( '.pafd-movable' ) );
		}
	} );

	// Add/remove button-primary class to/from checked/unchecked checkboxes
	$( ':checkbox', '.pafd-movable' ).change( function() {

		var $this = $( this );
		var $movable = $this.closest( '.pafd-movable' );

		if( $this.is( ':checked' ) ) {
			$movable.addClass( 'button-primary' );
		} else {
			$movable.removeClass( 'button-primary' );
		}
	} ).change();

	// Add/remove button-primary class to/from checked/unchecked checkboxes
	$( ':radio', '.pafd-movable' ).change( function() {

		var $this = $( this );
		var $movable = $this.closest( '.pafd-movable' );
		var $movables = $movable.siblings( '.pafd-movable' )

		if( $this.attr( 'checked' )  || $this.is( ':checked' ) ) {
			$movables.removeClass( 'button-primary' );
			$movable.addClass( 'button-primary' );
		}
	} ).change();
} );
