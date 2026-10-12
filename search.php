<?php
// search.php - returns matching patients as JSON (called by app.js with fetch)
require_once 'config.php';
header('Content-Type: application/json');

// Patient data is private
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo '[]';
    exit;
}

$where  = [];     // pieces of the WHERE clause
$types  = '';     // bind_param type string
$params = [];     // values for the ? placeholders

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $where[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR phone LIKE ?)";
    $types .= 'ss';
    array_push($params, "%$q%", "%$q%");
}
if (in_array($_GET['status'] ?? '', $statuses)) {
    $where[] = 'status = ?';
    $types .= 's';
    $params[] = $_GET['status'];
}
if (in_array($_GET['gender'] ?? '', $genders)) {
    $where[] = 'gender = ?';
    $types .= 's';
    $params[] = $_GET['gender'];
}
if (in_array($_GET['blood'] ?? '', $blood_types)) {
    $where[] = 'blood_type = ?';
    $types .= 's';
    $params[] = $_GET['blood'];
}

$sql = 'SELECT id, first_name, last_name, birth_date, gender, blood_type, phone, status FROM patients';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY last_name, first_name';

$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
