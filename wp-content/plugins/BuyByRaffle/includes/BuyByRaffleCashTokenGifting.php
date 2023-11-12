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
        if ($sns_status !== "gifted") {
            $order = wc_get_order($order_id);
            $items = $order->get_items();
    
            $product_ids = array();
            $vendor_ids = array();
    
            foreach ($items as $item) {
    
                $product_id = $item->get_product_id();
                $vendor_id = get_post_field('post_author', $product_id);
                $product_ids[] = $product_id;
                $vendor_ids[] = $vendor_id;
            }
    
            if (in_array(39, $vendor_ids) ||  in_array(354, $vendor_ids)) {
                return $this->returnStatusCode(200);
            }
    
            if (empty($order)) {
                update_post_meta($order_id, 'customer_gifted', "order doesn't exist");
                return $this->returnStatusCode(200);
            } else {
                $order = wc_get_order($order_id);
                $order_data = $order->get_data();
                $phone = $order_data['billing']['phone'];
                $phone = strval($phone);
                $pgs_cashtoken_campaignId = getenv('PGS_CASHTOKEN_CAMPAIGN_ID');
                //make a call for access token
                $idp_base_url = getenv('IDP_BASE_URL');
                $idp_username = getenv('IDP_TOKEN_USERNAME');
                $idp_password = getenv('IDP_TOKEN_PASSWORD');
    
                $url = $idp_base_url . "/oauth/token";
    
                $body = array(
                    'grant_type'    =>   'client_credentials',
                    //we need to extract this to env
                    'scope'   =>  'urn:cashtoken:api:gift_request'
                );
    
                $response = wp_remote_post(
                    $url,
                    array(
                        'method'      => 'POST','timeout'=> 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking'    => true,
                        'headers' =>  array(
                            'Authorization' => 'Basic ' . base64_encode($idp_username . ':' . $idp_password),
                            'Content-Type' => "application/x-www-form-urlencoded"
                        ),
                        'body' => $body
                    ),
                );
    
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
    
                    return new WP_REST_Response(
                        array(
                            'status' => 503,
                            'message' => "Service is unavailable at the moment"
                        ),
                        503
                    );
                } else {
                    $responseCode = $response["response"]["code"];
                    if ($responseCode == 200) {
                        //update_user_meta(24, '_cashtoken_access_token', $responseToken);
                        $response_code = wp_remote_retrieve_response_code($response);
    
                        $converted_res = json_decode(wp_remote_retrieve_body($response), true);
                        $access_token =  $converted_res['access_token'];
                        update_user_meta(1, '_cashtoken_access_token', $access_token);
                    }
                }
    
                $gift_url = getenv('GIFTING_BASE_URL');
    
                $cashtoken_endpoint = $gift_url . 'v2/gifting/submit';
    
                $_cashtoken_unit_price = getenv('CASHTOKEN_UNIT_PRICE');
                update_post_meta($order_id, 'customer_gifted', "processing");
                $batchId = strval($order_id);
                $fields = [
                    'commodity'  => 'cashtoken',
                    'batchId' => $batchId,
                    'profile' => 'default',
                    'campaign' => $pgs_cashtoken_campaignId,
                    'recipients' => [
                        [
                            'recipient' => $phone,
                            'value' => '1',
                            'giftId' => $batchId
                        ]
                    ]
                ];
    
                $fields_string = wp_json_encode($fields);
    
                $header = [
                    'Content-Type' => 'application/json',
                    'X-Country-Id' => 'NG',
                    'Authorization' => 'Bearer ' . $access_token
                ];
                $options = [
                    'body' => $fields_string,
                    'headers' => $header,
                    'timeout'     => 60,
                ];
    
                $result = wp_remote_post($cashtoken_endpoint, $options);
                $result = json_decode($result['body']);
                $response = (array) $result;

                // Check for error in the response from the cashtoken endpoint
                if ($response['error']) {
                    update_post_meta($order_id, 'customer_gifted', $response['error']);
                    return $this->returnStatusCode(500);
                } else {
                    update_post_meta($order_id, 'customer_gifted', 'gifted');
                    return $this->returnStatusCode(200);
                }
            }
        }
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
        $this->returnStatusCode($code);
        return new WP_REST_Response('', $code);
    }
}
