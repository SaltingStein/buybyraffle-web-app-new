<?php

/**
 * Class CashTokenGiftingAPI
 * 
 * Handles the CashToken Gifting REST API endpoint.
 */
class CashTokenGiftingAPI{
    /**
     * Constructor.
     * 
     * Adds the action to initialize the REST API endpoint.
     */
    public function __construct(){
        add_action('rest_api_init', [$this, 'init_rest_api']);
    }

    /**
     * Initializes the REST API endpoint.
     * 
     * Registers the route '/cashtoken/v2/gifting' for POST method.
     */
    public function init_rest_api(){
        register_rest_route('cashtoken/v2', '/gifting', array(
            'methods' => 'POST',
            'callback' => [$this, 'handleGifting'],
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Handles the gifting process.
     * 
     * This method is the callback for the REST route. It processes the request
     * and returns appropriate HTTP status codes based on the order status.
     *
     * @param WP_REST_Request $request The REST request object.
     * @return WP_REST_Response The response object with the appropriate status code.
     */
    public function handleGifting(WP_REST_Request $request)
    {
        $input = @file_get_contents("php://input");
        $eventObj = json_decode($input);

        $order_id = $eventObj->order_id;
        $sns_status = get_post_meta($order_id, '_customer_gifted', true);

        if ($sns_status === 'true') {
            return $this->returnStatusCode(200);
        } elseif ($sns_status === 'processing') {
            return $this->returnStatusCode(500);
        }

        // The main logic of the gifting process goes here.
        // ...

        // Assume successful completion of the script
        update_post_meta($order_id, '_customer_gifted', 'gifted');
        return $this->returnStatusCode(200);
    }

    /**
     * Returns an HTTP status code.
     * 
     * Helper function to set the HTTP status header and return a REST response.
     *
     * @param int $code The HTTP status code to return.
     * @return WP_REST_Response The REST response object.
     */
    private function returnStatusCode($code)
    {
        status_header($code);
        return new WP_REST_Response('', $code);
    }
}

// Initialize the CashToken Gifting API.
new CashTokenGiftingAPI();
