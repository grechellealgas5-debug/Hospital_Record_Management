<?php

require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $uid = (int)$_POST['user_id'];

    if ($uid !== (int)$_SESSION['user']['id']) {   
        if ($_POST['action'] === 'delete') {
            $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            flash('Staff account deleted.', 'info');
        } elseif ($_POST['action'] === 'role' && in_array($_POST['role'], ['admin', 'staff'])) {
            $stmt = $db->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->bind_param('si', $_POST['role'], $uid);
            $stmt->execute();
            flash('Role updated.');
        }
    }
    go('admin.php');
}

$users = $db->query('SELECT u.*, (SELECT COUNT(*) FROM records WHERE user_id = u.id) AS total
                     FROM users u ORDER BY u.id')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="container py-4">
  <h3 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Staff accounts</h3>

  <div class="card form-card"><div class="table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Role</th><th>Records written</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td class="fw-semibold"><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td><span class="badge <?= $u['role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>"><?= e($u['role']) ?></span></td>
        <td><?= $u['total'] ?></td>
        <td class="text-end">
          <?php if ($u['id'] != $_SESSION['user']['id']): ?>
          <form method="post" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="action" value="role">
            <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'staff' : 'admin' ?>">
            <button class="btn btn-sm btn-outline-primary">Make <?= $u['role'] === 'admin' ? 'staff' : 'admin' ?></button>
          </form>
          <form method="post" class="d-inline" data-confirm="Delete this staff account?">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
          <?php else: ?><small class="text-muted">You</small><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div></div>
</div>
<?php include 'footer.php'; ?>
