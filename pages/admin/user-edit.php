<?php
require_once '../../config/auth.php';
if (($_SESSION['role_name'] ?? '') !== 'admin') { http_response_code(403); die('Forbidden'); }
require_once '../../config/db.php';
require_once '../../config/csrf.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); die('Invalid user id'); }

// Fetch roles
$roles = [];
$rs = $conn->query("SELECT id, role AS name FROM roles ORDER BY role ASC");
if ($rs) $roles = $rs->fetch_all(MYSQLI_ASSOC);

// Load user
$stmt = $conn->prepare("SELECT id, name, email, role_id, status FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { http_response_code(404); die('User not found'); }

$csrf_token = csrf_token('user_edit_'.$id);

$errors = []; $ok = null;

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '', 'user_edit_'.$id)) {
        $errors[] = "Invalid or expired form. Please try again.";
    } else {
        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $newPass = trim($_POST['new_password'] ?? '');

        if ($name === '') $errors[] = "Name is required.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        if ($roleId <= 0) $errors[] = "Role is required.";

        // Prevent deactivating or demoting the last admin
        if ($user['role_id'] != $roleId || $status !== $user['status']) {
            // find admin role id
            $ridAdmin = $conn->query("SELECT id FROM roles WHERE role='admin' LIMIT 1")->fetch_assoc()['id'] ?? 0;
            if ($ridAdmin && ($user['role_id'] == $ridAdmin) && ($roleId != $ridAdmin || $status === 'inactive')) {
                // count admins
                $stmtC = $conn->prepare("SELECT COUNT(*) AS c FROM users WHERE role_id=?");
                $stmtC->bind_param("i", $ridAdmin);
                $stmtC->execute();
                $c = (int)($stmtC->get_result()->fetch_assoc()['c'] ?? 0);
                if ($c <= 1) $errors[] = "You cannot demote/deactivate the last remaining admin.";
            }
        }

        if (!$errors) {
            try {
                if ($newPass !== '') {
                    $hash = password_hash($newPass, PASSWORD_DEFAULT);
                    $stmtU = $conn->prepare("UPDATE users SET name=?, email=?, role_id=?, status=?, password=? WHERE id=?");
                    $stmtU->bind_param("ssissi", $name, $email, $roleId, $status, $hash, $id);
                } else {
                    $stmtU = $conn->prepare("UPDATE users SET name=?, email=?, role_id=?, status=? WHERE id=?");
                    $stmtU->bind_param("ssisi", $name, $email, $roleId, $status, $id);
                }
                $stmtU->execute();
                $ok = "User updated successfully.";
                // refresh user
                $stmt = $conn->prepare("SELECT id, name, email, role_id, status FROM users WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } catch (Throwable $e) {
                $errors[] = "Update failed: " . $e->getMessage();
            }
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<main class="admin-content">
  <div class="content-header">
    <h1><i class="fas fa-user-edit me-2"></i>Edit User</h1>
    <p>Update user details, role, status, and password.</p>
    <a href="users.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>

  <div class="content-body">
    <?php if ($ok): ?>
      <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($ok); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form method="post" class="card p-4">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Role</label>
          <select name="role_id" class="form-select" required>
            <option value="">Select role…</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo $r['id']; ?>" <?php echo ($user['role_id']==$r['id']?'selected':''); ?>>
                <?php echo htmlspecialchars(ucfirst($r['name'])); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="active"   <?php echo ($user['status']==='active'?'selected':''); ?>>Active</option>
            <option value="inactive" <?php echo ($user['status']==='inactive'?'selected':''); ?>>Inactive</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label>
          <input type="password" name="new_password" class="form-control" minlength="6">
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
        <a href="users.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</main>
<?php include 'includes/footer.php'; ?>
