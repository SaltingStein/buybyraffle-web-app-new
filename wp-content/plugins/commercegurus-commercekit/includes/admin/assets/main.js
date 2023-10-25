// xeroshoes js source
;
( function( $ ) {
    "use strict";

    $(document).ready(function(){

	    // Block Name: Popup for size guide
	    if ( $( "#shiphero-order-meta" ).length ) {
	    	console.log('pingy');
	    	$('#shiphero-order-meta,#shiphero-returns-meta').wrapAll('<div class="cg-shiphero-meta-container"></div>');
	    }

    });

    // Close anon function.
}( jQuery ) );