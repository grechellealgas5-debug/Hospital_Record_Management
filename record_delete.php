<?php
// record_delete.php - DELETE a medical record (author or admin, POST only)
require_once 'config.php';
require_login();

$patient_id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)$_POST['id'];

    $stmt = $db->prepare('SELECT patient_id, user_id FROM records WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();

    if ($r) {
        $patient_id = $r['patient_id'];
        if (is_admin() || $r['user_id'] == $_SESSION['user']['id']) {
            $stmt = $db->prepare('DELETE FROM records WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            flash('Record deleted.', 'info');
        } else {
            flash('You can only delete your own records.', 'danger');
        }
    }
}
go($patient_id ? 'patient_view.php?id=' . $patient_id : 'index.php');
