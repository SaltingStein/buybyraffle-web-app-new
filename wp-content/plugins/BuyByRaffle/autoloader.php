<?php
spl_autoload_register('buybyraffle_autoloader');

function buybyraffle_autoloader($class_name) {
    if (false !== strpos($class_name, 'BuyByRaffle')) {
        $base_dir = plugin_dir_path(__FILE__);

        // Define the directory for 'includes'
        $include_dir = $base_dir . 'includes/';

        // Standard class files at root or other folders
        $class_file = $base_dir . str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

        // Class files in 'includes' folder
        $class_file_includes = $include_dir . str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

        if (file_exists($class_file)) {
            require_once $class_file;
        } elseif (file_exists($class_file_includes)) {
            require_once $class_file_includes;
        }
    }
}