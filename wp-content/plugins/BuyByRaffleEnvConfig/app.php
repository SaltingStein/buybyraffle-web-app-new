<?php
/**
 * Plugin Name: BuyByRaffle Environment Configurator
 * Plugin URI: https://saltingstein.com
 * Description: Manages environment-specific configurations for BuyByRaffle.
 * Version: 1.0
 * Author: SGS Team <Mzer Michael Terungwa>
 */

use Sgs\Buybyraffle\BuyByRaffleEnvConfig;

require_once plugin_dir_path(__FILE__) . 'BuyByRaffleEnvConfig.php';

// Create an instance of the class
$configInstance = new BuyByRaffleEnvConfig();

// Call the getConfigurations method to get the merged configuration array
$configurations = $configInstance->getConfigurations();
//error_log(print_r($configurations, true));
