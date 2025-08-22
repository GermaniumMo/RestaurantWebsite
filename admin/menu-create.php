<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/validation.php';

// Require admin role
require_role('admin');

$page_title = 'Add Menu Item';
$page_subtitle = 'Create a new menu item';
$current_page = 'menu';

$errors = [];
$form_data = [
    'name' => '',
    'description' => '',
    'price' => '',
    'category_id' => '',
    'image_url' => '',
    'is_available' => 1,
    'is_featured' => 0,
    'display_order' => 0
];

// Get categories
$categories = db_fetch_all("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $form_data = [
        'name' => sanitize_input($_POST['name'] ?? ''),
        'description' => sanitize_input($_POST['description'] ?? ''),
        'price' => sanitize_input($_POST['price'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'image_url' => sanitize_input($_POST['image_url'] ?? ''),
        'is_available' => isset($_POST['is_available']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'display_order' => (int)($_POST['display_order'] ?? 0)
    ];
    
    // Validation
    if (empty($form_data['name'])) {
        $errors['name'] = 'Menu item name is required.';
    } elseif (strlen($form_data['name']) > 150) {
        $errors['name'] = 'Menu item name cannot exceed 150 characters.';
    }
    
    if (empty($form_data['price'])) {
        $errors['price'] = 'Price is required.';
    } elseif (!is_numeric($form_data['price']) || $form_data['price'] < 0) {
        $errors['price'] = 'Price must be a valid positive number.';
    } elseif ($form_data['price'] > 999.99) {
        $errors['price'] = 'Price cannot exceed $999.99.';
    }
    
    if ($form_data['category_id'] > 0) {
        $category_exists = db_fetch_one("SELECT id FROM categories WHERE id = ? AND is_active = 1", [$form_data['category_id']], 'i');
        if (!$category_exists) {
            $errors['category_id'] = 'Selected category does not exist.';
        }
    }
    
    if (!empty($form_data['image_url']) && !filter_var($form_data['image_url'], FILTER_VALIDATE_URL)) {
        $errors['image_url'] = 'Image URL must be a valid URL.';
    }
    
    if (empty($errors)) {
        try {
            $menu_item_id = db_insert(
    "INSERT INTO menu_items (category_id, name, description, price, image_url, is_available, is_featured, display_order, created_at, updated_at) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
    [
        $form_data['category_id'] ?: null,
        $form_data['name'],
        $form_data['description'],
        $form_data['price'],
        $form_data['image_url'] ?: null,
        $form_data['is_available'],
        $form_data['is_featured'],
        $form_data['display_order']
    ],
    'issdsiii' // <-- Changed from 'issdsiiii' to 'issdsiii' (8 types)
);

            
            if ($menu_item_id) {
                flash('success', 'Menu item created successfully!');
                header('Location: menu-list.php');
                exit;
            } else {
                $errors['general'] = 'Failed to create menu item. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred while creating the menu item.';
        }
    }
}

include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Add Menu Item</h3>
        <a href="menu-list.php" class="btn btn-outline-secondary">
            <i class="me-2">←</i> Back to Menu
        </a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <?= csrf_field() ?>
        
        <div class="col-md-8">
            <label for="name" class="form-label">Menu Item Name *</label>
            <input type="text" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" 
                   id="name" name="name" value="<?= htmlspecialchars($form_data['name']) ?>" required>
            <?php if (!empty($errors['name'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <label for="price" class="form-label">Price *</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" min="0" max="999.99" 
                       class="form-control <?= !empty($errors['price']) ? 'is-invalid' : '' ?>" 
                       id="price" name="price" value="<?= htmlspecialchars($form_data['price']) ?>" required>
                <?php if (!empty($errors['price'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" 
                      placeholder="Describe this delicious menu item..."><?= htmlspecialchars($form_data['description']) ?></textarea>
        </div>

        <div class="col-md-6">
            <label for="category_id" class="form-label">Category</label>
            <select class="form-select <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>" 
                    id="category_id" name="category_id">
                <option value="">No Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" 
                            <?= $form_data['category_id'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['category_id'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['category_id']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="display_order" class="form-label">Display Order</label>
            <input type="number" class="form-control" id="display_order" name="display_order" 
                   value="<?= htmlspecialchars($form_data['display_order']) ?>" min="0">
            <div class="form-text">Lower numbers appear first</div>
        </div>

        <div class="col-12">
            <label for="image_url" class="form-label">Image URL</label>
            <input type="url" class="form-control <?= !empty($errors['image_url']) ? 'is-invalid' : '' ?>" 
                   id="image_url" name="image_url" value="<?= htmlspecialchars($form_data['image_url']) ?>" 
                   placeholder="https://example.com/image.jpg">
            <?php if (!empty($errors['image_url'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($errors['image_url']) ?></div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_available" name="is_available" 
                               <?= $form_data['is_available'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_available">
                            Available for ordering
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                               <?= $form_data['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">
                            Featured item (show on homepage)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Menu Item</button>
                <a href="menu-list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php include 'shared/footer.php'; ?>
