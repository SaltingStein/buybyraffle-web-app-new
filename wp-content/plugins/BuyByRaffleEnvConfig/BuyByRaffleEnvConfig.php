<?php 
namespace Sgs\Buybyraffle;
use Google\Cloud\PubSub\PubSubClient;
use Google\Client;
use Exception;

class BuyByRaffleEnvConfig {
    private $configFilePaths = [];
    private $environment;

    public function __construct() {
        $this->setEnvironmentConfig();
        $this->environment = wp_get_environment_type();
    }

    private function setEnvironmentConfig() {
        $environment = wp_get_environment_type();

        // switch ($environment) {
        //     case 'local':
        //         $this->configFilePaths[] = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
        //         $this->configFilePaths[] = 'C:\wamp64\www\wordpress\cashtoken_idp_local_env.json';
        //         break;
        //     case 'development':
        //         $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\buybyraffle-dcc92f760bee.json';
        //         $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\cashtoken_idp_local_env.json';
        //         break;
        //     ;
        //     case 'staging':
        //         $this->configFilePaths[] = '/var/env/buybyraffle-dcc92f760bee.json';
        //         $this->configFilePaths[] = '/var/env/cashtoken_idp_staging_env.json';
        //         //$this->configFilePaths[] = '/home/master/applications/aczbbjzsvv/private_html/cashtoken_idp_staging_env.json';
        //         break;
        //     ;
        //     case 'production':
        //         // Set paths for production environment
        //         $this->configFilePaths[] = '/var/env/buybyraffle-c24c1f61c187.json';
        //         $this->configFilePaths[] = '/var/env/cashtoken_idp_production_env.json';
        //         break;
        //     default:
        //         $this->configFilePaths[] = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
        //         $this->configFilePaths[] = 'C:\wamp64\www\wordpress\cashtoken_idp_local_env.json';
        // }
        switch ($environment) {
            case 'local':
                $this->configFilePaths[] = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
                $this->configFilePaths[] = 'C:\wamp64\www\wordpress\cashtoken_idp_local_env.json';
                break;
            case 'development':
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\buybyraffle-dcc92f760bee.json';
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\cashtoken_idp_local_env.json';
                break;
            case 'staging':
                // Use the previously defined constants for staging environment
                $this->configFilePaths[] = STAGING_CONFIG_FILE_PATH; // Use the staging config file path
                $this->configFilePaths[] = STAGING_CONFIG_FILE_PATH_FOR_CASHTOKEN; // Replace with the actual constant for Cashtoken config
                break;
            case 'production':
                // Use the previously defined constants for production environment
                $this->configFilePaths[] = PRODUCTION_CONFIG_FILE_PATH; // Use the production config file path
                $this->configFilePaths[] = PRODUCTION_CONFIG_FILE_PATH_FOR_CASHTOKEN; // Replace with the actual constant for Cashtoken config
                break;
            default:
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\buybyraffle-dcc92f760bee.json';
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\cashtoken_idp_local_env.json';
        }
    }

    public function getConfigurations() {
        $config = [];
        foreach ($this->configFilePaths as $filePath) {
            $fileContents = file_get_contents($filePath);
            if ($fileContents === false) {
                error_log("Error reading config file: $filePath");
                continue;
            }
            $fileConfig = json_decode($fileContents, true);
            if ($fileConfig === null) {
                error_log("Error decoding JSON from config file: $filePath");
                continue;
            }
            $config = array_merge($config, $fileConfig);
        }
        return $config;
    }
    /**
     * Fetches a bearer token using the Google API client.
     *
     * This method looks for the 'buybyraffle_dcc92f760bee.json' file in the configuration
     * paths and uses it to authenticate with Google and fetch a bearer token.
     *
     * @return string|false The bearer token if successful, or false on failure.
     */
    public function getBearerToken($configFilePath) {
        try {
            $client = new Client();
            $client->setAuthConfig($configFilePath);
            $client->setScopes(['https://www.googleapis.com/auth/cloud-platform']);
    
            // Fetch the access token
            $accessToken = $client->fetchAccessTokenWithAssertion();
    
            // Return the access token
            if (isset($accessToken['access_token'])) {
                return $accessToken['access_token'];
            } else {
                throw new Exception('Failed to fetch access token');
            }
        } catch (Exception $e) {
            // Handle exceptions, such as file not found or invalid credentials
            error_log('Exception in getBearerToken: ' . $e->getMessage());
            throw $e; // Rethrow the exception for the caller to handle
        }
    }


    public function publishMessage($projectId, $topicName, $messageData) {
        // Detect the environment
        $environment = wp_get_environment_type();

        // Define the correct configuration file path based on the environment
        $configFilePath = ''; // Initialize variable
 
        // Determine the correct configuration file path based on the environment
        // switch ($environment) {
        //     case 'local':
        //         $configFilePath = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
        //         break;
        //     case 'development':
        //             $configFilePath = 'C:\xampp\htdocs\buybyraffle\buybyraffle-dcc92f760bee.json';
        //         break;
        //     case 'staging':
        //         //$configFilePath = '/home/master/applications/aczbbjzsvv/private_html/buybyraffle-dcc92f760bee.json';
        //         $configFilePath = '/var/env/buybyraffle-dcc92f760bee.json';
        //         break;
        //     case 'production':
        //         $configFilePath = '/var/env/buybyraffle-c24c1f61c187.json';
        //         break;
        //     default:
        //         $configFilePath = '/var/env/buybyraffle-dcc92f760bee.json';
        //         break;
        // }
        switch ($environment) {
            case 'local':
                $configFilePath = LOCAL_CONFIG_FILE_PATH;
                break;
            case 'development':
                $configFilePath = DEVELOPMENT_CONFIG_FILE_PATH;
                break;
            case 'staging':
                $configFilePath = STAGING_CONFIG_FILE_PATH;
                break;
            case 'production':
                $configFilePath = PRODUCTION_CONFIG_FILE_PATH;
                break;
            default:
                $configFilePath = PRODUCTION_CONFIG_FILE_PATH; // Default to production
              
        }

        // Assuming the getBearerToken function exists and fetches a valid bearer token
        $bearerToken = $this->getBearerToken($configFilePath);
        if (!$bearerToken) {
            error_log('token_error', 'Failed to fetch access token.');
        }

        $apiUrl = 'https://pubsub.googleapis.com/v1/projects/' . $projectId . '/topics/' . $topicName . ':publish';

        $payload = [
            'messages' => [
                [
                    'attributes' => [
                        'action' => 'generatevouchers',
                        'environment' => wp_get_environment_type(),
                    ],
                    'data' => base64_encode(json_encode($messageData)),
                ],
            ],
        ];
        $maxRetries = 120;
        $attempt = 0;
        $backoff = 30; // seconds
        while ($attempt < $maxRetries) {
            $response = wp_remote_post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode($payload),
                'method' => 'POST',
                'data_format' => 'body',
                'timeout' => 65
            ]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
                // Success
                return true;
            }

            // Exponential backoff
            sleep($backoff);
            $backoff *= 2;
            $attempt++;
        }

        $admin_email = get_bloginfo('admin_email');
        $subject = 'Pub/Sub Message Publishing Failure';
        $message = 'Failed to publish message after ' . $maxRetries . ' attempts.';
        wp_mail($admin_email, $subject, $message);

        error_log($message);
        return false;

    }
}
