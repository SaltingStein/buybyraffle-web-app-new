<?php 
/**
 * Handles the database operations using PDO for the Raffle system.
 *
 * This class is responsible for establishing a connection to the database
 * and executing queries, particularly for the Raffle system. It retrieves
 * the necessary database credentials from WordPress options.
 */

namespace Sgs\Buybyraffle;

use Exception;

class BuyByRaffleCycleHandler {

    /**
     * Constructor.
     */
    public function __construct() {
        // Any necessary constructor code can go here.
    }

    /**
     * Posts a new raffle cycle to an external API using Basic Authentication.
     *
     * @param array $raffleCycleData Data for the raffle cycle.
     * @return string The ID of the last inserted row from the API response.
     * @throws Exception If there's an error in API request or response.
     */
    public function createRaffleCycle($raffleCycleData) {
        try{
            $configPath = $this->getConfigPath();
            //error_log(print_r($configPath, true));
           
            $configArray = $this->loadConfig($configPath);
            //error_log(print_r($configArray, true));
            
            // Assign each value to a variable
            $idpTokenPassword = $configArray['idp_token_password'];
            $idpTokenUsername = $configArray['idp_token_username'];
            $idpBaseUrl = $configArray['idp_base_url'];
            $pgsCashtokenCampaignId = $configArray['pgs_cashtoken_campaign_id'];
            $email = $configArray['email'];
            $apiUrl = $configArray['api_url'];
            $authPassword = $configArray['auth_password'];

            $data_to_send = json_encode($raffleCycleData);

            // Basic Auth credentials (replace with actual credentials)
            $base64_credentials = base64_encode($email . ':' . $authPassword);

            $api_response = wp_remote_post($apiUrl, [
                'body' => $data_to_send,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . $base64_credentials,
                ],
                'method' => 'POST',
                'data_format' => 'body',
            ]);

            // Error handling and response processing...
            if (is_wp_error($api_response)) {
                throw new Exception('Error posting to external API: ' . $api_response->get_error_message());
            }
            //error_log(print_r($api_response), true);
           
            // Retrieve the status code and response body from the API response.
            $status_code = wp_remote_retrieve_response_code($api_response);
            $api_response_body = wp_remote_retrieve_body($api_response);
            // error_log($api_response_body);
            // error_log($status_code);
            // return '';
           
            // Check if the response status code indicates a successful request.
            if ($status_code >= 200 && $status_code < 300) {
                // Parse the JSON response body to an associative array.
                $response_data = json_decode($api_response_body, true);

                // Check for JSON parse errors.
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Error parsing JSON from API response: ' . json_last_error_msg());
                }
                //error_log(print_r($response_data), true);
                // Ensure the 'last_insert_id' key exists in the response data.
                if (isset($response_data['raffle_cycle_id'])) {
                    // Return the last inserted ID from the response.
                    return $response_data['raffle_cycle_id'];
                } else {
                    // The expected key does not exist in the response; throw an exception.
                    throw new Exception('Invalid API response: missing raffle cycle ID');
                }
            } else {
                // The response did not indicate success, throw an exception with details.
                throw new Exception("API request failed with status code {$status_code}: " . $api_response_body);
            }
        } catch (Exception $e) {
            error_log('Error in createRaffleCycle: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Determines the configuration file path based on the server environment.
     *
     * @return string Configuration file path.
     */
    private function getConfigPath() {
        $isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
        return $isLocalhost ? 
               'C:\wamp64\www\wordpress\buybyraffle_staging_env.json' :
               '/home/master/applications/aczbbjzsvv/private_html/buybyraffle_staging_env.json';
    }

    /**
     * Loads the configuration from a JSON file.
     *
     * @param string $configPath Path to the configuration file.
     * @return array Configuration data as an associative array.
     * @throws Exception If the file does not exist or cannot be parsed.
     */
    public function loadConfig($configPath) {
        // Check if the server is local or remote
        $isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
    
        // Load the JSON configuration file
        if (!file_exists($configPath)) {
            throw new Exception('Configuration file does not exist: ' . $configPath);
        }
    
        // Decode the JSON string into a PHP associative array
        $config = json_decode(file_get_contents($configPath), true);
    
        // Prepare an array to hold the loaded configurations
        $loadedConfig = [
            // Global configurations
            'idp_token_password' => $config['IDP_TOKEN_PASSWORD'],
            'idp_token_username' => $config['IDP_TOKEN_USERNAME'],
            'idp_base_url' => $config['IDP_BASE_URL'],
            'pgs_cashtoken_campaign_id' => $config['PGS_CASHTOKEN_CAMPAIGN_ID'],
            'email' => $config['email']
        ];
    
        // Load environment-specific configurations
        $environment = $isLocalhost ? 'local' : 'remote';
        $loadedConfig['api_url'] = $config['raffle_cycle_url_' . $environment];
        $loadedConfig['auth_password'] = $config['AUTH_PASSWORD_' . strtoupper($environment)];
    
        // Return the loaded configuration
        return $loadedConfig;
    }
}