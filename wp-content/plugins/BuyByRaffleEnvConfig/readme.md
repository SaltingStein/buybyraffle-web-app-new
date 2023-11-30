# BuyByRaffle Environment Configurator

## Description
The BuyByRaffle Environment Configurator plugin manages environment-specific configurations for the BuyByRaffle WordPress setup. It dynamically loads configuration settings based on the current environment (local, development, staging, or production) and makes these configurations accessible throughout the WordPress site.

The path to the configuration file should be correctly added to the BuyByRaffleEnvConfig.php file. Prefereable in a non-public accessible environment. Especially if the configuration file contains sensitive information.

## Installation
1. Download the plugin's zip file.
2. Go to your WordPress Dashboard and navigate to `Plugins` -> `Add New`.
3. Click `Upload Plugin` and upload the zip file.
4. Activate the plugin after the upload is complete.

## Usage
The plugin provides a centralized way to access configuration data across your WordPress site. To use the configuration data, you need to include the `BuyByRaffleEnvConfig` class and call its `getConfigurations` method.

Here's how you can use it in your WordPress theme or plugin:

1. Include the `BuyByRaffleEnvConfig` class at the beginning of your PHP file:
   ```php
   use Sgs\Buybyraffle\BuyByRaffleEnvConfig;
   require_once plugin_dir_path(__FILE__) . 'path/to/BuyByRaffleEnvConfig.php';

### Create an instance of the BuyByRaffleEnvConfig class and retrieve configurations:
```php
$configInstance = new BuyByRaffleEnvConfig();
$configurations = $configInstance->getConfigurations();
```
### Use the $configurations array as needed:
```php
$apiUrl = $configurations['API_URL'] ?? null;
// Other configuration uses...
```

## Functionality

The plugin automatically determines the environment type and loads the appropriate configuration files. It supports the following environments:

    Local
    Development
    Staging
    Production

The configurations for each environment should be stored in JSON files and placed in the specified paths for each environment type.

## Support

For support, please contact SGS Team.

## Plugin URI

https://saltingstein.com

## Author

SGS Team <Mzer Michael Terungwa>


### Notes for Customization
```markdown
- Replace `path/to/BuyByRaffleEnvConfig.php` with the actual path to your `BuyByRaffleEnvConfig.php` file.
- Modify the support contact and Plugin URI as needed.
- Add any additional instructions or information that users of the plugin might need.
```