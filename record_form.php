<?php
require_once 'config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$errors = [];
$r = ['visit_date' => date('Y-m-d'), 'diagnosis' => '', 'treatment' => '', 'notes' => ''];

if ($id) {
    $stmt = $db->prepare('SELECT * FROM records WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if (!$r) go('index.php');
    if (!is_admin() && $r['user_id'] != $_SESSION['user']['id']) {
        flash('You can only edit your own records.', 'danger');
        go('patient_view.php?id=' . $r['patient_id']);
    }
    $patient_id = $r['patient_id'];
} else {
    $patient_id = (int)($_GET['patient_id'] ?? 0);
}

$stmt = $db->prepare('SELECT id, first_name, last_name FROM patients WHERE id = ?');
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
if (!$patient) go('index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $r = [
        'visit_date' => $_POST['visit_date'],
        'diagnosis'  => trim($_POST['diagnosis']),
        'treatment'  => trim($_POST['treatment']),
        'notes'      => trim($_POST['notes']),
    ];

    
    $d = DateTime::createFromFormat('Y-m-d', $r['visit_date']);
    if (!$d || $d->format('Y-m-d') !== $r['visit_date']) $errors[] = 'Enter a valid visit date.';
    elseif ($r['visit_date'] > date('Y-m-d')) $errors[] = 'Visit date cannot be in the future.';
    if ($r['diagnosis'] === '' || strlen($r['diagnosis']) > 150) $errors[] = 'Diagnosis is required (max 150 characters).';
    if ($r['treatment'] === '') $errors[] = 'Treatment is required.';

    if (!$errors) {
        if ($id) {   
            $stmt = $db->prepare('UPDATE records SET visit_date=?, diagnosis=?, treatment=?, notes=? WHERE id=?');
            $stmt->bind_param('ssssi', $r['visit_date'], $r['diagnosis'], $r['treatment'], $r['notes'], $id);
            flash('Record updated.');
        } else {     
            $stmt = $db->prepare('INSERT INTO records (patient_id, user_id, visit_date, diagnosis, treatment, notes) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('iissss', $patient_id, $_SESSION['user']['id'], $r['visit_date'], $r['diagnosis'], $r['treatment'], $r['notes']);
            flash('Record added.');
        }
        $stmt->execute();
        go('patient_view.php?id=' . $patient_id);
    }
}
include 'header.php';
?>
<div class="container py-4">
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card form-card"><div class="card-body p-4 p-md-5">
      <h3 class="fw-bold mb-1"><?= $id ? 'Edit record' : 'Add medical record' ?></h3>
      <p class="text-muted mb-4">Patient: <strong><?= e($patient['first_name'] . ' ' . $patient['last_name']) ?></strong> (<?= patient_no($patient['id']) ?>)</p>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
      <form method="post" class="needs-check" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3"><label class="form-label">Visit date</label>
          <input class="form-control" type="date" name="visit_date" required max="<?= date('Y-m-d') ?>" value="<?= e($r['visit_date']) ?>">
          <div class="invalid-feedback">Pick a date (not in the future).</div></div>

        <div class="mb-3"><label class="form-label">Diagnosis</label>
          <input class="form-control" name="diagnosis" required maxlength="150" value="<?= e($r['diagnosis']) ?>">
          <div class="invalid-feedback">Please enter a diagnosis.</div></div>

        <div class="mb-3"><label class="form-label">Treatment / prescription</label>
          <textarea class="form-control" name="treatment" rows="3" required><?= e($r['treatment']) ?></textarea>
          <div class="invalid-feedback">Please describe the treatment.</div></div>

        <div class="mb-4"><label class="form-label">Notes <span class="text-muted">(optional)</span></label>
          <textarea class="form-control" name="notes" rows="3"><?= e($r['notes']) ?></textarea></div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save</button>
          <a class="btn btn-outline-secondary" href="patient_view.php?id=<?= $patient_id ?>">Cancel</a>
        </div>
      </form>
    </div></div>
  </div>
</div>
</div>
<?php include 'footer.php'; ?>
