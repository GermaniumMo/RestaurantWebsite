<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/flash.php';

// Check if user is admin
if (!is_logged_in() || !has_role('admin')) {
    header('Location: ../auth/login.php');
    exit;
}

$page_title = 'Category Management';
$current_page = 'categories';

// Handle search and filtering
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "name LIKE ?";
    $params[] = "%$search%";
}

if ($status_filter !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = (int)$status_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$count_sql = "SELECT COUNT(*) as count FROM categories $where_clause";
$count_result = db_fetch_one($count_sql, $params);
$total_categories = $count_result['count'];
$total_pages = ceil($total_categories / $per_page);

$sql = "SELECT * FROM categories $where_clause ORDER BY display_order ASC, name ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$categories = db_fetch_all($sql, $params);

include 'shared/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Category Management</h2>
                <a href="category-create.php" class="btn btn-primary">Add New Category</a>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search categories..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="category-list.php" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No categories found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Display Order</th>
                                        <th>Status</th>
                                        <th>Menu Items</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <?php
                                        // Get menu items count for this category
                                        $menu_count = db_fetch_one("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?", [$category['id']], 'i');
                                        ?>
                                        <tr>
                                            <td><?= $category['id'] ?></td>
                                            <td><?= htmlspecialchars($category['name']) ?></td>
                                            <td><?= htmlspecialchars(substr($category['description'] ?? '', 0, 50)) ?><?= strlen($category['description'] ?? '') > 50 ? '...' : '' ?></td>
                                            <td><?= $category['display_order'] ?></td>
                                            <td>
                                                <span class="badge <?= $category['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td><?= $menu_count['count'] ?> items</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="category-edit.php?id=<?= $category['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Categories pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
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
                <p>Are you sure you want to delete category <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-warning">This will also remove the category from all menu items.</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="category-delete.php" class="d-inline">
                    <input type="hidden" name="category_id" id="deleteCategoryId">
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(categoryId, categoryName) {
    document.getElementById('deleteCategoryId').value = categoryId;
    document.getElementById('deleteCategoryName').textContent = categoryName;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include 'shared/footer.php'; ?>
