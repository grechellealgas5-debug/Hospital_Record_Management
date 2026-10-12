<?php
require_once 'config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$p = ['first_name' => '', 'last_name' => '', 'birth_date' => '', 'gender' => 'Male', 'blood_type' => 'Unknown',
      'phone' => '', 'emergency_contact' => '', 'address' => '', 'allergies' => '', 'status' => 'outpatient'];
$errors = [];

if ($id) {
    $stmt = $db->prepare('SELECT * FROM patients WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p) go('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $p = [
        'first_name'        => trim($_POST['first_name']),
        'last_name'         => trim($_POST['last_name']),
        'birth_date'        => $_POST['birth_date'],
        'gender'            => $_POST['gender'],
        'blood_type'        => $_POST['blood_type'],
        'phone'             => trim($_POST['phone']),
        'emergency_contact' => trim($_POST['emergency_contact']),
        'address'           => trim($_POST['address']),
        'allergies'         => trim($_POST['allergies']),
        'status'            => $_POST['status'],
    ];


    if ($p['first_name'] === '' || strlen($p['first_name']) > 50) $errors[] = 'First name is required (max 50 characters).';
    if ($p['last_name'] === '' || strlen($p['last_name']) > 50) $errors[] = 'Last name is required (max 50 characters).';
    $d = DateTime::createFromFormat('Y-m-d', $p['birth_date']);
    if (!$d || $d->format('Y-m-d') !== $p['birth_date']) $errors[] = 'Enter a valid birth date.';
    elseif ($p['birth_date'] > date('Y-m-d')) $errors[] = 'Birth date cannot be in the future.';
    if (!in_array($p['gender'], $genders)) $errors[] = 'Invalid gender.';
    if (!in_array($p['blood_type'], $blood_types)) $errors[] = 'Invalid blood type.';
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $p['phone'])) $errors[] = 'Phone must be 7-20 characters (digits, +, -, spaces).';
    if ($p['emergency_contact'] === '' || strlen($p['emergency_contact']) > 100) $errors[] = 'Emergency contact is required (max 100 characters).';
    if ($p['address'] === '' || strlen($p['address']) > 150) $errors[] = 'Address is required (max 150 characters).';
    if (strlen($p['allergies']) > 200) $errors[] = 'Allergies must be 200 characters or less.';
    if (!in_array($p['status'], $statuses)) $errors[] = 'Invalid status.';

    if (!$errors) {
        $vals = array_values($p);  
        if ($id) {  
            $vals[] = $id;
            $stmt = $db->prepare('UPDATE patients SET first_name=?, last_name=?, birth_date=?, gender=?, blood_type=?, phone=?,
                                  emergency_contact=?, address=?, allergies=?, status=? WHERE id=?');
            $stmt->bind_param('ssssssssssi', ...$vals);
            $stmt->execute();
            flash('Patient updated.');
            go('patient_view.php?id=' . $id);
        } else {    
            $vals[] = $_SESSION['user']['id'];
            $stmt = $db->prepare('INSERT INTO patients (first_name, last_name, birth_date, gender, blood_type, phone,
                                  emergency_contact, address, allergies, status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->bind_param('ssssssssssi', ...$vals);
            $stmt->execute();
            flash('Patient added.');
            go('patient_view.php?id=' . $db->insert_id);
        }
    }
}
include 'header.php';
?>
<div class="container py-4">
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card form-card"><div class="card-body p-4 p-md-5">
      <h3 class="fw-bold mb-4"><i class="bi bi-person-vcard me-2"></i><?= $id ? 'Edit patient' : 'Add patient' ?></h3>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
      <form method="post" class="needs-check" novalidate>
        <?= csrf_field() ?>

        <div class="row">
          <div class="col-sm-6 mb-3"><label class="form-label">First name</label>
            <input class="form-control" name="first_name" required maxlength="50" value="<?= e($p['first_name']) ?>">
            <div class="invalid-feedback">Please enter the first name.</div></div>
          <div class="col-sm-6 mb-3"><label class="form-label">Last name</label>
            <input class="form-control" name="last_name" required maxlength="50" value="<?= e($p['last_name']) ?>">
            <div class="invalid-feedback">Please enter the last name.</div></div>
        </div>

        <div class="row">
          <div class="col-sm-4 mb-3"><label class="form-label">Birth date</label>
            <input class="form-control" type="date" name="birth_date" required max="<?= date('Y-m-d') ?>" value="<?= e($p['birth_date']) ?>">
            <div class="invalid-feedback">Pick a valid date.</div></div>
          <div class="col-sm-4 mb-3"><label class="form-label">Gender</label>
            <select class="form-select" name="gender">
              <?php foreach ($genders as $g): ?><option <?= $p['gender'] === $g ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?>
            </select></div>
          <div class="col-sm-4 mb-3"><label class="form-label">Blood type</label>
            <select class="form-select" name="blood_type">
              <?php foreach ($blood_types as $b): ?><option <?= $p['blood_type'] === $b ? 'selected' : '' ?>><?= e($b) ?></option><?php endforeach; ?>
            </select></div>
        </div>

        <div class="row">
          <div class="col-sm-6 mb-3"><label class="form-label">Phone</label>
            <input class="form-control" name="phone" required pattern="[0-9+\-\s()]{7,20}" placeholder="0917-555-0000" value="<?= e($p['phone']) ?>">
            <div class="invalid-feedback">Enter a valid phone number (7-20 digits).</div></div>
          <div class="col-sm-6 mb-3"><label class="form-label">Emergency contact</label>
            <input class="form-control" name="emergency_contact" required maxlength="100" placeholder="Name - phone" value="<?= e($p['emergency_contact']) ?>">
            <div class="invalid-feedback">Please enter an emergency contact.</div></div>
        </div>

        <div class="mb-3"><label class="form-label">Address</label>
          <input class="form-control" name="address" required maxlength="150" value="<?= e($p['address']) ?>">
          <div class="invalid-feedback">Please enter the address.</div></div>

        <div class="mb-3"><label class="form-label">Known allergies</label>
          <input class="form-control" name="allergies" maxlength="200" placeholder="Leave empty if none" value="<?= e($p['allergies']) ?>"></div>

        <div class="mb-4"><label class="form-label">Status</label>
          <select class="form-select" name="status">
            <?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $p['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option><?php endforeach; ?>
          </select></div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save</button>
          <a class="btn btn-outline-secondary" href="<?= $id ? 'patient_view.php?id=' . $id : 'index.php' ?>">Cancel</a>
        </div>
      </form>
    </div></div>
  </div>
</div>
</div>
<?php include 'footer.php'; ?>
