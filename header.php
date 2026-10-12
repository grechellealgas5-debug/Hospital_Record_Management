<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MediRecord - Patient Record System</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark app-nav sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-heart-pulse-fill me-2"></i>MediRecord</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto align-items-md-center gap-md-2">
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-people me-1"></i>Patients</a></li>
          <li class="nav-item"><a class="btn btn-light btn-sm fw-semibold" href="patient_form.php"><i class="bi bi-person-plus me-1"></i>Add Patient</a></li>
          <?php if (is_admin()): ?>
            <li class="nav-item"><a class="nav-link" href="admin.php"><i class="bi bi-shield-lock me-1"></i>Staff</a></li>
          <?php endif; ?>
          <li class="nav-item"><span class="nav-link"><i class="bi bi-person-circle me-1"></i><?= e($_SESSION['user']['name']) ?>
            <span class="badge bg-light text-dark ms-1"><?= e($_SESSION['user']['role']) ?></span></span></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-light btn-sm fw-semibold" href="register.php">Staff sign up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php ?>
<?php if (isset($_SESSION['flash'])): ?>
  <div class="container mt-3">
    <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
      <?= e($_SESSION['flash']['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
