<?php
/**
 * Bulk SMS Provider Class File
 *
 * Handles the functionality to send SMS messages using the BulkSMSNigeria service.
 */
// Include the IMessageProvider interface
require_once UMH_PLUGIN_DIR . 'includes/IMessageProvider.php';

/**
 * Class BulkSMSProvider
 *
 * Implements IMessageProvider for sending SMS messages using the BulkSMSNigeria API.
 */
class BulkSMSProvider implements IMessageProvider {
    /**
     * API token for BulkSMSNigeria.
     * @var string
     */
    private $apiToken;

    /**
     * Constructor for the BulkSMSProvider class.
     *
     * @param string $apiToken The API token for BulkSMSNigeria service.
     */
    public function __construct($apiToken) {
        $this->apiToken = get_option('umh_api_token');
    }

    /**
     * Send an SMS message.
     *
     * @param string $recipient The phone number of the recipient.
     * @param string $message The message content.
     * @return array|false Returns the decoded response from BulkSMSNigeria or false on failure.
     * @throws Exception Throws an exception if there is a cURL error or an API error.
     */
    public function sendMessage($recipient, $message) {
        try {
            $curl = curl_init();
            
            // Set up the cURL options for sending the SMS
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.bulksmsnigeria.com/api/v2/sms',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'body' => $message,
					'from' => "BuyByRaffle",
                    'to' => $recipient,
                    'api_token' => $this->apiToken,
                    'gateway' => 'direct-refund'
                ]),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json'
                ),
            ));

            // Execute the cURL request and handle errors
            $response = curl_exec($curl);
            if ($response === false) {
                throw new Exception('cURL error: ' . curl_error($curl));
            }

            // Decode the JSON response and handle errors
            $decodedResponse = json_decode($response, true);
            if (is_null($decodedResponse)) {
                throw new Exception('Invalid JSON response');
            }
            if (isset($decodedResponse['error'])) {
                throw new Exception('BulkSMS error: ' . $decodedResponse['error']['message']);
            }

            // Close the cURL session and return the response
            curl_close($curl);
            return $decodedResponse;

        } catch (Exception $e) {
            // Log the error and return false
            umh_log_error('BulkSMSProvider Error: ' . $e->getMessage(), [
                'recipient' => $recipient,
                'message' => $message
            ]);
            return false;
        }
    }
}
