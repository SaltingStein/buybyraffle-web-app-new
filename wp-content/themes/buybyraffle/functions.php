<?php 

add_action('wp_enqueue_scripts', 'buybyraffle_enqueue_styles');
function buybyraffle_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css'); 
}
function normalizeNigerianPhoneNumber($phoneNumber) {
    // Define allowed Nigerian phone number prefixes
    $allowedPrefixes = [
        '0703', '0706', '0803', '0806', '0810', '0813', '0814', '0816', '0903', '0906', '0913', '0916', // MTN
        '07025', '07026', '0704', // MTN (Visafone)
        '0809', '0817', '0818', '0909', '0908', // 9Mobile
        '0701', '0708', '0802', '0808', '0812', '0901', '0902', '0904', '0907', '0912', '0911', // Airtel
        '0705', '0805', '0807', '0811', '0815', '0905', '0915', // Globacom
        '07027', '0709', // Multi-Links
        '0804', // Ntel
        '07020', // Smile
        '07028', '07029', '0819', // Starcomms
        '0707', // ZoomMobile
    ];

    // Remove any non-digit character
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Check for numbers with international prefix +234 or 234
    if (strpos($phoneNumber, '234') === 0) {
        $phoneNumber = '0' . substr($phoneNumber, 3);
    }

    // Check if the phone number is valid (11 digits long)
    if (strlen($phoneNumber) !== 11) {
        return 'Invalid number'; // Or handle the error as needed
    }

    // Check if the prefix is allowed
    $prefix = substr($phoneNumber, 0, 4);
    if (!in_array($prefix, $allowedPrefixes)) {
        return false; // Or handle the error as needed
    }

    return $phoneNumber;
}

