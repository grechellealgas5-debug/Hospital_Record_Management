<?php
require_once 'config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT * FROM patients WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { flash('Patient not found.', 'danger'); go('index.php'); }

$stmt = $db->prepare('SELECT r.*, u.name AS author FROM records r LEFT JOIN users u ON u.id = r.user_id
                      WHERE r.patient_id = ? ORDER BY r.visit_date DESC, r.id DESC');
$stmt->bind_param('i', $id);
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$badge = ['outpatient' => 'bg-info', 'admitted' => 'bg-warning text-dark', 'discharged' => 'bg-secondary'];
include 'header.php';
?>
<div class="container py-4">
  <a href="index.php" class="text-decoration-none small no-print"><i class="bi bi-arrow-left"></i> All patients</a>

  <div class="card form-card mt-2 mb-4"><div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between gap-3">
      <div class="d-flex gap-3 align-items-center">
        <div class="avatar"><?= e(strtoupper($p['first_name'][0] . $p['last_name'][0])) ?></div>
        <div>
          <h3 class="fw-bold mb-0"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></h3>
          <span class="text-muted"><?= patient_no($p['id']) ?></span>
          <span class="badge <?= $badge[$p['status']] ?> ms-1"><?= e(ucfirst($p['status'])) ?></span>
        </div>
      </div>
      <div class="d-flex gap-2 align-items-start no-print">
        <button class="btn btn-outline-secondary btn-sm print-btn"><i class="bi bi-printer me-1"></i>Print</button>
        <a class="btn btn-outline-primary btn-sm" href="patient_form.php?id=<?= $p['id'] ?>"><i class="bi bi-pencil me-1"></i>Edit</a>
        <?php if (is_admin()): ?>
        <form method="post" action="patient_delete.php" data-confirm="Delete this patient and ALL their records?">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $p['id'] ?>">
          <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($p['allergies'] !== ''): ?>
      <div class="alert alert-warning mt-3 mb-0 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Allergies:</strong> <?= e($p['allergies']) ?></div>
    <?php endif; ?>

    <hr>
    <div class="row g-3 small">
      <div class="col-6 col-md-3"><div class="text-muted">Age</div><div class="fw-semibold"><?= age($p['birth_date']) ?> years</div></div>
      <div class="col-6 col-md-3"><div class="text-muted">Birth date</div><div class="fw-semibold"><?= e(date('M d, Y', strtotime($p['birth_date']))) ?></div></div>
      <div class="col-6 col-md-3"><div class="text-muted">Gender</div><div class="fw-semibold"><?= e($p['gender']) ?></div></div>
      <div class="col-6 col-md-3"><div class="text-muted">Blood type</div><div class="fw-semibold"><?= e($p['blood_type']) ?></div></div>
      <div class="col-6 col-md-3"><div class="text-muted">Phone</div><div class="fw-semibold"><?= e($p['phone']) ?></div></div>
      <div class="col-6 col-md-4"><div class="text-muted">Emergency contact</div><div class="fw-semibold"><?= e($p['emergency_contact']) ?></div></div>
      <div class="col-12 col-md-5"><div class="text-muted">Address</div><div class="fw-semibold"><?= e($p['address']) ?></div></div>
    </div>
  </div></div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-journal-medical me-2"></i>Medical records (<?= count($records) ?>)</h4>
    <a class="btn btn-primary btn-sm no-print" href="record_form.php?patient_id=<?= $p['id'] ?>"><i class="bi bi-plus-lg me-1"></i>Add record</a>
  </div>

  <?php if (!$records): ?>
    <div class="text-center text-muted py-5"><i class="bi bi-clipboard2" style="font-size:3rem"></i><p class="mt-2">No records yet.</p></div>
  <?php endif; ?>

  <?php foreach ($records as $r): ?>
    <?php $can_edit = is_admin() || $r['user_id'] == $_SESSION['user']['id'];  ?>
    <div class="card form-card record mb-3"><div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
          <h5 class="fw-bold mb-0"><?= e($r['diagnosis']) ?></h5>
          <small class="text-muted"><i class="bi bi-calendar-event me-1"></i><?= e(date('M d, Y', strtotime($r['visit_date']))) ?>
            &middot; <i class="bi bi-person me-1"></i><?= e($r['author'] ?? 'Unknown') ?></small>
        </div>
        <?php if ($can_edit): ?>
        <div class="d-flex gap-2 no-print">
          <a class="btn btn-sm btn-outline-secondary" href="record_form.php?id=<?= $r['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
          <form method="post" action="record_delete.php" data-confirm="Delete this record?">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <div class="mt-3 small">
        <div class="text-muted">Treatment</div>
        <div class="mb-2"><?= nl2br(e($r['treatment'])) ?></div>
        <?php if ($r['notes'] !== ''): ?>
          <div class="text-muted">Notes</div>
          <div><?= nl2br(e($r['notes'])) ?></div>
        <?php endif; ?>
      </div>
    </div></div>
  <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>
