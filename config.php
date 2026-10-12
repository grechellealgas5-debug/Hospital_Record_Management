<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$name = 'hospital_db';

$db = new mysqli($host, $user, $pass, $name);
if ($db->connect_error) {
    die('Database connection failed. Is MySQL running and is the "hospital_db" database imported?');
}
$db->set_charset('utf8mb4');

$genders     = ['Male', 'Female', 'Other'];
$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'];
$statuses    = ['outpatient', 'admitted', 'discharged'];

function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function go($url) {
    header("Location: $url");
    exit;
}

function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function patient_no($id) {
    return 'P-' . str_pad($id, 4, '0', STR_PAD_LEFT);
}

function age($birth) {
    return (new DateTime($birth))->diff(new DateTime())->y;
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function require_login() {
    if (!isset($_SESSION['user'])) {
        flash('Please log in first.', 'warning');
        go('login.php');
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        flash('Only administrators can do that.', 'danger');
        go('index.php');
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function check_csrf() {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        die('Invalid request.');
    }
}
