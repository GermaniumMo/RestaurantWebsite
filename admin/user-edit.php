<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validation.php';

// Check if user is admin
if (!is_logged_in() || !has_role('admin')) {
    header('Location: ../auth/login.php');
    exit;
}

$db = get_db_connection();
$user_id = intval($_GET['id'] ?? 0);

if (!$user_id) {
    flash_set('error', 'Invalid user ID.');
    header('Location: user-list.php');
    exit;
}

// Get user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    flash_set('error', 'User not found.');
    header('Location: user-list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        header('Location: user-edit.php?id=' . $user_id);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');

    $errors = [];

    // Validate input
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (!in_array($role, ['admin', 'customer'])) {
        $errors[] = 'Invalid role selected.';
    }

    // Check if email is already taken by another user
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = 'Email is already registered to another user.';
    }

    // Validate password if provided
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
    }

    if (empty($errors)) {
        try {
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, role = ?, is_active = ?, password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $email, $role, $is_active, $hashed_password, $user_id]);
            } else {
                // Update without changing password
                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, role = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $email, $role, $is_active, $user_id]);
            }

            flash_set('success', 'User updated successfully.');
            header('Location: user-list.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error updating user. Please try again.';
        }
    }

    if (!empty($errors)) {
        flash_set('error', implode('<br>', $errors));
    }
}

include 'shared/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Edit User</h2>
                <a href="user-list.php" class="btn btn-secondary">Back to Users</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <?php // Fixed CSRF field generation to use correct function name ?>
                                <?= csrf_field() ?>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active User
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                                    <div class="form-text">Minimum 8 characters required if changing password.</div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Update User</button>
                                        <a href="user-list.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'shared/footer.php'; ?>
