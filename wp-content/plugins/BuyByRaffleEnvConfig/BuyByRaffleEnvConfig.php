<?php 
namespace Sgs\Buybyraffle;

class BuyByRaffleEnvConfig {
    private $configFilePaths = [];

    public function __construct() {
        $this->setEnvironmentConfig();
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
}
