<?php
// pages/admin/user.php

// --- Access control ---
require_once '../../config/auth.php';
if (($_SESSION['role_name'] ?? '') !== 'admin') {
    http_response_code(403); die('Forbidden');
}

// --- Core deps ---
require_once '../../config/db.php';
require_once '../../config/csrf.php';

// ---------- helpers ----------
function is_last_admin(mysqli $conn, int $userId): bool {
    // Is the target user an admin now?
    $q1 = $conn->prepare("SELECT role_id FROM users WHERE id=?");
    $q1->bind_param("i", $userId);
    $q1->execute();
    $u = $q1->get_result()->fetch_assoc();
    if (!$u) return false;

    $row = $conn->query("SELECT id FROM roles WHERE role='admin' LIMIT 1");
    if (!$row || !$row->num_rows) return false;
    $adminRoleId = (int)$row->fetch_assoc()['id'];

    if ((int)$u['role_id'] !== $adminRoleId) return false;

    $q2 = $conn->prepare("SELECT COUNT(*) AS c FROM users WHERE role_id=?");
    $q2->bind_param("i", $adminRoleId);
    $q2->execute();
    $c = (int)($q2->get_result()->fetch_assoc()['c'] ?? 0);

    return $c <= 1; // attempting to delete the last admin?
}

// ---------- POST actions (CSRF protected) ----------
$success_msg = $error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!csrf_verify($_POST['csrf_token'] ?? '', 'users_actions')) {
        http_response_code(400);
        $error_msg = "Invalid or expired request. Please try again.";
    } else {
        try {
            if ($action === 'delete' && $id > 0) {
                if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
                    $error_msg = "You cannot delete your own account.";
                } elseif (is_last_admin($conn, $id)) {
                    $error_msg = "You cannot delete the last remaining admin.";
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $success_msg = ($stmt->affected_rows > 0)
                        ? "User deleted successfully!"
                        : "User not found or already deleted.";
                }
            }

            if ($action === 'toggle_status' && $id > 0) {
                $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $u = $stmt->get_result()->fetch_assoc();

                if (!$u) {
                    $error_msg = "User not found.";
                } else {
                    $current = strtolower($u['status'] ?? 'active');
                    $new_status = ($current === 'active') ? 'inactive' : 'active';

                    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_status, $id); // correct: string, int
                    $stmt->execute();

                    $success_msg = "User status updated to {$new_status}.";
                }
            }
        } catch (Throwable $e) {
            $error_msg = "Action failed: " . $e->getMessage();
        }
    }
}

// ---------- fetch roles (for filter labels) ----------
$roles = [];
try {
    $rs = $conn->query("SELECT id, role AS name FROM roles ORDER BY role ASC");
    if ($rs) $roles = $rs->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $roles = [];
}

// ---------- fetch users with role name ----------
try {
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, u.role_id, r.role AS role_name, u.status, u.created_at
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        ORDER BY u.id DESC
    ");
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    error_log('Error fetching users: ' . $e->getMessage());
    $users = [];
}

// derived metrics
$activeUsers = array_filter($users, fn($u) => strtolower($u['status'] ?? 'active') === 'active');
$admins      = array_filter($users, fn($u) => strtolower($u['role_name'] ?? 'user') === 'admin');
$newUsers    = array_filter($users, fn($u) => strtotime($u['created_at']) > strtotime('-30 days'));

// CSRF token for action forms
$csrf_token = csrf_token('users_actions');

