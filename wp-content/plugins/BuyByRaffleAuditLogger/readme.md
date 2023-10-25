# User Activity Logger

## Description

The User Activity Logger is a WordPress plugin designed to log various user actions. This includes user login/logout activities, content creation, updates, deletions, and WooCommerce order status changes.

## Version

1.0

## Features

- Logs user login and logout activities.
- Logs content creation, updates, and deletions.
- Logs WooCommerce order status changes and deletions.
- Uses PDO for database interactions.
- Modular codebase for easy maintenance and extensibility.

## Installation

1. Download the plugin zip file.
2. Go to your WordPress Dashboard and navigate to `Plugins -> Add New`.
3. Click on `Upload Plugin` and choose the downloaded zip file.
4. Click on `Install Now`.
5. After installation, click on `Activate Plugin`.

## Directory Structure

```
- includes/
    - class-audit-logs.php         // Main class for logging various user activities.
    - class-db-connection.php      // Handles database connections using PDO.
    - class-schema-migrations.php  // Manages database table creation and schema migrations.
    - class-session-logs.php       // Logs user session activities like login and logout.
    - class-content-logs.php       // Logs content-related activities like creation, update, and deletion.
    - app.php                      // Logs WooCommerce order-related activities.
- main-plugin-file.php            // Main plugin file that initializes and orchestrates the     plugin functionalities.
```

## Usage

Once the plugin is activated, it will automatically start logging the defined user activities. You can check the logs in the respective database tables.

## Hooks

The plugin uses various WordPress hooks for logging activities:

- `wp_login`
- `wp_logout`
- `publish_post`
- `before_delete_post`
- `post_updated`
- `woocommerce_order_status_changed`
- `woocommerce_before_order_object_save`

## Database Tables

The plugin creates the following tables:

### wp_buybyraffle_audit_logs

| Column                | Type                      | Description                          |
|-----------------------|---------------------------|--------------------------------------|
| id                    | INT                       | Auto-incremental primary key         |
| session_duration_id   | INT | Auto-incremental    | Foreign key from the session tbl (id)|
| user_id               | INT                       | User ID                              |
| user_login            | VARCHAR(20)               | Username                             |
| event                 | VARCHAR(100)              | Event type                           |
| timestamp             | DATETIME                  | Timestamp of the event               |
| post_id               | INT                       | Post ID (if applicable)              |
| old_status            | VARCHAR(10)               | Old status (if applicable)           |
| new_status            | VARCHAR(10)               | New status (if applicable)           |
| additional_info       | TEXT                      | Additional information (if any)      |

### wp_session_duration_logs

| Column           | Type         | Description                          |
|------------------|--------------|--------------------------------------|
| id               | INT          | Auto-incremental primary key         |
| user_id          | INT          | User ID                              |
| session_id       | VARCHAR(32)  | Session ID                           |
| login_timestamp  | DATETIME     | Login timestamp                      |
| logout_timestamp | DATETIME     | Logout timestamp                     |
| session_duration | INT          | Session duration in seconds          |
| device_info      | VARCHAR(100) | Device information                   |
| ip_address       | binary(16)   | User IP address                      |

## Contents Being Updated

- User login and logout timestamps
- Content creation, update, and deletion timestamps
- WooCommerce order status changes
- Session duration
- Device information

## Contributing

# How to Contribute

- Main Plugin File
The main file for this project is app.php, which serves as the entry point for the plugin. It initializes the database connection, schema migrations, and various logging services. The class-audit-logs.php file is particularly important as it initializes the different logging services.

- Modularization
The different logging activities are modularized into their own classes (Session_Logs, Content_Logs, Order_Logs, etc.) for ease of management and extensibility. This modular approach allows for better separation of concerns and makes it easier to add new logging functionalities.
- Prerequisites
    Basic understanding of PHP and WordPress plugin development
    Familiarity with PDO for database operations

# Steps:

- Fork the repository.
- Clone your fork locally.
- Create a new branch for your feature or bug fix.
- Make your changes.
- Push your changes back to your fork on GitHub.
- Create a pull request for your changes.

# Using the Database Connector

The DB_Connection class serves as the database connector for the plugin. If you're contributing a new class that requires database operations, you can utilize this class to establish a database connection. Here's how to do it:

- Import the DB_Connection class at the top of your PHP file:
```php
    require_once 'class-db-connection.php';
```
- Create an instance of DB_Connection within your class:
```php
    private $db_connection;
    public function __construct() {
        $this->db_connection = new DB_Connection();
    }
```
- Use the getDb() method to get the PDO object for database operations:

```php

        public function someDatabaseOperation() {
            $db = $this->db_connection->getDb();
            // Your database operations here
        }
```
By following these steps, you can easily integrate database operations into your new class.

## License

GPL-3.0 License