// jQuery(document).ready(function($) {
//     // Function to check the state of the field and tab
//     function checkFieldState() {
//         var selectedValues = $('.wc-taxonomy-term-search').val() || [];
//         var taxonomy = $('.wc-taxonomy-term-search').data('taxonomy');

//         if (taxonomy === 'pa_buybyraffle-product-group') {
//             if ($.inArray("77", selectedValues) !== -1) {
//                 // Hide the tab when "Hero" is selected
//                 $('.custom_data_options').hide();
//             } else {
//                 // Show the tab for other selections
//                 $('.custom_data_options').show();
//             }
//         }
//     }

//     // Bind the function to the change event
//     $(document).on('change', '.wc-taxonomy-term-search', function() {
//         checkFieldState();
//     });

//     // Call the function on page load and after the page reloads
//     checkFieldState();

//     // Listen for changes in the WooCommerce product attributes panel
//     $(document.body).on('woocommerce_variations_loaded', function() {
//         // Delay the check to ensure the panel is fully loaded
//         setTimeout(function() {
//             checkFieldState();
//         }, 1000);
//     });

//     // Listen for attribute update button click
//     $(document.body).on('click', '.update_attributes', function() {
//         // Delay the check after the button click event
//         setTimeout(function() {
//             checkFieldState();
//         }, 10);
//     });
// });
// // this preents users from saving a product when they have made updates to the attribute field.
// jQuery(document).ready(function($) {
//     var attributeSaved = true; // Initialize to true because we assume no changes at first
//     var attributeChanged = false; // Initialize to false, will be set to true if attribute fields are changed

//     // Detect changes in any attribute field
//     $('.woocommerce_attribute').change(function() {
//         attributeChanged = true;
//         attributeSaved = false; // Attributes have been modified, therefore not saved
//     });

//     // Detect the "Save attributes" click
//     $('.save_attributes').click(function() {
//         attributeSaved = true; // Once saved, reset the flag
//         attributeChanged = false; // Reset this as well because attributes have been saved
//     });

//     // Validate on "Update" click
//     $('#publish').click(function(event) {
//         if (attributeChanged && !attributeSaved) {
//             event.preventDefault();
//             alert('Please save attributes before updating the product.');
//         }
//     });
// });

// /**
//  * Explanation:
//  * The attributeIsBait variable keeps track of whether the attribute has been set to 'bait'.
//  * The heroProductSelected variable keeps track of whether a Hero Product has been selected.
//  * When either of these fields is changed, the validateBeforeSave function is called. This function enables or disables the "Publish" button based on whether the requirements are met.
//  * Initial validation is performed by calling validateBeforeSave immediately, to handle the case where the page is loaded with the attribute already set to 'bait'.
//  */
// jQuery(document).ready(function($) {
//     console.log("Script Loaded");

//     let attributeChanged = false;

//     // Detect changes in the attribute field
//     $(document).on('change', '.woocommerce_attribute', function() {
//         console.log("Attribute Changed");
//         attributeChanged = true;
//         validateBeforeSave();
//     });

//     // Detect changes in the Hero Product dropdown
//     $(document).on('change', '#hero_product_id', function() {
//         console.log("Hero Product Changed");
//         validateBeforeSave();
//     });

//     /**
//      * polling is the only method that worked here, it likely means that the element is being 
//      * manipulated in such a way that traditional event delegation and observation 
//      * methods aren't effective. However, polling, while effective, is not the most 
//      * efficient way to handle such scenarios.
//      * 
//      * Polling can be resource-intensive if not done carefully. 
//      * A few point implemented to make polling more efficient are: 
//      * 
//      * Debounce the Polling: Instead of checking every second, I debounce to stop polling altogether once my conditions are met.
//      * Limit Scope: I maded sure my selector is as specific as possible to limit the area of the DOM that needs to be traversed.
//      * Clear Interval: Once I found the element, I used clearInterval() to stop the polling, releasing the resources.
//      * 
//      */
//     var intervalId;

//     function checkElement() {
//         if ($('.remove_row.delete').length) {
//             $('.remove_row.delete').on('click', function() {
//                 console.log('Attribute Removed');
//                 validateBeforeSave();
//             });
//             clearInterval(intervalId);  // Stop polling once element is found
//         }
//     }
    
//     intervalId = setInterval(checkElement, 1000); // Save interval ID for clearing later
    
    

//     function validateBeforeSave() {
//         console.log("Validating...");

//         let heroAttributeSelected = false;
        
//         // Check if any attribute fields are present
//         let attributeFieldsExist = $('.woocommerce_attribute').length > 0;
        
//         //alert(attributeFieldsExist)
//         $('.woocommerce_attribute').each(function() {
//             if ($(this).find('option:selected').text() === 'Hero') {
//                 heroAttributeSelected = true;
//                 return false; // Break the loop
//             }
//         });

