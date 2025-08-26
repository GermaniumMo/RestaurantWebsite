<?php
function render_admin_document_head($title = 'Admin', $additional_css = []) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - Savoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/main.css">
    <?php foreach ($additional_css as $css_file): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css_file) ?>" />
    <?php endforeach; ?>
    <style>
        .admin-sidebar {
            background-color: #111827;
            min-height: 100vh;
            padding-top: 2rem;
        }
        .admin-content {
            background-color: #faf9f7;
            min-height: 100vh;
            padding: 2rem;
        }
        .admin-nav-link {
            color: #9ca3af;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            display: block;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
            font-family: "Cormorant Garamond", serif;
            font-size: 1.1rem;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            background-color: #ea580c;
            color: white;
        }
        .admin-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
        }
        .btn-primary {
            background-color: #ea580c;
            border-color: #ea580c;
        }
        .btn-primary:hover {
            background-color: #c2410c;
            border-color: #c2410c;
        }
    </style>
</head>
<body>
<?php
}

function render_admin_header($page_title = 'Admin Panel', $page_subtitle = '', $current_page = '') {
    $user = current_user();
?>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="px-3">
                    <h2 class="text-white mb-4" style="font-family: 'Cormorant Garamond', serif;">
                        <a href="../index.php" style="color: #ea580c; text-decoration: none;">Savoria</a>
                        <small class="d-block text-muted" style="font-size: 0.8rem;">Admin Panel</small>
                    </h2>
                    
                    <nav class="nav flex-column">
                        <a href="index.php" class="admin-nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                            <i class="me-2">📊</i> Dashboard
                        </a>
                        <a href="menu-list.php" class="admin-nav-link <?= $current_page === 'menu' ? 'active' : '' ?>">
                            <i class="me-2">🍽️</i> Menu Items
                        </a>
                        <a href="reservation-list.php" class="admin-nav-link <?= $current_page === 'reservations' ? 'active' : '' ?>">
                            <i class="me-2">📅</i> Reservations
                        </a>
                        <a href="user-list.php" class="admin-nav-link <?= $current_page === 'users' ? 'active' : '' ?>">
                            <i class="me-2">👥</i> Users
                        </a>
                        <a href="category-list.php" class="admin-nav-link <?= $current_page === 'categories' ? 'active' : '' ?>">
                            <i class="me-2">📂</i> Categories
                        </a>
                        <hr class="my-3" style="border-color: #374151;">
                        <a href="../index.php" class="admin-nav-link">
                            <i class="me-2">🏠</i> Back to Site
                        </a>
                        <form method="POST" action="../auth/logout.php" class="mt-2">
                            <button type="submit" class="admin-nav-link border-0 bg-transparent w-100 text-start">
                                <i class="me-2">🚪</i> Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 admin-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 style="font-family: 'Cormorant Garamond', serif; color: #111827;">
                            <?= htmlspecialchars($page_title) ?>
                        </h1>
                        <?php if ($page_subtitle): ?>
                            <p class="text-muted"><?= htmlspecialchars($page_subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted">
                        Welcome, <?= htmlspecialchars($user['name']) ?>
                    </div>
                </div>

                <?php flash_show_all(); ?>

<?php
}

function render_admin_footer($extra_js = '') {
?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <?php if ($extra_js): ?>
        <?= $extra_js ?>
    <?php endif; ?>
</body>
</html>
<?php
}

function render_admin_card($title, $content, $actions = '') {
?>
    <div class="admin-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 style="font-family: 'Cormorant Garamond', serif; color: #111827; margin: 0;">
                <?= htmlspecialchars($title) ?>
            </h3>
            <?php if ($actions): ?>
                <div><?= $actions ?></div>
            <?php endif; ?>
        </div>
        <?= $content ?>
    </div>
<?php
}

function render_admin_stats_card($title, $value, $icon, $color = 'primary') {
?>
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="admin-card text-center">
            <div class="mb-3" style="font-size: 2rem;"><?= $icon ?></div>
            <h4 class="text-<?= $color ?>" style="font-family: 'Cormorant Garamond', serif;">
                <?= htmlspecialchars($value) ?>
            </h4>
            <p class="text-muted mb-0"><?= htmlspecialchars($title) ?></p>
        </div>
    </div>
<?php
}

function render_admin_table($headers, $rows, $actions_column = true) {
?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <?php foreach ($headers as $header): ?>
                        <th><?= htmlspecialchars($header) ?></th>
                    <?php endforeach; ?>
                    <?php if ($actions_column): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row['data'] as $cell): ?>
                            <td><?= $cell ?></td>
                        <?php endforeach; ?>
                        <?php if ($actions_column && isset($row['actions'])): ?>
                            <td><?= $row['actions'] ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
