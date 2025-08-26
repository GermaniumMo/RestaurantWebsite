<?php
    define('ADMIN_PAGE', true);

    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/flash.php';
    require_once __DIR__ . '/../includes/validation.php';
    require_once __DIR__ . '/../includes/security.php';
    require_once __DIR__ . '/../includes/csrf.php';

    require_role('admin');

    $page_title    = 'Menu Items';
    $page_subtitle = 'Manage your restaurant menu';
    $current_page  = 'menu';

    $page            = max(1, (int) ($_GET['page'] ?? 1));
    $search          = sanitize_input($_GET['search'] ?? '');
    $category_filter = (int) ($_GET['category'] ?? 0);
    $status_filter   = $_GET['status'] ?? '';
    $limit           = ITEMS_PER_PAGE;
    $offset          = ($page - 1) * $limit;

    $where_conditions = [];
    $params           = [];
    $types            = '';

    if (! empty($search)) {
        $where_conditions[] = "(m.name LIKE ? OR m.description LIKE ?)";
        $search_param       = "%$search%";
        $params[]           = $search_param;
        $params[]           = $search_param;
        $types .= 'ss';
    }

    if ($category_filter > 0) {
        $where_conditions[] = "m.category_id = ?";
        $params[]           = $category_filter;
        $types .= 'i';
    }

    if ($status_filter === 'available') {
        $where_conditions[] = "m.is_available = 1";
    } elseif ($status_filter === 'unavailable') {
        $where_conditions[] = "m.is_available = 0";
    }

    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    $count_sql   = "SELECT COUNT(*) as total FROM menu_items m $where_clause";
    $total_items = db_fetch_one($count_sql, $params, $types)['total'];
    $total_pages = max(1, (int) ceil($total_items / $limit));

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

    $categories = db_fetch_all("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");

    include 'shared/header.php';
?>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0" style="font-family: 'Cormorant Garamond', serif;">Menu Items</h3>
            <small class="text-muted"><?php echo number_format($total_items) ?> total items</small>
        </div>
        <a href="menu-create.php" class="btn btn-primary">
            <i class="me-2">➕</i> Add Menu Item
        </a>
    </div>

    <?php flash_show('success'); ?>
<?php flash_show('error'); ?>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search menu items..."
                   value="<?php echo htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id'] ?>"<?php echo $category_filter == $category['id'] ? 'selected' : '' ?>>
                        <?php echo htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="available"                                                                                   <?php echo $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="unavailable"                                                                                       <?php echo $status_filter === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
        </div>
    </form>

    <?php if (empty($menu_items)): ?>
        <div class="text-center py-5">
            <p class="text-muted">No menu items found.</p>
            <a href="menu-create.php" class="btn btn-primary">Add Your First Menu Item</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Order</th>
                        <th style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($menu_items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($item['image_url']): ?>
                                   <img src="<?php echo '../' . htmlspecialchars($item['image_url']); ?>"
         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         class="rounded me-3" style="width:50px;height:50px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                         style="width:50px;height:50px;">🍽️</div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name']) ?></strong>
                                    <?php if ($item['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(mb_substr($item['description'], 0, 60)) ?><?php echo mb_strlen($item['description']) > 60 ? '…' : '' ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php echo $item['category_name']
                                ? '<span class="badge bg-secondary">' . htmlspecialchars($item['category_name']) . '</span>'
                            : '<span class="text-muted">No Category</span>' ?>
                        </td>
                        <td><strong>$<?php echo number_format((float) $item['price'], 2) ?></strong></td>
                        <td>
                            <span class="badge bg-<?php echo $item['is_available'] ? 'success' : 'danger' ?>">
                                <?php echo $item['is_available'] ? 'Available' : 'Unavailable' ?>
                            </span>
                        </td>
                        <td><?php echo $item['is_featured'] ? '<span class="badge bg-warning">Featured</span>' : '' ?></td>
                        <td><small class="text-muted"><?php echo (int) $item['display_order'] ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="menu-edit.php?id=<?php echo (int) $item['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                <button type="button" class="btn btn-outline-danger"
                                        onclick="confirmDelete(<?php echo (int) $item['id'] ?>, '<?php echo htmlspecialchars($item['name'], ENT_QUOTES) ?>')">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Menu items pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1 ?>&search=<?php echo urlencode($search) ?>&category=<?php echo $category_filter ?>&status=<?php echo urlencode($status_filter) ?>">Previous</a>
                        </li>
                    <?php endif; ?>
<?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item<?php echo $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?php echo $i ?>&search=<?php echo urlencode($search) ?>&category=<?php echo $category_filter ?>&status=<?php echo urlencode($status_filter) ?>"><?php echo $i ?></a>
                        </li>
                    <?php endfor; ?>
<?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1 ?>&search=<?php echo urlencode($search) ?>&category=<?php echo $category_filter ?>&status=<?php echo urlencode($status_filter) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
<?php endif; ?>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="menu-delete.php">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
          <p class="text-muted mb-0">This action cannot be undone.</p>
          <input type="hidden" name="id" id="deleteItemId">
          <?php echo csrf_field() ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
function confirmDelete(id, name) {
  document.getElementById('deleteItemId').value = id;
  document.getElementById('deleteItemName').textContent = name;
  var m = new bootstrap.Modal(document.getElementById('deleteModal'));
  m.show();
}
</script>

<?php include 'shared/footer.php'; ?>