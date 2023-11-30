<?php
//Create ticket in external environment
class BuyByRaffleSendTicketExternalAPIHandler {

    private $base_url;
    private $username;
    private $password;

    public function __construct($base_url, $username, $password) {
        $this->base_url = $base_url;
        $this->username = $username;
        $this->password = $password;
    }

    public function sendData($order_id, $raffle_cycle_id, $customer_id, $raffle_class_id) {
        // Determine draw type based on raffle_class_id
        $draw_type = ($raffle_class_id == 1) ? 'Bait' : ($raffle_class_id == 3 ? 'Solo' : '');

        // Check if draw_type is determined
        if (empty($draw_type)) {
            return; // Invalid raffle_class_id
        }
        if(function_exists(generate_random_pin())){
            // Prepare data to send
            $ticketId = get_post_meta($order_id, 'bbr_ticket_id', true);
            $data = json_encode(array(
                'ticket_id' => $ticketId,
                'raffle_cycle_id' => $raffle_cycle_id,
                'user_id' => $customer_id,
                'draw_type' => $draw_type
            ));
        }else{
            $admin_email = get_option('admin_email');

            // Set the email subject and message
            $subject = 'Ticket ID Generation Failure Notification';
            $message = 'The function generate_random_pin() has encountered a failure. Please investigate.';
    
            // Send email to the admin
           return wp_mail($admin_email, $subject, $message);

        }   

        //Set up HTTP headers
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password),
        );

        // Set up the HTTP request
        $args = array(
            'body'    => $data,
            'headers' => $headers,
        );

        // Make the request
        $response = wp_remote_post($this->base_url, $args);

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
}