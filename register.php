<?php
require_once 'config.php';
if (isset($_SESSION['user'])) go('index.php');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    // Server-side validation
    if ($name === '' || strlen($name) > 60) $errors[] = 'Name is required (max 60 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (!$errors) {
        // Is the email already used? (prepared statement)
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'That email is already registered.';
        } else {
            // New accounts are always "staff" (only an admin can promote them)
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'staff')");
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();
            flash('Account created! You can log in now.');
            go('login.php');
        }
    }
}
include 'header.php';
?>
<div class="container py-4">
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-5">
    <div class="card form-card"><div class="card-body p-4 p-md-5">
      <div class="auth-icon"><i class="bi bi-person-badge"></i></div>
      <h3 class="text-center fw-bold mb-1">Staff registration</h3>
      <p class="text-center text-muted mb-4">Create an account to access patient records.</p>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endforeach; ?>
      <form method="post" class="needs-check" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Full name</label>
          <input class="form-control" name="name" required maxlength="60" value="<?= e($_POST['name'] ?? '') ?>">
          <div class="invalid-feedback">Please enter your name.</div></div>
        <div class="mb-3"><label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
          <div class="invalid-feedback">Please enter a valid email.</div></div>
        <div class="mb-3"><label class="form-label">Password</label>
          <div class="input-group">
            <input class="form-control" type="password" name="password" id="password" required minlength="6">
            <button class="btn btn-outline-secondary" type="button" id="togglePw"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback">At least 6 characters.</div>
          </div></div>
        <button class="btn btn-primary w-100 py-2">Sign up</button>
      </form>
      <p class="text-center mt-3 mb-0 small">Already have an account? <a href="login.php">Log in</a></p>
    </div></div>
  </div>
</div>
</div>
<?php include 'footer.php'; ?>
