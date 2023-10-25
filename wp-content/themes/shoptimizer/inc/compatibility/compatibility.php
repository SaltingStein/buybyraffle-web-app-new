<?php

function shoptimizer_compatability_styles() {

	$compatibility_uri = get_template_directory_uri() . '/inc/compatibility';

	if ( class_exists( 'WeDevs_Dokan' ) ) {
		wp_enqueue_style( 'shoptimizer-dokan-style', $compatibility_uri . '/dokan/dokan.css' );
	}

}
add_action( 'wp_enqueue_scripts', 'shoptimizer_compatability_styles', 90 );

// Dokan support.
if ( class_exists( 'WeDevs_Dokan' ) ) {
	require get_template_directory() . '/inc/compatibility/dokan/dokan.php';
}