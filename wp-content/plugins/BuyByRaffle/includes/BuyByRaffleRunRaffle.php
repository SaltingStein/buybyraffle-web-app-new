<?php
function BuyByRaffleRunRaffle($raffle_cycle_id) {
    $base_url = 'https://example.com/external-api'; // Raffle Run with your external API URL
    $username = getenv('RAFFLE_AUTH_USERNAME');
    $password = getenv('RAFFLE_AUTH_PASSWORD');

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
