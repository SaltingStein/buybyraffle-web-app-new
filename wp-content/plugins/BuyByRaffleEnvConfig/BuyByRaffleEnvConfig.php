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

        switch ($environment) {
            case 'local':
                $this->configFilePaths[] = 'C:\wamp64\www\wordpress\buybyraffle_dcc92f760bee.json';
                $this->configFilePaths[] = 'C:\wamp64\www\wordpress\cashtoken_idp_local_env.json';
                break;
            case 'development':
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\buybyraffle_dcc92f760bee.json';
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\cashtoken_idp_local_env.json';
                break;
            ;
            case 'staging':
                $this->configFilePaths[] = '/home/master/applications/aczbbjzsvv/private_html/buybyraffle-dcc92f760bee.json';
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\cashtoken_idp_staging_env.json';
                break;
            ;
            case 'production':
                $this->configFilePaths[] = '/home/master/applications/bbqpcmbxkq/private_html/buybyraffle-dcc92f760bee.json';
                $this->configFilePaths[] = 'C:\xampp\htdocs\wordpress\cashtoken_idp_production_env.json';
                break;
            default:
                error_log("Unrecognized environment type: $environment");
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
        switch ($environment) {
            case 'local':
                $configFilePath = 'C:\wamp64\www\wordpress\buybyraffle-dcc92f760bee.json';
                break;
            case 'development':
                    $configFilePath = 'C:\xampp\htdocs\buybyraffle\buybyraffle-dcc92f760bee.json';
                break;
            case 'staging':
                $configFilePath = '/home/master/applications/aczbbjzsvv/private_html/buybyraffle-dcc92f760bee.json';
                break;
            case 'production':
                $configFilePath = '/home/master/applications/bbqpcmbxkq/private_html/buybyraffle-dcc92f760bee.json';
                break;
            default:
                error_log("Unexpected environment type: $environment");
                return; // Exit if the environment is not recognized
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
