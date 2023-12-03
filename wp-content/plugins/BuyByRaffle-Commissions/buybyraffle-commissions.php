<?php
/**
 * Plugin Name: BuybyRaffleCommission Model Settings
 * Description: A simple plugin to manage BuyByRaffle commission model settings.
 * Version: 1.0
 * Author: SGS Team
 */

class Commission_Model_Settings {

    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Display admin notice on successful save
        add_action('admin_notices', array($this, 'display_admin_notice'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Commission Model Settings',
            'Commission Model',
            'manage_options',
            'commission_model_settings',
            array($this, 'admin_page_callback')
        );
    }

    /**
     * Admin page callback
     */
    public function admin_page_callback() {
        ?>
        <div class="wrap">
            <h2>Commission Model Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('pgs_commission_model');
                do_settings_sections('commission_model_settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('pgs_commission_model', 'pgs_commission_model', array($this, 'sanitize_settings'));

        add_settings_section('commission_model_section', 'Commission Model Options', array($this, 'section_callback'), 'commission_model_settings');

        $options = array(
            'Prize Hero Product',
            'Bait Product',
            'Customer Cashtoken',
            'VAT',
            'Market Management',
            'Resellers/Agents',
            'LSB/NLRC/NLTF (Regulatory)',
            'NCC (Regulatory)',
            'SGS - Tech Platform Owner',
            'Facilitator',
            'Payment Gateway',
            'SMS and Marketing',
            'Voucher Printing',
            'Margin'
        );

        foreach ($options as $option) {
            add_settings_field(
                sanitize_title(str_replace(' ', '_', $option)),
                $option,
                array($this, 'field_callback'),
                'commission_model_settings',
                'commission_model_section',
                array('option' => $option)
            );
        }
    }

    /**
     * Section callback
     */
    public function section_callback() {
        echo '<p>Enter the percentage for each commission model option. The total must equal 100%.</p>';
    }

    /**
     * Field callback
     */
    public function field_callback($args) {
        $option = $args['option'];
        $options = get_option('pgs_commission_model');
        $value = isset($options[sanitize_title(str_replace(' ', '_', $option))]) ? esc_attr($options[sanitize_title(str_replace(' ', '_', $option))]) : '';

        echo '<input type="number" step=".01" id="' . sanitize_title(str_replace(' ', '_', $option)) . '" name="pgs_commission_model[' . sanitize_title(str_replace(' ', '_', $option)) . ']" value="' . $value . '" min="0" max="100" required />';
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        // Ensure the total is equal to 100
        $total = array_sum($input);
        if ($total !== 100) {
            add_settings_error('pgs_commission_model', 'pgs_commission_model_error', 'The total percentage must be equal to 100%', 'error');
            return get_option('pgs_commission_model'); // Revert to the previous settings
        }

        return $input;
    }

    /**
     * Display admin notice on successful settings save
     */
    public function display_admin_notice() {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Commission settings saved successfully!</p>
            </div>
            <?php
        }
    }
}

// Initialize the plugin
$commission_model_settings = new Commission_Model_Settings();

// Include class file for processing commission splits
include 'BuyByRaffleStakeHoldersOrderMetaHandler.php';
