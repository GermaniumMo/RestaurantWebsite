<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/csrf.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: auth/login.php');
    exit;
}

$db = db();
$user = current_user();

$user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user['id']], 'i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        header('Location: profile.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Name is required.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }

        $existing_user = db_fetch_one("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user['id']], 'si');
        if ($existing_user) {
            $errors[] = 'Email is already registered to another user.';
        }

        if (empty($errors)) {
            try {
                db_execute("UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?", [$name, $email, $user['id']], 'ssi');

                flash('success', 'Profile updated successfully.');
                header('Location: profile.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Error updating profile. Please try again.';
            }
        }

        if (!empty($errors)) {
            flash('error', implode('<br>', $errors));
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($current_password)) {
            $errors[] = 'Current password is required.';
        } elseif (!password_verify($current_password, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        }

        if (empty($new_password)) {
            $errors[] = 'New password is required.';
        } elseif (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        }

        if ($new_password !== $confirm_password) {
            $errors[] = 'Password confirmation does not match.';
        }

        if (empty($errors)) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                db_execute("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?", [$hashed_password, $user['id']], 'si');

                flash('success', 'Password changed successfully.');
                header('Location: profile.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Error changing password. Please try again.';
            }
        }

        if (!empty($errors)) {
            flash('error', implode('<br>', $errors));
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Savoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header class="d-flex top-header w-100 position-relative">
        <div class="d-flex justify-content-between py-3 w-100 header-container">
            <h1><a href="index.php" style="color: white; text-decoration: none;">Savoria</a></h1>
            <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
                <li><a href="index.php" style="color: white; text-decoration: none">Home</a></li>
                <li><a href="menu.php" style="color: white; text-decoration: none">Menu</a></li>
                <li><a href="about.php" style="color: white; text-decoration: none">About</a></li>
                <li><a href="contact.php" style="color: white; text-decoration: none">Contact</a></li>
            </ul>
            <div class="d-flex gap-3">
                <div class="dropdown">
                    <button class="btn btn-Reserve dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <?= htmlspecialchars($user['name']) ?>
                    </button>
                    <ul class="dropdown-menu">
                        <?php if (has_role('admin')): ?>
                            <li><a class="dropdown-item" href="admin/index.php">Admin Dashboard</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                        <li><a class="dropdown-item" href="reservations.php">My Reservations</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="auth/logout.php" class="d-inline">
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container" style="margin-top: 120px;">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">My Profile</h2>
                
                <?php 
                flash_show_all(); 
                ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Account Type</label>
                                        <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" value="<?= date('F j, Y', strtotime($user['created_at'])) ?>" readonly>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="form-text">Minimum 8 characters required.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>

                                    <button type="submit" class="btn btn-warning">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
