# PGS Vouchers Plugin for WordPress

The BuyByRaffle Vouchers plugin for WordPress is a comprehensive solution designed for generating, managing, and redeeming e-pin vouchers. Integrated seamlessly with WooCommerce, it provides a custom payment gateway and leverages Google Cloud Pub/Sub for scalable and efficient processing. This plugin is ideal for businesses looking to incorporate a voucher-based payment system within their WordPress environment.

## Features

1. Voucher Management: Generate, redeem, and manage e-pins for vouchers directly from the WordPress dashboard.
2. WooCommerce Integration: Custom payment gateway to accept vouchers as a payment method in WooCommerce stores.
3. Google Cloud Pub/Sub Integration: Scalable message publishing and consumption for asynchronous processing tasks.
4. REST API Support: Custom endpoints for voucher operations, secured with JWT authentication for selected routes.
5. Environment-Specific Configuration: Tailored configurations for different deployment stages - local, development, staging, and production.

## Directory Structure

Below is the directory structure of the PGS Vouchers plugin:
```
voucher-plugin/
-apis
--get-voucher.php
--redeem-voucher.php
--sendvouchersbymail.php
-libraries
--blomFilter.php
--pubsubConsumerVoucherGen.php
--pubsubPublisherEmailVoucherCsv.php
--pubsubPublisherVoucherGen.php
-payment
--wc-payment-gateway-voucher.php
vendor
batchers-table.php
composer.json
composer.lock
index.php
voucher.php
```
## Installation

1. Clone or download the plugin repository.
2. If you haven't already, install Composer to manage dependencies. Then run `composer install` in the plugin directory to install PHP Office Spreadsheet.
3. Upload the plugin files to your `/wp-content/plugins/pgs-vouchers` directory, or install the plugin through the WordPress plugins screen directly.
4. Install Dependencies: Navigate to the plugin directory and run:
5. Activate the plugin through the 'Plugins' screen in WordPress.

## Usage

### Admin Interface

1. Admin Panel: Manage voucher batches and view details from the WordPress admin panel.
Navigate to the 'E-Pin Management' page in the WordPress admin menu. Here, you can generate new e-pins by specifying the number of pins you want to create.
Usage
2.  WooCommerce Checkout: Customers can choose 'PGS Voucher Payment' during checkout and pay using their vouchers.
3. API Interaction: Use the provided REST API endpoints for programmatic access to voucher functionalities.

### API Reference

A detailed API reference is available in the API.md file.

## Contributing

Contributions are welcome from the community. To contribute:

1. Fork the Repository: Create your own fork of the project.
2. Create a Feature Branch: git checkout -b new-feature-branch
3. ommit Your Changes: git commit -am 'Add some feature'
4. Push to the Branch: git push origin new-feature-branch
5. Submit a Pull Request: Open a new pull request targeting the main branch of the original repository.
Please follow the WordPress coding standards for PHP, HTML, and JavaScript.

## Dependencies

- [PHP Office Spreadsheet](https://github.com/PHPOffice/PhpSpreadsheet): Used for generating Excel files containing lists of generated e-pins.

## Support

If you encounter any problems or have any queries about using the plugin, please submit an issue on the GitHub repository.

## License

The PGS Vouchers plugin is open-sourced software licensed under the GPL v2 or later.

