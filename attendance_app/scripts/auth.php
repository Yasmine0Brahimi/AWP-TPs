<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function role() {
    return $_SESSION['user']['role'] ?? null;
}

function require_role($required) {
    if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? null) !== $required) {
        header("Location: /attendance_app/index.php");
        exit;
    }
}
