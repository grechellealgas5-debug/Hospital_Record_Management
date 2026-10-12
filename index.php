<?php
require_once 'config.php';
require_login();   
include 'header.php';

$total    = $db->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
$admitted = $db->query("SELECT COUNT(*) FROM patients WHERE status='admitted'")->fetch_row()[0];
$today    = $db->query("SELECT COUNT(*) FROM records WHERE visit_date = CURDATE()")->fetch_row()[0];
?>
<div class="container py-4">

  <div class="hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h2 class="fw-bold mb-1">Patient Records</h2>
        <div class="opacity-75">Signed in as <?= e($_SESSION['user']['name']) ?> (<?= e($_SESSION['user']['role']) ?>)</div>
      </div>
      <a href="patient_form.php" class="btn btn-light fw-semibold"><i class="bi bi-person-plus me-1"></i>Add patient</a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-4"><div class="stat d-flex justify-content-between align-items-center">
      <div><div class="num" style="color:var(--brand)"><?= $total ?></div><small class="text-muted">Patients</small></div>
      <i class="bi bi-people d-none d-sm-block" style="color:var(--brand)"></i></div></div>
    <div class="col-4"><div class="stat d-flex justify-content-between align-items-center">
      <div><div class="num text-warning"><?= $admitted ?></div><small class="text-muted">Admitted</small></div>
      <i class="bi bi-hospital text-warning d-none d-sm-block"></i></div></div>
    <div class="col-4"><div class="stat d-flex justify-content-between align-items-center">
      <div><div class="num text-primary"><?= $today ?></div><small class="text-muted">Records today</small></div>
      <i class="bi bi-journal-medical text-primary d-none d-sm-block"></i></div></div>
  </div>

  <div class="card form-card mb-4"><div class="card-body">
    <div class="row g-2">
      <div class="col-lg-5">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input id="search" class="form-control" placeholder="Search by name or phone...">
        </div>
      </div>
      <div class="col-4 col-lg-2">
        <select id="status" class="form-select">
          <option value="">All status</option>
          <?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>"><?= e(ucfirst($s)) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-4 col-lg-2">
        <select id="gender" class="form-select">
          <option value="">All genders</option>
          <?php foreach ($genders as $g): ?><option><?= e($g) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-4 col-lg-3">
        <select id="blood" class="form-select">
          <option value="">All blood types</option>
          <?php foreach ($blood_types as $b): ?><option><?= e($b) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
  </div></div>

  <div class="card form-card"><div class="table-responsive">
    <table class="table patient-table align-middle mb-0">
      <thead class="table-light">
        <tr><th>No.</th><th>Name</th><th>Age / Sex</th><th class="d-none d-md-table-cell">Blood</th>
            <th class="d-none d-md-table-cell">Phone</th><th>Status</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody id="patients"></tbody>
    </table>
  </div></div>
  <div id="empty" class="text-center text-muted py-5 d-none">
    <i class="bi bi-person-x" style="font-size:3rem"></i>
    <p class="mt-2">No patients found.</p>
  </div>
</div>

<?php  ?>
<script>
  const ME = <?= json_encode(['id' => $_SESSION['user']['id'], 'role' => $_SESSION['user']['role']]) ?>;
  const CSRF = <?= json_encode(csrf_token()) ?>;
</script>
<?php include 'footer.php'; ?>
