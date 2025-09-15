<?php
// pages/admin/user-create.php
require_once '../../config/auth.php';
if (($_SESSION['role_name'] ?? '') !== 'admin') { http_response_code(403); die('Forbidden'); }
require_once '../../config/db.php';
require_once '../../config/csrf.php';

$errors = []; $ok = null;
$csrf_scope = 'user_create';

// fetch roles
$roles = [];
$rs = $conn->query("SELECT id, role AS name FROM roles ORDER BY role ASC");
if ($rs) $roles = $rs->fetch_all(MYSQLI_ASSOC);

// handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '', $csrf_scope)) {
        $errors[] = "Invalid or expired form. Please try again.";
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $roleId   = (int)($_POST['role_id'] ?? 0);
        $status   = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $password = trim($_POST['password'] ?? '');

        if ($name === '') $errors[] = "Name is required.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        if ($username === '') $errors[] = "Username is required.";
        if ($roleId <= 0) $errors[] = "Role is required.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

        // Optional: ensure the very first account must be admin
        // $countUsers = (int)($conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0);
        // if ($countUsers === 0) { /* first user */ $status = 'active'; $roleId = (int)($conn->query("SELECT id FROM roles WHERE role='admin'")->fetch_assoc()['id'] ?? $roleId); }

        // Uniqueness checks
        if (!$errors) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=? LIMIT 1");
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            if ($stmt->get_result()->fetch_assoc()) {
                $errors[] = "Email or Username already exists.";
            }
        }

        if (!$errors) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, name, email, password, role_id, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssis", $username, $name, $email, $hash, $roleId, $status);
                $stmt->execute();
                $ok = "User created successfully.";
                // After success, you can redirect to list:
                header("Location: users.php?created=1"); // or users.php depending on your filename
                exit;
            } catch (Throwable $e) {
                $errors[] = "Create failed: " . $e->getMessage();
            }
        }
    }
}

// token for next display/submit
$csrf_token = csrf_token($csrf_scope);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<main class="admin-content">
    <div class="content-header">
        <h1><i class="fas fa-user-plus me-2"></i>New User</h1>
        <p>Create a new user account.</p>
        <a href="users.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="content-body">
        <?php if ($ok): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($ok); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
                    <input type="text" name="name" class="form-control" required
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-select" required>
                        <option value="">Select role…</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?php echo (int)$r['id']; ?>"
                            <?php echo ((int)($_POST['role_id'] ?? 0)===(int)$r['id']?'selected':''); ?>>
                            <?php echo htmlspecialchars(ucfirst($r['name'])); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo (($_POST['status'] ?? '')==='inactive'?'':'selected'); ?>>
                            Active</option>
                        <option value="inactive" <?php echo (($_POST['status'] ?? '')==='inactive'?'selected':''); ?>>
                            Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create User</button>
                <a href="users.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>
<?php include 'includes/footer.php'; ?>