// ---------- view ----------
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-content">
    <div class="content-header">
        <h1><i class="fas fa-users me-2"></i>Users</h1>
        <p>Manage User Accounts</p>
        <a href="user-create.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New User
        </a>
    </div>

    <div class="content-body">
        <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row stats-cards">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($activeUsers); ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($admins); ?></div>
                    <div class="stat-label">Administrators</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($newUsers); ?></div>
                    <div class="stat-label">New This Month</div>
                </div>
            </div>
        </div>

        <div class="search-filter-bar">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="roleFilter">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?php echo strtolower(htmlspecialchars($r['name'])); ?>">
                            <?php echo htmlspecialchars(ucfirst($r['name'])); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <h5>No Users Found</h5>
                                <p>Start by adding your first user.</p>
                                <a href="user-create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Add User
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <?php
                          $role   = strtolower($u['role_name'] ?? 'user');
                          $status = strtolower($u['status'] ?? 'active');
                        ?>
                    <tr data-user-name="<?php echo strtolower(htmlspecialchars($u['name'])); ?>"
                        data-email="<?php echo strtolower(htmlspecialchars($u['email'])); ?>"
                        data-role="<?php echo htmlspecialchars($role); ?>"
                        data-status="<?php echo htmlspecialchars($status); ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3"><?php echo strtoupper(substr($u['name'], 0, 2)); ?></div>
                                <div class="user-info">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($u['name']); ?></h6>
                                    <small class="text-muted">ID: #<?php echo (int)$u['id']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-primary"><?php echo htmlspecialchars($u['email']); ?></span></td>
                        <td><span
                                class="badge role-badge <?php echo 'role-' . $role; ?>"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
                        </td>
                        <td><span class="badge status-badge <?php echo 'status-' . $status; ?>"><i
                                    class="fas fa-circle me-1"
                                    style="font-size:.5em;"></i><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                        </td>
                        <td><small class="text-muted"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></small>
                        </td>
                        <td>
                            <div class="action-buttons d-flex gap-2">
                                <a href="user-edit.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-outline-primary"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button type="button" class="btn btn-outline-warning"
                                    onclick="openToggleModal(<?php echo (int)$u['id']; ?>, '<?php echo addslashes($u['name']); ?>', '<?php echo $status; ?>')"
                                    title="Toggle Status">
                                    <i class="fas fa-toggle-<?php echo ($status === 'active') ? 'on' : 'off'; ?>"></i>
                                </button>

                                <?php if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== (int)$u['id']): ?>
                                <button type="button" class="btn btn-outline-danger"
                                    onclick="openDeleteModal(<?php echo (int)$u['id']; ?>, '<?php echo addslashes($u['name']); ?>')"
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Hidden forms to submit POST -->
<form id="deleteForm" method="post" class="d-none">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteFormId" value="">
</form>

<form id="toggleForm" method="post" class="d-none">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="id" id="toggleFormId" value="">
</form>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel"><i
                        class="fas fa-exclamation-triangle text-warning me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user "<strong id="deleteUserName"></strong>"?</p>
                <p class="text-muted mb-0">This action cannot be undone and will permanently remove the account.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><i
                        class="fas fa-trash me-1"></i>Delete User</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel"><i class="fas fa-toggle-on text-warning me-2"></i>Toggle
                    User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="statusAction"></span> the user "<strong
                        id="statusUserName"></strong>"?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmStatusBtn"><i
                        class="fas fa-toggle-on me-1"></i>Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
// Filters
document.getElementById('searchInput').addEventListener('input', filterUsers);
document.getElementById('roleFilter').addEventListener('change', filterUsers);
document.getElementById('statusFilter').addEventListener('change', filterUsers);

function filterUsers() {
    const searchTerm = (document.getElementById('searchInput').value || '').toLowerCase();
    const roleFilter = (document.getElementById('roleFilter').value || '').toLowerCase();
    const statusFilter = (document.getElementById('statusFilter').value || '').toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr[data-user-name]');

    rows.forEach(row => {
        const userName = row.getAttribute('data-user-name');
        const userEmail = row.getAttribute('data-email');
        const role = row.getAttribute('data-role');
        const status = row.getAttribute('data-status');

        let show = true;
        if (searchTerm && !userName.includes(searchTerm) && !userEmail.includes(searchTerm)) show = false;
        if (roleFilter && role !== roleFilter) show = false;
        if (statusFilter && status !== statusFilter) show = false;

        row.style.display = show ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('statusFilter').value = '';
    filterUsers();
}

// Modal wiring -> submit hidden POST forms
function openDeleteModal(id, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteFormId').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    document.getElementById('deleteForm').submit();
});

function openToggleModal(id, name, currentStatus) {
    const actionWord = currentStatus === 'active' ? 'deactivate' : 'activate';
    document.getElementById('statusAction').textContent = actionWord;
    document.getElementById('statusUserName').textContent = name;
    document.getElementById('toggleFormId').value = id;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}
document.getElementById('confirmStatusBtn').addEventListener('click', function() {
    document.getElementById('toggleForm').submit();
});

// Auto-dismiss alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => (new bootstrap.Alert(el)).close());
}, 5000);
</script>

<?php include 'includes/footer.php'; ?>