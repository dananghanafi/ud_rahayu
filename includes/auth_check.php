<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
}

// Function to check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Function to check if user is regular user
function isUser() {
    return isLoggedIn() && $_SESSION['role'] === 'user';
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /ud_rahayu/login.php');
        exit();
    }
}

// Function to require admin role
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /ud_rahayu/login.php');
        exit();
    }
}

// Function to require user role
function requireUser() {
    requireLogin();
    if (!isUser()) {
        header('Location: /ud_rahayu/login.php');
        exit();
    }
}
?> 