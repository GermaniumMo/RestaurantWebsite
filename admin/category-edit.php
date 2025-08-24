<?php
    define('ADMIN_PAGE', true);
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/flash.php';
    require_once __DIR__ . '/../includes/csrf.php';
    require_once __DIR__ . '/../includes/validation.php';

    // Check admin
    if (! is_logged_in() || ! has_role('admin')) {
        header('Location: ../auth/login.php');
        exit;
    }

    // Get category ID
    $category_id = (int) ($_GET['id'] ?? 0);
    if (! $category_id) {
        flash('error', 'Invalid category ID.');
        header('Location: category-list.php');
        exit;
    }

    // Fetch category
    $category = db_fetch_one("SELECT * FROM categories WHERE id = ?", [$category_id], 'i');
    if (! $category) {
        flash('error', 'Category not found.');
        header('Location: category-list.php');
        exit;
    }

    // Initialize form values
    $form_name        = $category['name'];
    $form_description = $category['description'] ?? '';
    $form_display     = (int) $category['display_order'];
    $form_is_active   = (int) $category['is_active'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! verify_csrf()) {
            flash('error', 'Invalid security token.');
            header('Location: category-edit.php?id=' . $category_id);
            exit;
        }

        // POST values
        $form_name        = trim($_POST['name'] ?? $form_name);
        $form_description = trim($_POST['description'] ?? $form_description);
        $form_display     = (int) ($_POST['display_order'] ?? $form_display);
        $form_is_active   = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];

        // Validation
        if (empty($form_name)) {
            $errors[] = 'Name is required.';
        }

        // Check for duplicate name (ignore current category)
        $existing = db_fetch_one(
            "SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1",
            [$form_name, $category_id],
            "si"
        );
        if ($existing) {
            $errors[] = 'Another category already has this name.';
        }

        if (empty($errors)) {
            // Update category
            $sql = "UPDATE categories
                   SET name = ?, description = ?, display_order = ?, is_active = ?, updated_at = NOW()
                   WHERE id = ?";
            $params = [$form_name, $form_description, $form_display, $form_is_active, $category_id];
            $types  = "ssiii";

            try {
                $rows = db_execute($sql, $params, $types);

                if ($rows === false) {
                    throw new Exception("Database execution failed.");
                }

                if ($rows === 0) {
                    flash('info', 'No changes detected.');
                } else {
                    flash('success', 'Category updated successfully.');
                }

                header('Location: category-list.php');
                exit;

            } catch (Exception $e) {
                flash('error', 'Database error: ' . $e->getMessage());
            }
        } else {
            flash('error', implode('<br>', $errors));
        }
    }

    include 'shared/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Edit Category</h2>
                <a href="category-list.php" class="btn btn-secondary">Back to Categories</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <?php flash_show_all(); ?>

                            <form method="POST">
                                <?php echo csrf_field(); ?>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="<?php echo htmlspecialchars($form_name); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($form_description); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="display_order" name="display_order"
                                           value="<?php echo $form_display; ?>" required>
                                </div>

                                <div class="mb-3 form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                           <?php echo $form_is_active ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Category</button>
                                <a href="category-list.php" class="btn btn-secondary">Cancel</a>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'shared/footer.php'; ?>
