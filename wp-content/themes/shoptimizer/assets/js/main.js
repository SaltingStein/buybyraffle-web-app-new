// Main Shoptimizer js.
;
( function( $ ) {
	'use strict';

	window.addEventListener( 'load', () => {

		// First we get the viewport height and we multiple it by 1% to get a value for a vh unit
		let vh = window.innerHeight * 0.01;

		// Then we set the value in the --vh custom property to the root of the document
		document.documentElement.style.setProperty( '--vh', `${vh}px` );
	} );

	window.addEventListener( 'resize', () => {
		let vh = window.innerHeight * 0.01;
		document.documentElement.style.setProperty( '--vh', `${vh}px` );
	} );

	$( window ).on( 'load resize', function() {

		if ( 992 < $( window ).width() ) {

		// Make a standard hover menu work on touchscreens. Activate only on devices with a touch screen.
		if ( 'ontouchstart' in window ) {

			// Make the touch event add hover pseudoclass.
			document.addEventListener( 'touchstart', function() {}, true );

			// Modify click event.
			document.addEventListener( 'click', function( e ) {
				var el = $( e.target ).hasClass( 'menu-item-has-children' ) ? $( e.target ) : $( e.target ).closest( '.menu-item-has-children' );
				if ( ! el.length ) {
					return;
				}

				// Remove tapped class from old ones.
				$( '.menu-item-has-children.tapped' ).each( function() {
					if ( this !== el.get( 0 ) ) {
					$( this ).removeClass( 'tapped' );
					}
				} );
				if ( ! el.hasClass( 'tapped' ) ) {

					// First Tap.
					el.addClass( 'tapped' );
					e.preventDefault();
					return false;
				} else {

					// Second Tap.
					return true;
				}
			}, true );
		}
	}
	} );


	// Add custom id to the single product form.
	$( document ).ready( function() {
		$( '.single-product form.cart' ).attr( 'id', 'sticky-scroll' );
	} );

	// Toggle cart drawer.
	$( document ).on( 'click', '.mobile-filter', function( e ) {
		e.stopPropagation();
		e.preventDefault();
		$( 'body' ).toggleClass( 'filter-open' );
	} );

	// Close drawer - click the x icon.
	$( '.close-drawer' ).on( 'click', function() {
		$( 'body' ).removeClass( 'filter-open' );
	} );

	$( document ).ready( function() {
		var $loading = $( '#ajax-loading' ).hide();
		$( document ).ajaxStart( function() {
			$loading.show();
		} )
		.ajaxStop( function() {
			$loading.hide();
		} );
	} );


	// Reposition size guide link on the single product template.
	$( '.button-wrapper' ).addClass( 'shoptimizer-size-guide' );

	// Add a class if term description text or an image exists.
	if ( 0 < $( '.term-description' ).length ) {
		$( '.woocommerce-products-header' ).addClass( 'description-exists' );
	}

	if ( 0 < $( '.woocommerce-products-header img' ).length ) {
		$( '.woocommerce-products-header' ).addClass( 'image-exists' );
	}

	// Overlay when a full width menu item is hovered over.
	$( document ).ready( function() {
		$( 'li.full-width' ).hover( function() {
			$( '.site-content' ).addClass( 'overlay' );
		},
		function() {
			$( '.site-content' ).removeClass( 'overlay' );
		} );
	} );

	// Toggle menu.
	$( '.menu-toggle' ).on( 'touchstart click', function( e ) {
		e.stopPropagation();
		e.preventDefault();
		$( 'body' ).toggleClass( 'mobile-toggled' );
	} );

	// Close mobile menu - click the x icon.
	$( '.close-drawer' ).on( 'click', function() {
		$( 'body' ).removeClass( 'mobile-toggled' );
	} );

	// Close the mobile menu when clicking/tapping outside it.
	$( document ).on( 'touchstart click', function( e ) {
		var mobileContainer = $( '.mobile-toggled .col-full-nav' );
		if ( ! mobileContainer.is( e.target ) && 0 === mobileContainer.has( e.target ).length ) {
			$( 'body' ).removeClass( 'mobile-toggled' );
		}
	} );

	// Close mobile menu when an add to cart button is clicked within it. The sidebar cart opens.
	$( '.add_to_cart_button' ).on( 'click', function() {
		$( 'body' ).removeClass( 'mobile-toggled' );
	} );

	// Reveal/Hide Mobile sub menus.
	$( 'body .main-navigation ul.menu li.menu-item-has-children .caret' ).on( 'click', function( e ) {
		$( this ).closest( 'li' ).toggleClass( 'dropdown-open' );
		e.preventDefault();
	} );

	// Reveal sub menus if the top level parent link is a hash
	$( '.main-navigation ul.menu li.menu-item-has-children > a' ).each( function() {
		var nav = $( this );
		if ( 0 < nav.length ) {
			if ( '#' === nav.attr( 'href' ) ) {
				$( this ).click(
					function( e ) {
						$( this ).closest( 'li' ).toggleClass( 'dropdown-open' );
						e.preventDefault();
					}
				);
			}
		}
	} );


	// Scroll to top.
	$( '.logo-mark a' ).click( function() {
		$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
		return false;
	} );

	// Smooth scroll for sticky single products - for variable, bundle, composite and grouped items
	$( 'a.variable-grouped-sticky[href*="#"]' ).on( 'click', function( e ) {
		e.preventDefault();

		$( 'html, body' ).animate( {
			scrollTop: $( $( this ).attr( 'href' ) ).offset().top - 80}, 500, 'linear' );
	} );

	document.addEventListener( 'DOMContentLoaded', function() {
	let lazyImages = [].slice.call( document.querySelectorAll( 'img.lazy' ) );
	let active = false;

	const lazyLoad = function() {
		if ( false === active ) {
			active = true;

			setTimeout( function() {
				lazyImages.forEach( function( lazyImage ) {
					if ( ( lazyImage.getBoundingClientRect().top <= window.innerHeight && 0 <= lazyImage.getBoundingClientRect().bottom ) && 'none' !== getComputedStyle( lazyImage ).display ) {
						lazyImage.src = lazyImage.dataset.src;
						lazyImage.srcset = lazyImage.dataset.srcset;
						lazyImage.classList.remove( 'lazy' );

						lazyImages = lazyImages.filter( function( image ) {
							return image !== lazyImage;
						} );

						if ( 0 === lazyImages.length ) {
							document.removeEventListener( 'scroll', lazyLoad );
							window.removeEventListener( 'resize', lazyLoad );
							window.removeEventListener( 'orientationchange', lazyLoad );
						}
					}
				} );

				active = false;
			}, 200 );
		}
	};

	document.addEventListener( 'scroll', lazyLoad );
	window.addEventListener( 'resize', lazyLoad );
	window.addEventListener( 'orientationchange', lazyLoad );
} );


}( jQuery ) );
