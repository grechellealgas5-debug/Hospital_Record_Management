<?php
require_once 'config.php';
if (isset($_SESSION['user'])) go('index.php');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = trim($_POST['email']);

    $stmt = $db->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($_POST['password'], $row['password'])) {
        session_regenerate_id(true);   
        $_SESSION['user'] = ['id' => $row['id'], 'name' => $row['name'], 'role' => $row['role']];
        flash('Welcome, ' . $row['name'] . '!');
        go('index.php');
    }
    $error = 'Wrong email or password.';
}
include 'header.php';
?>
<div class="container py-4">
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-5">
    <div class="card form-card"><div class="card-body p-4 p-md-5">
      <div class="auth-icon"><i class="bi bi-heart-pulse"></i></div>
      <h3 class="text-center fw-bold mb-1">MediRecord</h3>
      <p class="text-center text-muted mb-4">Log in to view patient records.</p>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="needs-check" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required>
          <div class="invalid-feedback">Please enter your email.</div></div>
        <div class="mb-3"><label class="form-label">Password</label>
          <div class="input-group">
            <input class="form-control" type="password" name="password" id="password" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePw"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback">Please enter your password.</div>
          </div></div>
        <button class="btn btn-primary w-100 py-2">Log in</button>
      </form>
      <p class="text-center mt-3 mb-0 small">New staff member? <a href="register.php">Sign up</a></p>
    </div></div>
  </div>
</div>
</div>
<?php include 'footer.php'; ?>
