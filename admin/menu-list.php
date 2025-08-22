<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/security.php'; // Added missing security.php include for sanitize_input function

// Require admin role
require_role('admin');

$page_title = 'Menu Items';
$page_subtitle = 'Manage your restaurant menu';
$current_page = 'menu';

// Pagination and search
$page = max(1, (int)($_GET['page'] ?? 1));
$search = sanitize_input($_GET['search'] ?? '');
$category_filter = (int)($_GET['category'] ?? 0);
$status_filter = $_GET['status'] ?? '';
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(m.name LIKE ? OR m.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($category_filter > 0) {
    $where_conditions[] = "m.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

if ($status_filter === 'available') {
    $where_conditions[] = "m.is_available = 1";
} elseif ($status_filter === 'unavailable') {
    $where_conditions[] = "m.is_available = 0";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM menu_items m $where_clause";
$total_items = db_fetch_one($count_sql, $params, $types)['total'];
$total_pages = ceil($total_items / $limit);

// Get menu items
$sql = "SELECT m.*, c.name as category_name 
        FROM menu_items m 
        LEFT JOIN categories c ON m.category_id = c.id 
        $where_clause 
        ORDER BY m.display_order ASC, m.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$menu_items = db_fetch_all($sql, $params, $types);

// Get categories for filter
$categories = db_fetch_all("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");

include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Menu Items</h3>
            <small class="text-muted"><?= number_format($total_items) ?> total items</small>
        </div>
        <a href="menu-create.php" class="btn btn-primary">
            <i class="me-2">➕</i> Add Menu Item
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search menu items..." 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="unavailable" <?= $status_filter === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Menu Items Table -->
    <?php if (empty($menu_items)): ?>
        <div class="text-center py-5">
            <p class="text-muted">No menu items found.</p>
            <a href="menu-create.php" class="btn btn-primary">Add Your First Menu Item</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i>🍽️</i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <?php if ($item['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($item['description'], 0, 60)) ?>...</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($item['category_name']): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($item['category_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">No Category</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong>$<?= number_format($item['price'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $item['is_available'] ? 'success' : 'danger' ?>">
                                    <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($item['is_featured']): ?>
                                    <span class="badge bg-warning">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?= $item['display_order'] ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="menu-edit.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>')">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Menu items pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&status=<?= urlencode($status_filter) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&status=<?= urlencode($status_filter) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>&status=<?= urlencode($status_filter) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="menu-delete.php" class="d-inline">
                    <input type="hidden" name="id" id="deleteItemId">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '
<script>
function confirmDelete(id, name) {
    document.getElementById("deleteItemId").value = id;
    document.getElementById("deleteItemName").textContent = name;
    new bootstrap.Modal(document.getElementById("deleteModal")).show();
}
</script>
';

include 'shared/footer.php';
?>
