<?php



require_once plugin_dir_path(__FILE__) . '../../../BuyByRaffleEnvConfig/app.php';


function BuyByRaffleRunRaffle($raffle_cycle_id) {
    global $configurations;

    // Now use $configurations array to access your configuration values
    // For example, if you have an API URL in your config:
    $apiUrl = $configurations['API_URL'] ?? null;

    // Ensure that the required configuration is present
    if ($apiUrl === null) {
        error_log("API URL not set in configuration.");
        return;
    }

    
    // Extract the values
    $base_url = $configurations['API_URL'] ?? null; // Using null coalescing operator to handle missing values
    $username = $configurations['EMAIL'] ?? null;
    $password = $configurations['AUTH_PASSWORD'] ?? null;

    // Check if all required config variables are present
    if ($base_url === null || $username === null || $password === null) {
        // Handle error - missing configuration values
        error_log("Missing required configuration values in configurations");
        return;
    }

    // Prepare data to send
    $data = json_encode(array(
        'raffle_cycle_id' => $raffle_cycle_id,
    ));

    // Set up HTTP headers
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($username . ':' . $password),
    );

    // Set up the HTTP request
    $args = array(
        'body'    => $data,
        'headers' => $headers,
    );

    // Make the request
    $response = wp_remote_post($base_url, $args);

    // Check if the request was successful
    if (is_wp_error($response)) {
        error_log('Error communicating with the external API: ' . $response->get_error_message());
    } else {
        // Optionally handle the response from the external API
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        // Handle the response as needed
    }
}
