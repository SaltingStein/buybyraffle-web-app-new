<?php
/**
 * Admin Settings for Universal Message Hub
 *
 * This file contains the functions necessary to create and manage the admin settings page
 * for the Universal Message Hub plugin. It includes functions to register the menu item,
 * display the settings form, and register the settings with WordPress.
 */

/**
 * Registers the Universal Message Hub menu in the WordPress admin dashboard.
 *
 * This function adds a new menu item to the WordPress admin dashboard, which leads
 * to the settings page for the Universal Message Hub plugin.
 */
function umh_admin_menu() {
    // Add a new menu page under the Settings menu
    add_menu_page(
        'Universal Message Hub Settings', // Page title
        'Message Hub', // Menu title
        'manage_options', // Capability required to see this menu item
        'universal-message-hub', // Menu slug
        'umh_settings_page' // Function to display the settings page
    );
}
add_action('admin_menu', 'umh_admin_menu'); // Hook into the 'admin_menu' action
function umh_get_notification_providers() {
    $args = array(
        'role' => 'notification_service_provider', // Replace with your actual role name
        'orderby' => 'user_nicename',
        'order' => 'ASC'
    );
    $users = get_users($args);
    return $users;
}

/**
 * Displays the settings page for the Universal Message Hub.
 *
 * This function outputs the HTML for the settings page where users can configure
 * various options for the Universal Message Hub plugin.
 */
function umh_settings_page() {
    $providers = umh_get_notification_providers();
    $selected_provider = get_option('umh_selected_provider');
    // Check if settings were updated
    $settings_updated = isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true';
    ?>
    <div class="wrap">
    <?php if ($settings_updated) : ?>
            <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                <p><strong>Settings saved.</strong></p>
            </div>
        <?php endif; ?>
        <h1>Universal Message Hub Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('umh-settings-group'); ?>
            <?php do_settings_sections('umh-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Token</th>
                    <td><input type="text" name="umh_api_token" value="<?php echo esc_attr(get_option('umh_api_token')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Notification System Provider</th>
                    <td>
                        <select name="umh_selected_provider">
                            <?php foreach ($providers as $provider) : ?>
                                <option value="<?php echo esc_attr($provider->ID); ?>" <?php selected($selected_provider, $provider->ID); ?>>
                                    <?php echo esc_html($provider->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


/**
 * Registers the settings used by the Universal Message Hub.
 *
 * This function registers settings that will be stored and retrieved using the WordPress settings API.
 * These settings are used to configure various aspects of the Universal Message Hub plugin.
 */
function umh_register_settings() {
    // Register a new setting for the Universal Message Hub, for storing the API token
    register_setting('umh-settings-group', 'umh_api_token');
    // Additional settings can be registered here
}
add_action('admin_init', 'umh_register_settings'); // Hook into 'admin_init' to register settings
