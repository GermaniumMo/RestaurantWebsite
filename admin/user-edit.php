<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/validation.php';

// Require admin role
require_role('admin');

$page_title = 'Edit User';
$current_page = 'users';

$user_id = (int)($_GET['id'] ?? 0);
if (!$user_id) {
    flash('error', 'Invalid user ID.');
    header('Location: user-list.php');
    exit;
}

// Get user
$user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user_id], 'i');
if (!$user) {
    flash('error', 'User not found.');
    header('Location: user-list.php');
    exit;
}

$page_subtitle = 'Edit: ' . $user['name'];

$errors = [];
$form_data = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $form_data = [
        'name' => sanitize_input($_POST['name'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'role' => sanitize_input($_POST['role'] ?? 'customer'),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    // Validation
    if (empty($form_data['name'])) {
        $errors['name'] = 'Name is required.';
    } elseif (strlen($form_data['name']) > 100) {
        $errors['name'] = 'Name cannot exceed 100 characters.';
    }
    
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        // Check if email already exists for another user
        $existing_user = db_fetch_one("SELECT id FROM users WHERE email = ? AND id != ?", [$form_data['email'], $user_id], 'si');
        if ($existing_user) {
            $errors['email'] = 'This email address is already registered to another user.';
        }
    }
    
    if (!in_array($form_data['role'], ['admin', 'customer'])) {
        $errors['role'] = 'Invalid role selected.';
    }
    
    // Validate password if provided
    if (!empty($form_data['password']) && strlen($form_data['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }
    
    if (empty($errors)) {
        try {
            if (!empty($form_data['password'])) {
                // Update with new password
                $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
                $affected_rows = db_execute(
                    "UPDATE users SET name = ?, email = ?, password = ?, role = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                    [
                        $form_data['name'],
                        $form_data['email'],
                        $hashed_password,
                        $form_data['role'],
                        $form_data['is_active'],
                        $user_id
                    ],
                    'ssssii'
                );
            } else {
                // Update without changing password
                $affected_rows = db_execute(
                    "UPDATE users SET name = ?, email = ?, role = ?, is_active = ?, updated_at = NOW() WHERE id = ?",
                    [
                        $form_data['name'],
                        $form_data['email'],
                        $form_data['role'],
                        $form_data['is_active'],
                        $user_id
                    ],
                    'sssii'
                );
            }
            
            if ($affected_rows >= 0) {
                flash('success', 'User updated successfully!');
                header('Location: user-list.php');
                exit;
            } else {
                $errors['general'] = 'Failed to update user. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred while updating the user.';
        }
    }
}

include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Edit User</h3>
        <a href="user-list.php" class="btn btn-outline-secondary">
            <i class="me-2">←</i> Back to Users
        </a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <?= csrf_field() ?>
        
        <div class="col-md-6">
            <label for="name" class="form-label">Full Name *</label>
            <input type="text" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
                   id="name" name="name" value="<?= htmlspecialchars($form_data['name']) ?>" required>
            <?php if (!empty($errors['name'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email Address *</label>
            <input type="email" class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>" 
                   id="email" name="email" value="<?= htmlspecialchars($form_data['email']) ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="role" class="form-label">Role *</label>
            <select class="form-select <?= !empty($errors['role']) ? 'is-invalid' : '' ?>" 
                    id="role" name="role" required>
                <option value="customer" <?= $form_data['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                <option value="admin" <?= $form_data['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <?php if (!empty($errors['role'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['role']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                       <?= $form_data['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">
                    Active Account
                </label>
                <div class="form-text">Inactive users cannot log in</div>
            </div>
        </div>

        <div class="col-12">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" 
                   id="password" name="password" placeholder="Leave blank to keep current password">
            <div class="form-text">Minimum 6 characters required if changing password</div>
            <?php if (!empty($errors['password'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="user-list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php include 'shared/footer.php'; ?>
