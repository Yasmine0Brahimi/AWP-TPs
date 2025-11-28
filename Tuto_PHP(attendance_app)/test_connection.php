<?php
require_once 'db_connect.php';

$connection = getDbConnection();

if ($connection) {
    echo "Connection successful!";
} else {
    echo "Connection failed!";
}
?>
