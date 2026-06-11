<?php
/**
 * admin/users.php — CRUD for users
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth('manage_users');

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Load roles for dropdowns
$db->prepare('SELECT id, display_name FROM roles ORDER BY id');
$roles = $db->fetchAll();
$roleOptions = [];
foreach ($roles as $r) { $roleOptions[$r['id']] = $r['display_name']; }

// --- Handle POST (create / update) ---
if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.');
        redirect(url('/admin/users'));
    }

    $email      = trim((string)post('email', ''));
    $username   = trim((string)post('username', ''));
    $firstName  = trim((string)post('first_name', ''));
    $lastName   = trim((string)post('last_name', ''));
    $roleId     = (int)post('role_id', 3);
    $status     = post('status', 'active');
    $password   = (string)post('password', '');
    $editingId  = (int)post('id', 0);

    if (!Security::validateEmail($email)) {
        flash('danger', 'Invalid email address.');
        redirect(url('/admin/users?action=' . ($editingId ? 'edit&id=' . $editingId : 'new')));
    }
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
        flash('danger', 'Username must be 3-50 chars (letters, digits, _ . -).');
        redirect(url('/admin/users?action=' . ($editingId ? 'edit&id=' . $editingId : 'new')));
    }

    if ($editingId > 0) {
        // Update
        $sql = 'UPDATE users SET email=:email, username=:username, first_name=:first_name, last_name=:last_name, role_id=:role_id, status=:status';
        if ($password !== '') {
            if (strlen($password) < 8) {
                flash('danger', 'Password must be at least 8 characters.');
                redirect(url('/admin/users?action=edit&id=' . $editingId));
            }
            $sql .= ', password=:password';
        }
        $sql .= ' WHERE id=:id';
        $db->prepare($sql);
        $db->bind(':email', $email);
        $db->bind(':username', $username);
        $db->bind(':first_name', $firstName);
        $db->bind(':last_name', $lastName);
        $db->bind(':role_id', $roleId);
        $db->bind(':status', $status);
        $db->bind(':id', $editingId);
        if ($password !== '') $db->bind(':password', Security::hashPassword($password));
        $db->execute();
        flash('success', 'User updated.');
    } else {
        // Create
        if (strlen($password) < 8) {
            flash('danger', 'Password must be at least 8 characters.');
            redirect(url('/admin/users?action=new'));
        }
        $db->prepare('INSERT INTO users (email, username, password, first_name, last_name, role_id, status) VALUES (:email,:username,:password,:first_name,:last_name,:role_id,:status)');
        $db->bind(':email', $email);
        $db->bind(':username', $username);
        $db->bind(':password', Security::hashPassword($password));
        $db->bind(':first_name', $firstName);
        $db->bind(':last_name', $lastName);
        $db->bind(':role_id', $roleId);
        $db->bind(':status', $status);
        if ($db->execute()) {
            flash('success', 'User created.');
            // Send verification email if requested
            if (post('send_verification') === '1') {
                $newId = (int)$db->lastInsertId();
                (new Auth())->sendEmailVerification($newId);
            }
        } else {
            flash('danger', 'Could not create user (email/username may already exist).');
        }
    }
    redirect(url('/admin/users'));
}

// --- Handle delete (GET ?action=delete&id=X&csrf=...) ---
if ($action === 'delete' && $id > 0) {
    if (!Security::verifyCSRFToken($_GET['csrf'] ?? '')) {
        flash('danger', 'Invalid request token.');
        redirect(url('/admin/users'));
    }
    $u = getUser();
    if ((int)$u['id'] === $id) {
        flash('warning', 'You cannot delete your own account.');
        redirect(url('/admin/users'));
    }
    $db->prepare('DELETE FROM users WHERE id=:id');
    $db->bind(':id', $id);
    $db->execute();
    flash('success', 'User deleted.');
    redirect(url('/admin/users'));
}

// --- Edit form ---
if ($action === 'edit' && $id > 0) {
    $db->prepare('SELECT * FROM users WHERE id=:id');
    $db->bind(':id', $id);
    $user = $db->fetch();
    if (!$user) { flash('danger', 'User not found.'); redirect(url('/admin/users')); }
    admin_layout_start('Edit User', 'users');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
        <?php
        admin_field('Email', 'email', $user['email'], 'email', ['required' => true]);
        admin_field('Username', 'username', $user['username'], 'text', ['required' => true]);
        admin_field('First name', 'first_name', $user['first_name'] ?? '');
        admin_field('Last name', 'last_name', $user['last_name'] ?? '');
        admin_field('Role', 'role_id', $user['role_id'], 'select', ['options' => $roleOptions, 'required' => true]);
        admin_field('Status', 'status', $user['status'], 'select', [
            'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'],
            'required' => true,
        ]);
        admin_field('New password', 'password', '', 'password', ['help' => 'Leave blank to keep current password. Min 8 chars.']);
        ?>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> <span>Save changes</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/users'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end();
    return;
}

// --- New form ---
if ($action === 'new' || $action === 'create') {
    admin_layout_start('New User', 'users');
    ?>
    <form method="POST" class="card card-body">
        <?php echo Security::getCSRFField(); ?>
        <?php
        admin_field('Email', 'email', '', 'email', ['required' => true]);
        admin_field('Username', 'username', '', 'text', ['required' => true]);
        admin_field('First name', 'first_name');
        admin_field('Last name', 'last_name');
        admin_field('Role', 'role_id', 3, 'select', ['options' => $roleOptions, 'required' => true]);
        admin_field('Status', 'status', 'active', 'select', [
            'options' => ['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'],
            'required' => true,
        ]);
        admin_field('Password', 'password', '', 'password', ['required' => true, 'help' => 'Minimum 8 characters.']);
        ?>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="send_verification" name="send_verification" value="1" checked>
            <label class="form-check-label" for="send_verification">Send email-verification link to this user</label>
        </div>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus" aria-hidden="true"></i> <span>Create user</span></button>
            <a class="btn btn-outline-secondary" href="<?php echo url('/admin/users'); ?>">Cancel</a>
        </div>
    </form>
    <?php
    admin_layout_end();
    return;
}

// --- List ---
$db->prepare('SELECT u.*, r.display_name AS role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC');
$users = $db->fetchAll();
$csrf = Security::generateCSRFToken();

admin_layout_start('Users', 'users');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Total: <?php echo count($users); ?></p>
    <a class="btn btn-primary" href="<?php echo url('/admin/users?action=new'); ?>">
        <i class="fas fa-user-plus" aria-hidden="true"></i> <span>New user</span>
    </a>
</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover align-middle">
    <thead><tr><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $row): ?>
        <tr>
            <td><?php echo e(trim($row['first_name'] . ' ' . $row['last_name'])); ?></td>
            <td><?php echo e($row['email']); ?></td>
            <td><?php echo e($row['username']); ?></td>
            <td><span class="badge bg-primary"><?php echo e($row['role_name']); ?></span></td>
            <td><?php echo status_badge($row['status']); ?></td>
            <td class="text-end">
                <div class="admin-table-actions justify-content-end">
                    <a class="btn btn-sm btn-outline-primary icon-btn" href="<?php echo url('/admin/users?action=edit&id=' . (int)$row['id']); ?>" aria-label="Edit user <?php echo e($row['email']); ?>">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-sm btn-outline-danger icon-btn"
                       href="<?php echo url('/admin/users?action=delete&id=' . (int)$row['id'] . '&csrf=' . urlencode($csrf)); ?>"
                       aria-label="Delete user <?php echo e($row['email']); ?>"
                       onclick="return confirm('Delete this user? This cannot be undone.');">
                        <i class="fas fa-trash" aria-hidden="true"></i>
                    </a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div></div>
<?php
admin_layout_end();
