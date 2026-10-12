<?php
require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)$_POST['id'];
    $stmt = $db->prepare('DELETE FROM patients WHERE id = ?');   
    $stmt->bind_param('i', $id);
    $stmt->execute();
    flash('Patient deleted.', 'info');
}
go('index.php');