//         if (attributeFieldsExist) {
//             if (attributeChanged && heroAttributeSelected) {
//                 $('#publish').prop('disabled', false);
//                 console.log("Validation Passed");
//             } else {
//                 var heroProductSelected = !!$('#hero_product_id').val();
//                 if (!heroProductSelected) {
//                     console.log("Validation Failed");
//                     $('#publish').prop('disabled', true);
//                     alert('Please remember to select a Hero Product from the tab before saving.');
//                 } else {
//                     console.log("Validation Passed");
//                     $('#publish').prop('disabled', false);
//                 }
//             }
//         } else {
//             console.log("No attribute fields. Validation Passed");
//             $('#publish').prop('disabled', false);
//         }
//     }

//     // Initial validation
//     validateBeforeSave();
// });



/**
 * This script handles the visibility of custom data options based on the selected taxonomy term.
 */
jQuery(document).ready(function($) {
    /**
     * Function to check the state of the field and tab.
     */
    function checkFieldState() {
        var selectedValues = $('.wc-taxonomy-term-search').val() || [];
        var taxonomy = $('.wc-taxonomy-term-search').data('taxonomy');

        // Check for specific taxonomy
        if (taxonomy === 'pa_buybyraffle-product-group') {
            // Hide or show the custom_data_options tab based on the selected value
            if ($.inArray("77", selectedValues) !== -1) {
                $('.custom_data_options').hide();
            } else {
                $('.custom_data_options').show();
            }
        }
    }

    // Bind the function to the change event of the taxonomy term search field
    $(document).on('change', '.wc-taxonomy-term-search', function() {
        checkFieldState();
    });

    // Call the function on page load and after the page reloads
    checkFieldState();

    // Listen for changes in the WooCommerce product attributes panel
    $(document.body).on('woocommerce_variations_loaded', function() {
        setTimeout(function() {
            checkFieldState();
        }, 1000);
    });

    // Listen for attribute update button click
    $(document.body).on('click', '.update_attributes', function() {
        setTimeout(function() {
            checkFieldState();
        }, 10);
    });
});

/**
 * This script prevents users from saving a product when they have made updates to the attribute field without saving them.
 */
jQuery(document).ready(function($) {
    var attributeSaved = true;
    var attributeChanged = false;

    // Detect changes in any attribute field
    $('.woocommerce_attribute').change(function() {
        attributeChanged = true;
        attributeSaved = false;
    });

    // Detect the "Save attributes" click
    $('.save_attributes').click(function() {
        attributeSaved = true;
        attributeChanged = false;
    });

    // Validate on "Update" click
    $('#publish').click(function(event) {
        if (attributeChanged && !attributeSaved) {
            event.preventDefault();
            alert('Please save attributes before updating the product.');
        }
    });
});

/**
 * This script handles the validation before saving a product, based on the selected attribute and Hero Product.
 */
jQuery(document).ready(function($) {
    console.log("Script Loaded");

    let attributeChanged = false;

    // Detect changes in the attribute field
    $(document).on('change', '.woocommerce_attribute', function() {
        console.log("Attribute Changed");
        attributeChanged = true;
        validateBeforeSave();
    });

    // Detect changes in the Hero Product dropdown
    $(document).on('change', '#hero_product_id', function() {
        console.log("Hero Product Changed");
        validateBeforeSave();
    });

    // Polling to detect the removal of an attribute
    var intervalId;
    function checkElement() {
        if ($('.remove_row.delete').length) {
            $('.remove_row.delete').on('click', function() {
                console.log('Attribute Removed');
                validateBeforeSave();
            });
            clearInterval(intervalId);
        }
    }
    intervalId = setInterval(checkElement, 1000);

    
    /**
     * Function to validate before saving the product.
     */
    function validateBeforeSave() {
        console.log("Validating...");

        let heroAttributeSelected = false;
        
        // Check if any attribute fields are present
        let attributeFieldsExist = $('.woocommerce_attribute').length > 0;
        
        //alert(attributeFieldsExist)
        $('.woocommerce_attribute').each(function() {
            if ($(this).find('option:selected').text() === 'Hero') {
                heroAttributeSelected = true;
                return false; // Break the loop
            }
        });

        if (attributeFieldsExist) {
            if (attributeChanged && heroAttributeSelected) {
                $('#publish').prop('disabled', false);
                console.log("Validation Passed");
            } else {
                var heroProductSelected = !!$('#hero_product_id').val();
                if (!heroProductSelected) {
                    console.log("Validation Failed");
                    $('#publish').prop('disabled', true);
                    alert('Please remember to select a Hero Product from the tab before saving.');
                } else {
                    console.log("Validation Passed");
                    $('#publish').prop('disabled', false);
                }
            }
        } else {
            console.log("No attribute fields. Validation Passed");
            $('#publish').prop('disabled', false);
        }
    }

    // Initial validation
    validateBeforeSave();
});

