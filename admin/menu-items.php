<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../classes/MenuItem.php';
require_once '../includes/pagination.php';
require_admin_login();

$database = new Database();
$db = $database->getConnection();
$menuItem = new MenuItem($db);

$message = '';
$error = '';

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 10;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $menuItem->name = sanitize_input($_POST['name']);
                $menuItem->description = sanitize_input($_POST['description']);
                $menuItem->price = sanitize_input($_POST['price']);
                $menuItem->category = sanitize_input($_POST['category']);
                $menuItem->image_url = sanitize_input($_POST['image_url']);
                $menuItem->rating = sanitize_input($_POST['rating']);
                $menuItem->review_count = sanitize_input($_POST['review_count']);
                $menuItem->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if ($menuItem->create()) {
                    $message = 'Menu item created successfully!';
                } else {
                    $error = 'Failed to create menu item.';
                }
                break;
                
            case 'update':
                $menuItem->id = sanitize_input($_POST['id']);
                $menuItem->name = sanitize_input($_POST['name']);
                $menuItem->description = sanitize_input($_POST['description']);
                $menuItem->price = sanitize_input($_POST['price']);
                $menuItem->category = sanitize_input($_POST['category']);
                $menuItem->image_url = sanitize_input($_POST['image_url']);
                $menuItem->rating = sanitize_input($_POST['rating']);
                $menuItem->review_count = sanitize_input($_POST['review_count']);
                $menuItem->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if ($menuItem->update()) {
                    $message = 'Menu item updated successfully!';
                } else {
                    $error = 'Failed to update menu item.';
                }
                break;
                
            case 'delete':
                $menuItem->id = sanitize_input($_POST['id']);
                if ($menuItem->delete()) {
                    $message = 'Menu item deleted successfully!';
                } else {
                    $error = 'Failed to delete menu item.';
                }
                break;
        }
    }
}

// Get total count and create pagination
$total_count = $menuItem->getTotalCountAll();
$pagination = new Pagination($total_count, $items_per_page, $page);

// Get paginated menu items
$stmt = $menuItem->readAllPaginated($pagination->getLimit(), $pagination->getOffset());
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Items - Savoria Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .admin-header {
            background-color: #111827;
            color: white;
            padding: 1rem 0;
        }
        .admin-sidebar {
            background-color: #1f2937;
            min-height: calc(100vh - 76px);
            padding: 2rem 0;
        }
        .admin-sidebar .nav-link {
            color: #9ca3af;
            padding: 0.75rem 1.5rem;
            border-radius: 0;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #ea580c;
            color: white;
        }
        .admin-title {
            font-family: "Cormorant Garamond", serif;
            font-size: 2rem;
            font-weight: 700;
            color: #ea580c;
        }
        .menu-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        .pagination-info {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0" style="color: #ea580c; font-family: 'Cormorant Garamond', serif;">Savoria Admin</h1>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['admin_username']; ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 admin-sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="menu-items.php">
                            <i class="bi bi-card-list"></i> Menu Items
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php">
                            <i class="bi bi-calendar-check"></i> Reservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="bi bi-envelope"></i> Messages
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="admin-title">Menu Items</h1>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuItemModal" style="background-color: #ea580c; border-color: #ea580c;">
                            <i class="bi bi-plus"></i> Add Menu Item
                        </button>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Pagination Info -->
                    <div class="pagination-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo $pagination->getRecordInfo(); ?></strong>
                            </div>
                            <div>
                                Page <?php echo $pagination->getCurrentPage(); ?> of <?php echo $pagination->getTotalPages(); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($menu_items)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <h5>No menu items found</h5>
                                                    <p class="text-muted">Add your first menu item to get started.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($menu_items as $item): ?>
                                                <tr>
                                                    <td>
                                                        <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                             class="menu-item-image">
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 50)) . '...'; ?></small>
                                                    </td>
                                                    <td><?php echo ucfirst(str_replace('_', ' ', $item['category'])); ?></td>
                                                    <td><?php echo format_price($item['price']); ?></td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-star-fill"></i> <?php echo $item['rating']; ?> (<?php echo $item['review_count']; ?>)
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $item['is_active'] ? 'success' : 'secondary'; ?>">
                                                            <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="editMenuItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this menu item?')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination Links -->
                    <?php if ($pagination->getTotalPages() > 1): ?>
                        <div class="mt-4">
                            <?php echo $pagination->generatePaginationLinks('menu-items.php'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Menu Item Modal -->
    <div class="modal fade" id="addMenuItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="starters">Starters</option>
                                        <option value="main_course">Main Course</option>
                                        <option value="desserts">Desserts</option>
                                        <option value="beverages">Beverages</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating</label>
                                    <input type="number" step="0.1" min="0" max="5" class="form-control" id="rating" name="rating" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="review_count" class="form-label">Review Count</label>
                                    <input type="number" class="form-control" id="review_count" name="review_count" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="text" class="form-control" id="image_url" name="image_url">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #ea580c; border-color: #ea580c;">Add Menu Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Menu Item Modal -->
    <div class="modal fade" id="editMenuItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <select class="form-select" id="edit_category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="starters">Starters</option>
                                        <option value="main_course">Main Course</option>
                                        <option value="desserts">Desserts</option>
                                        <option value="beverages">Beverages</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_price" class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_rating" class="form-label">Rating</label>
                                    <input type="number" step="0.1" min="0" max="5" class="form-control" id="edit_rating" name="rating">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_review_count" class="form-label">Review Count</label>
                                    <input type="number" class="form-control" id="edit_review_count" name="review_count">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_url" class="form-label">Image URL</label>
                            <input type="text" class="form-control" id="edit_image_url" name="image_url">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #ea580c; border-color: #ea580c;">Update Menu Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editMenuItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_description').value = item.description;
            document.getElementById('edit_price').value = item.price;
            document.getElementById('edit_category').value = item.category;
            document.getElementById('edit_image_url').value = item.image_url;
            document.getElementById('edit_rating').value = item.rating;
            document.getElementById('edit_review_count').value = item.review_count;
            document.getElementById('edit_is_active').checked = item.is_active == 1;
            
            new bootstrap.Modal(document.getElementById('editMenuItemModal')).show();
        }
    </script>
</body>
</html>
