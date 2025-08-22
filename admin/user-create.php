<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/validation.php';

// Require admin role
require_role('admin');

$page_title = 'Create New User';
$current_page = 'users';

$errors = [];
$form_data = [
    'name' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => '',
    'role' => 'customer',
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $form_data = [
        'name' => sanitize_input($_POST['name'] ?? ''),
        'email' => sanitize_input($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
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
        // Check if email already exists
        $existing_user = db_fetch_one("SELECT id FROM users WHERE email = ?", [$form_data['email']], 's');
        if ($existing_user) {
            $errors['email'] = 'This email address is already registered.';
        }
    }
    
    if (empty($form_data['password'])) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($form_data['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }
    
    if ($form_data['password'] !== $form_data['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    
    if (!in_array($form_data['role'], ['admin', 'customer'])) {
        $errors['role'] = 'Invalid role selected.';
    }
    
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
            
            $user_id = db_insert(
                "INSERT INTO users (name, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $form_data['name'],
                    $form_data['email'],
                    $hashed_password,
                    $form_data['role'],
                    $form_data['is_active']
                ],
                'ssssi'
            );
            
            if ($user_id) {
                flash('success', 'User created successfully!');
                header('Location: user-list.php');
                exit;
            } else {
                $errors['general'] = 'Failed to create user. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred while creating the user.';
        }
    }
}

include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Create New User</h3>
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
            <label for="password" class="form-label">Password *</label>
            <input type="password" class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>" 
                   id="password" name="password" required>
            <div class="form-text">Minimum 6 characters</div>
            <?php if (!empty($errors['password'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="confirm_password" class="form-label">Confirm Password *</label>
            <input type="password" class="form-control <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                   id="confirm_password" name="confirm_password" required>
            <?php if (!empty($errors['confirm_password'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
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
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="user-list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php include 'shared/footer.php'; ?>
