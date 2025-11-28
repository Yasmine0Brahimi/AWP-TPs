<?php
// db_connect.php

require_once 'config.php';

function getDbConnection() {
    try {
        // Create PDO connection
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Fetch associative arrays
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        return $pdo; // Return the connection object
    } catch (PDOException $e) {
        // Optional: log errors to a file
        file_put_contents('db_errors.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . PHP_EOL, FILE_APPEND);

        // Show a clean message to user
        die("Database connection failed. Please try again later.");
    }
}
?>
