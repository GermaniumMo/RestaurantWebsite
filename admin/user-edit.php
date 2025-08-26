<?php
    define('ADMIN_PAGE', true);
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/flash.php';
    require_once __DIR__ . '/../includes/csrf.php';
    require_once __DIR__ . '/../includes/validation.php';

    if (! is_logged_in() || ! has_role('admin')) {
        header('Location: ../auth/login.php');
        exit;
    }

    $user_id = intval($_GET['id'] ?? 0);
    if (! $user_id) {
        flash('error', 'Invalid user ID.');
        header('Location: user-list.php');
        exit;
    }

    $user = db_fetch_one("SELECT * FROM users WHERE id = ?", [$user_id], "i");
    if (! $user) {
        flash('error', 'User not found.');
        header('Location: user-list.php');
        exit;
    }

    $form_name      = $user['name'];
    $form_email     = $user['email'];
    $form_role      = $user['role'];
    $form_is_active = $user['is_active'] ? 1 : 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf()) {
            flash('error', 'Invalid security token.');
            header('Location: user-edit.php?id=' . $user_id);
            exit;
        }

        $form_name      = trim($_POST['name'] ?? $form_name);
        $form_email     = trim($_POST['email'] ?? $form_email);
        $form_role      = $_POST['role'] ?? $form_role;
        $form_is_active = isset($_POST['is_active']) ? 1 : 0;
        $password       = trim($_POST['password'] ?? '');

        $errors = [];

        if (empty($form_name)) {
            $errors[] = 'Name is required.';
        }

        if (empty($form_email) || ! filter_var($form_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }

        if (! in_array($form_role, ['admin', 'user'])) {
            $errors[] = 'Invalid role selected.';
        }

        if (strtolower($form_email) !== strtolower($user['email'])) {
            $existing = db_fetch_one(
                "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1",
                [$form_email, $user_id],
                "si"
            );
            if ($existing) {
                $errors[] = 'Email is already registered to another user: ';
            }
        }

        if (! empty($password) && strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        if (empty($errors)) {
            try {
                if (! empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql             = "UPDATE users
                           SET name = ?, email = ?, role = ?, is_active = ?, password_hash = ?, updated_at = NOW()
                           WHERE id = ?";
                    $params = [$form_name, $form_email, $form_role, $form_is_active, $hashed_password, $user_id];
                    $types  = "sssisi";
                } else {
                    $sql = "UPDATE users
                           SET name = ?, email = ?, role = ?, is_active = ?, updated_at = NOW()
                           WHERE id = ?";
                    $params = [$form_name, $form_email, $form_role, $form_is_active, $user_id];
                    $types  = "sssii";
                }

                $rows = db_execute($sql, $params, $types);

                if ($rows === false) {
                    throw new Exception("Database execution failed.");
                }

                if ($rows === 0) {
                    flash('info', 'No changes detected.');
                } else {
                    flash('success', 'User updated successfully.');
                }

                header('Location: user-list.php');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }

        if (! empty($errors)) {
            flash('error', implode('<br>', $errors));
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
                            <?php flash_show_all(); ?>

                            <form method="POST">
                                <?php echo csrf_field(); ?>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="<?php echo htmlspecialchars($form_name); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars($form_email); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="user"                                                                                                                                                                                     <?php echo $form_role === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin"                                                                                                                                                                                        <?php echo $form_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>

                                <div class="mb-3 form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                           <?php echo $form_is_active ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active User</label>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Leave blank to keep current password">
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
