<?php
// scripts/config.php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "attendance_db";

$BASE_URL = "/attendance_app";
$UPLOAD_DIR = __DIR__ . "/../uploads";
if (!is_dir($UPLOAD_DIR)) { mkdir($UPLOAD_DIR, 0777, true); }
