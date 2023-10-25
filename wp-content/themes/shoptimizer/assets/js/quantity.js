// Quantity buttons on the single product and cart pages.
;
( function( $ ) {
	'use strict';

	$( window ).on( 'load', function() {
		'use strict';

		shoptimizerWooQuantityButtons();
	} );

	$( document ).ajaxComplete( function() {
		'use strict';

		shoptimizerWooQuantityButtons();
	} );

	/**
	* WooCommerce quantity buttons
	* @param {number} $quantitySelector Quantity.
	*/
	function shoptimizerWooQuantityButtons( $quantitySelector ) {

		var $quantityBoxes;
		if ( ! $quantitySelector ) {
			$quantitySelector = '.qty';
		}

		$quantityBoxes = $( '.woocommerce-cart-form div.quantity:not(.buttons_added), .summary div.quantity:not(.buttons_added)' ).find( $quantitySelector );
		if ( $quantityBoxes && 'date' !== $quantityBoxes.prop( 'type' ) && 'hidden' !== $quantityBoxes.prop( 'type' ) ) {

			// Add plus and minus icons
			$quantityBoxes.parent().addClass( 'buttons_added' );
			$quantityBoxes.after( '<div class="quantity-nav"><a href="javascript:void(0)" class="quantity-button quantity-up plus">&nbsp;</a><a href="javascript:void(0)" class="quantity-button quantity-down minus">&nbsp;</a></div>' );

			// Target quantity inputs on product pages
			$( 'input' + $quantitySelector + ':not(.product-quantity input' + $quantitySelector + ')' ).each( function() {
					var $min = parseFloat( $( this ).attr( 'min' ) );

					if ( $min && 0 < $min && parseFloat( $( this ).val() ) < $min ) {
						$( this ).val( $min );
					}
			} );

			$( '.plus, .minus' ).unbind( 'click' );
			$( '.plus, .minus' ).on( 'click', function() {

					// Get values
					var $quantityBox     = $( this ).closest( '.quantity' ).find( $quantitySelector ),
						$currentQuantity = parseFloat( $quantityBox.val() ),
						$maxQuantity     = parseFloat( $quantityBox.attr( 'max' ) ),
						$minQuantity     = parseFloat( $quantityBox.attr( 'min' ) ),
						$step            = $quantityBox.attr( 'step' );

					// Fallback default values
					if ( ! $currentQuantity || '' === $currentQuantity  || 'NaN' === $currentQuantity ) {
						$currentQuantity = 0;
					}
					if ( '' === $maxQuantity || 'NaN' === $maxQuantity ) {
						$maxQuantity = '';
					}

					if ( '' === $minQuantity || 'NaN' === $minQuantity ) {
						$minQuantity = 0;
					}
					if ( 'any' === $step || '' === $step  || undefined === $step || 'NaN' === parseFloat( $step )  ) {
						$step = 1;
					}

					// Change the value
					if ( $( this ).is( '.plus' ) ) {
						if ( $maxQuantity && ( $maxQuantity === $currentQuantity || $currentQuantity > $maxQuantity ) ) {
							$quantityBox.val( $maxQuantity );
						} else {
							$quantityBox.val( $currentQuantity + parseFloat( $step ) );
						}

					} else {
						if ( $minQuantity && ( $minQuantity === $currentQuantity || $currentQuantity < $minQuantity ) ) {
							$quantityBox.val( $minQuantity );
						} else if ( 0 < $currentQuantity ) {
							$quantityBox.val( $currentQuantity - parseFloat( $step ) );
						}
					}

					// Trigger change event.
					$quantityBox.trigger( 'change' );
				}
			);
		}
	}


}( jQuery ) );
