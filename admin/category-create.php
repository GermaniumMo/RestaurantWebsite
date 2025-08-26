<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/security.php'; 

require_role('admin');

$page_title = 'Create New Category';
$current_page = 'categories';

$errors = [];
$form_data = [
    'name' => '',
    'description' => '',
    'display_order' => 0,
    'is_active' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $form_data = [
        'name' => sanitize_input($_POST['name'] ?? ''),
        'description' => sanitize_input($_POST['description'] ?? ''),
        'display_order' => (int)($_POST['display_order'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    
    if (empty($form_data['name'])) {
        $errors['name'] = 'Category name is required.';
    } elseif (strlen($form_data['name']) > 100) {
        $errors['name'] = 'Category name cannot exceed 100 characters.';
    }
    
    if (empty($errors)) {
        try {
            $category_id = db_insert(
                "INSERT INTO categories (name, description, display_order, is_active, created_at) VALUES (?, ?, ?, ?, NOW())",
                [
                    $form_data['name'],
                    $form_data['description'],
                    $form_data['display_order'],
                    $form_data['is_active']
                ],
                'ssii'
            );
            
            if ($category_id) {
                flash('success', 'Category created successfully!');
                header('Location: category-list.php');
                exit;
            } else {
                $errors['general'] = 'Failed to create category. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred while creating the category.';
        }
    }
}

include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Create New Category</h3>
        <a href="category-list.php" class="btn btn-outline-secondary">
            <i class="me-2">←</i> Back to Categories
        </a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <?= csrf_field() ?>
        
        <div class="col-md-8">
            <label for="name" class="form-label">Category Name *</label>
            <input type="text" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
                   id="name" name="name" value="<?= htmlspecialchars($form_data['name']) ?>" required>
            <?php if (!empty($errors['name'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <label for="display_order" class="form-label">Display Order</label>
            <input type="number" class="form-control" id="display_order" name="display_order" 
                   value="<?= htmlspecialchars($form_data['display_order']) ?>" min="0">
            <div class="form-text">Lower numbers appear first</div>
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" 
                      placeholder="Describe this category..."><?= htmlspecialchars($form_data['description']) ?></textarea>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                       <?= $form_data['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">
                    Active Category
                </label>
                <div class="form-text">Inactive categories won't be shown to customers</div>
            </div>
        </div>

        <div class="col-12">
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Category</button>
                <a href="category-list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php include 'shared/footer.php'; ?>
