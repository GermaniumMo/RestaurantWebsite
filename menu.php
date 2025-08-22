<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/flash.php';

if (!is_logged_in()) {
    // Allow viewing menu but restrict ordering functionality
    $can_order = false;
} else {
    $can_order = true;
}

// Get categories and menu items
$categories = db_fetch_all("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");
$menu_items = db_fetch_all(
    "SELECT m.*, c.name as category_name 
     FROM menu_items m 
     LEFT JOIN categories c ON m.category_id = c.id 
     WHERE m.is_available = 1 
     ORDER BY c.display_order ASC, m.display_order ASC, m.name ASC"
);

// Group menu items by category
$menu_by_category = [];
foreach ($menu_items as $item) {
    $category_name = $item['category_name'] ?: 'Other';
    $menu_by_category[$category_name][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Savoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header class="d-flex top-header w-100 position-absolute left-0">
        <div class="d-flex justify-content-between py-3 w-100 header-container">
            <h1><a href="index.php" style="color: white; text-decoration: none;">Savoria</a></h1>
            <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
                <li><a href="index.php" style="color: white; text-decoration: none">Home</a></li>
                <li><a href="menu.php" style="color: white; text-decoration: none" class="active">Menu</a></li>
                <li><a href="about.php" style="color: white; text-decoration: none">About</a></li>
                <li><a href="contact.php" style="color: white; text-decoration: none">Contact</a></li>
            </ul>
            <div class="d-flex gap-3">
                <?php if (is_logged_in()): ?>
                    <?php $user = current_user(); ?>
                    <div class="dropdown">
                        <button class="btn btn-Reserve dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                <?php else: ?>
                    <a class="btn btn-Reserve" href="auth/register.php">Sign Up</a>
                    <a class="btn btn-order" href="auth/login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="introduction-section">
        <div class="introduction-container">
            <div class="text-center">
                <h1>Our Menu</h1>
                <p>Discover our carefully crafted dishes made with the finest ingredients</p>
            </div>
        </div>
    </section>

    <!-- Flash Messages -->
    <?php flash_show_all(); ?>

    <!-- Menu Categories Navigation -->
    <?php if (!empty($categories)): ?>
        <section class="btn-container">
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <button class="btn btn-menu btn-menu-active" onclick="showAllCategories()">All</button>
                <?php foreach ($categories as $category): ?>
                    <button class="btn btn-menu" onclick="showCategory('<?= htmlspecialchars($category['name']) ?>')">
                        <?= htmlspecialchars($category['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Menu Items -->
    <section class="container-menu">
        <?php if (empty($menu_items)): ?>
            <div class="text-center py-5">
                <h3>Menu Coming Soon</h3>
                <p class="text-muted">We're working on our delicious menu. Please check back soon!</p>
            </div>
        <?php else: ?>
            <?php foreach ($menu_by_category as $category_name => $items): ?>
                <div class="menu-category mb-5" data-category="<?= htmlspecialchars($category_name) ?>">
                    <h2 class="text-center mb-4" style="font-family: 'Cormorant Garamond', serif; color: #ea580c;">
                        <?= htmlspecialchars($category_name) ?>
                    </h2>
                    
                    <div class="row g-4">
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                             class="card-img-top menu-card-img-top" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top menu-card-img-top bg-light d-flex align-items-center justify-content-center">
                                            <i style="font-size: 3rem;">🍽️</i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        <?php if ($item['description']): ?>
                                            <p class="card-text flex-grow-1"><?= htmlspecialchars($item['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <span class="text-primary fw-bold" style="font-size: 1.25rem;">
                                                $<?= number_format($item['price'], 2) ?>
                                            </span>
                                            <?php if ($item['is_featured']): ?>
                                                <span class="badge bg-warning">Featured</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Conditional cart functionality based on login status -->
                                        <div class="mt-3">
                                            <?php if ($can_order): ?>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <button class="btn btn-outline-secondary btn-sm" onclick="decreaseQuantity(<?= $item['id'] ?>)">-</button>
                                                        <input type="number" id="quantity-<?= $item['id'] ?>" class="form-control mx-2 text-center" 
                                                               style="width: 60px;" value="1" min="1" max="10">
                                                        <button class="btn btn-outline-secondary btn-sm" onclick="increaseQuantity(<?= $item['id'] ?>)">+</button>
                                                    </div>
                                                    <button class="btn btn-primary" onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', <?= $item['price'] ?>)">
                                                        Add to Cart
                                                    </button>
                                                </div>
                                                <button class="btn btn-outline-info btn-sm mt-2 w-100" onclick="viewDetails(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['description'], ENT_QUOTES) ?>', <?= $item['price'] ?>, '<?= htmlspecialchars($item['image_url']) ?>')">
                                                    View Details
                                                </button>
                                            <?php else: ?>
                                                <div class="text-center p-3 border rounded bg-light">
                                                    <p class="mb-2 text-muted">Want to order this item?</p>
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <a href="auth/login.php" class="btn btn-primary btn-sm">Login to Order</a>
                                                        <a href="auth/register.php" class="btn btn-outline-primary btn-sm">Sign Up</a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="d-flex w-100 contactFooter-container justify-content-center align-items-center">
        <div class="w-100 d-flex flex-column contactFooter-innContainer justify-content-center align-items-center">
            <div class="d-flex w-100 justify-content-center restaurant-info">
                <div class="d-flex flex-column gap-3" style="width: 288px">
                    <h1 class="title-info">Savoria</h1>
                    <p class="description-info">Experience the art of fine dining in an elegant atmosphere.</p>
                </div>
                <div class="col-3 col-md-3 mb-3">
                    <h5 class="contact-title">Contact</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <svg width="12" height="16" viewBox="0 0 12 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.74062 15.6C8.34375 13.5938 12 8.73125 12 6C12 2.6875 9.3125 0 6 0C2.6875 0 0 2.6875 0 6C0 8.73125 3.65625 13.5938 5.25938 15.6C5.64375 16.0781 6.35625 16.0781 6.74062 15.6ZM6 4C6.53043 4 7.03914 4.21071 7.41421 4.58579C7.78929 4.96086 8 5.46957 8 6C8 6.53043 7.78929 7.03914 7.41421 7.41421C7.03914 7.78929 6.53043 8 6 8C5.46957 8 4.96086 7.78929 4.58579 7.41484C4.21071 7.03914 4 6.53043 4 6C4 5.46957 4.21071 4.96086 4.58579 4.58579C4.96086 4.21071 5.46957 4 6 4Z" fill="#9CA3AF" />
                            </svg>
                            123 Gourmet Street
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" class="nav-link p-0 text-body-secondary">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.15312 0.768722C4.9125 0.187472 4.27812 -0.121903 3.67188 0.0437222L0.921875 0.793722C0.378125 0.943722 0 1.43747 0 1.99997C0 9.73122 6.26875 16 14 16C14.5625 16 15.0563 15.6218 15.2063 15.0781L15.9563 12.3281C16.1219 11.7218 15.8125 11.0875 15.2312 10.8468L12.2312 9.59685C11.7219 9.38435 11.1313 9.53122 10.7844 9.95935L9.52188 11.5C7.32188 10.4593 5.54062 8.6781 4.5 6.4781L6.04063 5.21872C6.46875 4.86872 6.61562 4.28122 6.40312 3.77185L5.15312 0.771847V0.768722Z" fill="#9CA3AF" />
                                </svg>
                                (555) 123-4567
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" class="nav-link p-0 text-body-secondary">
                                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1.5 0C0.671875 0 0 0.671875 0 1.5C0 1.97187 0.221875 2.41562 0.6 2.7L7.4 7.8C7.75625 8.06563 8.24375 8.06563 8.6 7.8L15.4 2.7C15.7781 2.41562 16 1.97187 16 1.5C16 0.671875 15.3281 0 14.5 0H1.5ZM0 3.5V10C0 11.1031 0.896875 12 2 12H14C15.1031 12 16 11.1031 16 10V3.5L9.2 8.6C8.4875 9.13438 7.5125 9.13438 6.8 8.6L0 3.5Z" fill="#9CA3AF" />
                                </svg>
                                info@savoria.com
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-3 col-md-3 mb-3">
                    <h5 class="contact-title">Hours</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">Mon-Thu: 11:00 am - 10:00 pm</li>
                        <li class="nav-item mb-2">Fri-Sat: 11:00 am - 11:00 pm</li>
                        <li class="nav-item mb-2">Sun: 11:00 am - 9:00 pm</li>
                    </ul>
                </div>
                <div class="d-flex flex-column">
                    <h5 class="contact-title">Follow Us</h5>
                    <ul class="list-unstyled d-flex">
                        <li class="ms-3">
                            <a class="link-body-emphasis" href="#">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.6875 10C19.6875 4.64844 15.3516 0.3125 10 0.3125C4.64844 0.3125 0.3125 4.64844 0.3125 10C0.3125 14.8352 3.85508 18.843 8.48633 19.5703V12.8004H6.02539V10H8.48633V7.86562C8.48633 5.43789 9.93164 4.09687 12.1453 4.09687C13.2055 4.09687 14.3141 4.28594 14.3141 4.28594V6.66875H13.0922C11.8891 6.66875 11.5137 7.41562 11.5137 8.18164V10H14.2004L13.7707 12.8004H11.5137V19.5703C16.1449 18.843 19.6875 14.8352 19.6875 10Z" fill="#9CA3AF" />
                                </svg>
                            </a>
                        </li>
                        <li class="ms-3">
                            <a class="link-body-emphasis" href="#">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.75391 4.50781C6.26953 4.50781 4.26562 6.51172 4.26562 8.99609C4.26562 11.4805 6.26953 13.4844 8.75391 13.4844C11.2383 13.4844 13.2422 11.4805 13.2422 8.99609C13.2422 6.51172 11.2383 4.50781 8.75391 4.50781ZM8.75391 11.9141C7.14844 11.9141 5.83594 10.6055 5.83594 8.99609C5.83594 7.38672 7.14453 6.07812 8.75391 6.07812C10.3633 6.07812 11.6719 7.38672 11.6719 8.99609C11.6719 10.6055 10.3594 11.9141 8.75391 11.9141ZM14.4727 4.32422C14.4727 4.90625 14.0039 5.37109 13.4258 5.37109C12.8438 5.37109 12.3789 4.90234 12.3789 4.32422C12.3789 3.74609 12.8477 3.27734 13.4258 3.27734C14.0039 3.27734 14.4727 3.74609 14.4727 4.32422ZM17.4453 5.38672C17.3789 3.98438 17.0586 2.74219 16.0312 1.71875C15.0078 0.695312 13.7656 0.375 12.3633 0.304687C10.918 0.222656 6.58594 0.222656 5.14062 0.304687C3.74219 0.371094 2.5 0.691406 1.47266 1.71484C0.445313 2.73828 0.128906 3.98047 0.0585937 5.38281C-0.0234375 6.82812 -0.0234375 11.1602 0.0585937 12.6055C0.125 14.0078 0.445313 15.25 1.47266 16.2734C2.5 17.2969 3.73828 17.6172 5.14062 17.6875C6.58594 17.7695 10.918 17.7695 12.3633 17.6875C13.7656 17.6211 15.0078 17.3008 16.0312 16.2734C17.0547 15.25 17.375 14.0078 17.4453 12.6055C17.5273 11.1602 17.5273 6.83203 17.4453 5.38672ZM15.5781 14.1562C15.2734 14.9219 14.6836 15.5117 13.9141 15.8203C12.7617 16.2773 10.0273 16.1719 8.75391 16.1719C7.48047 16.1719 4.74219 16.2734 3.59375 15.8203C2.82812 15.5156 2.23828 14.9258 1.92969 14.1562C1.47266 13.0039 1.57813 10.2695 1.57813 8.99609C1.57813 7.72266 1.47656 4.98438 1.92969 3.83594C2.23438 3.07031 2.82422 2.48047 3.59375 2.17187C4.74609 1.71484 7.48047 1.82031 8.75391 1.82031C10.0273 1.82031 12.7656 1.71875 13.9141 2.17187C14.6797 2.47656 15.2695 3.06641 15.5781 3.83594C16.0352 4.98828 15.9297 7.72266 15.9297 8.99609C15.9297 10.2695 16.0352 13.0078 15.5781 14.1562Z" fill="#9CA3AF" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top w-100 justify-content-center align-items-center">
                <p class="text-center w-100 footer-copyright">&copy; 2024 Company, Inc. All rights reserved.</p>
            </div>
        </div>
    </section>

    <!-- Conditional cart sidebar and floating button for logged-in users only -->
    <?php if ($can_order): ?>
        <!-- Cart Sidebar -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="cartSidebarLabel">Your Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div id="cartItems"></div>
                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex justify-content-between">
                        <strong>Total: $<span id="cartTotal">0.00</span></strong>
                    </div>
                    <button class="btn btn-success w-100 mt-3" onclick="proceedToCheckout()" id="checkoutBtn" disabled>
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>

        <!-- Item Details Modal -->
        <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemDetailsModalLabel">Item Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <img id="modalItemImage" src="/placeholder.svg" alt="" class="img-fluid rounded">
                            </div>
                            <div class="col-md-6">
                                <h4 id="modalItemName"></h4>
                                <p id="modalItemDescription" class="text-muted"></p>
                                <h5 class="text-primary">$<span id="modalItemPrice"></span></h5>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="decreaseModalQuantity()">-</button>
                                        <input type="number" id="modalQuantity" class="form-control mx-2 text-center" 
                                               style="width: 60px;" value="1" min="1" max="10">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="increaseModalQuantity()">+</button>
                                    </div>
                                    <button class="btn btn-primary w-100 mt-3" onclick="addToCartFromModal()">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Cart Button -->
        <div class="position-fixed bottom-0 end-0 m-4" style="z-index: 1000;">
            <button class="btn btn-primary rounded-circle p-3" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" 
                    style="width: 60px; height: 60px; position: relative;">
                🛒
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount" style="display: none;">
                    0
                </span>
            </button>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <?php if ($can_order): ?>
        <!-- Only load cart JavaScript for logged-in users -->
        <script>
            let cart = JSON.parse(localStorage.getItem('savoriaCart')) || [];
            let currentModalItem = null;

            // Update cart display
            function updateCartDisplay() {
                const cartItems = document.getElementById('cartItems');
                const cartTotal = document.getElementById('cartTotal');
                const cartCount = document.getElementById('cartCount');
                const checkoutBtn = document.getElementById('checkoutBtn');
                
                if (cart.length === 0) {
                    cartItems.innerHTML = '<p class="text-muted">Your cart is empty</p>';
                    cartTotal.textContent = '0.00';
                    cartCount.style.display = 'none';
                    checkoutBtn.disabled = true;
                    return;
                }
                
                let total = 0;
                let itemCount = 0;
                cartItems.innerHTML = '';
                
                cart.forEach((item, index) => {
                    total += item.price * item.quantity;
                    itemCount += item.quantity;
                    
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'cart-item mb-3 p-2 border rounded';
                    itemDiv.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${item.name}</h6>
                                <small class="text-muted">$${item.price.toFixed(2)} each</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${index})">×</button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-outline-secondary btn-sm" onclick="updateCartQuantity(${index}, ${item.quantity - 1})">-</button>
                                <span class="mx-2">${item.quantity}</span>
                                <button class="btn btn-outline-secondary btn-sm" onclick="updateCartQuantity(${index}, ${item.quantity + 1})">+</button>
                            </div>
                            <strong>$${(item.price * item.quantity).toFixed(2)}</strong>
                        </div>
                    `;
                    cartItems.appendChild(itemDiv);
                });
                
                cartTotal.textContent = total.toFixed(2);
                cartCount.textContent = itemCount;
                cartCount.style.display = itemCount > 0 ? 'block' : 'none';
                checkoutBtn.disabled = false;
                
                localStorage.setItem('savoriaCart', JSON.stringify(cart));
            }

            // Quantity controls
            function increaseQuantity(itemId) {
                const input = document.getElementById(`quantity-${itemId}`);
                if (input.value < 10) {
                    input.value = parseInt(input.value) + 1;
                }
            }

            function decreaseQuantity(itemId) {
                const input = document.getElementById(`quantity-${itemId}`);
                if (input.value > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            }

            function increaseModalQuantity() {
                const input = document.getElementById('modalQuantity');
                if (input.value < 10) {
                    input.value = parseInt(input.value) + 1;
                }
            }

            function decreaseModalQuantity() {
                const input = document.getElementById('modalQuantity');
                if (input.value > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            }

            // Add to cart
            function addToCart(itemId, itemName, itemPrice) {
                const quantity = parseInt(document.getElementById(`quantity-${itemId}`).value);
                const existingItem = cart.find(item => item.id === itemId);
                
                if (existingItem) {
                    existingItem.quantity += quantity;
                } else {
                    cart.push({
                        id: itemId,
                        name: itemName,
                        price: itemPrice,
                        quantity: quantity
                    });
                }
                
                updateCartDisplay();
                
                // Show success message
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Added!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-primary');
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.add('btn-primary');
                    btn.classList.remove('btn-success');
                }, 1000);
            }

            function addToCartFromModal() {
                if (currentModalItem) {
                    const quantity = parseInt(document.getElementById('modalQuantity').value);
                    const existingItem = cart.find(item => item.id === currentModalItem.id);
                    
                    if (existingItem) {
                        existingItem.quantity += quantity;
                    } else {
                        cart.push({
                            id: currentModalItem.id,
                            name: currentModalItem.name,
                            price: currentModalItem.price,
                            quantity: quantity
                        });
                    }
                    
                    updateCartDisplay();
                    
                    // Close modal and show success
                    const modal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
                    modal.hide();
                }
            }

            // Cart management
            function removeFromCart(index) {
                cart.splice(index, 1);
                updateCartDisplay();
            }

            function updateCartQuantity(index, newQuantity) {
                if (newQuantity <= 0) {
                    removeFromCart(index);
                } else {
                    cart[index].quantity = newQuantity;
                    updateCartDisplay();
                }
            }

            // View details
            function viewDetails(itemId, itemName, itemDescription, itemPrice, itemImage) {
                currentModalItem = { id: itemId, name: itemName, price: itemPrice };
                
                document.getElementById('modalItemName').textContent = itemName;
                document.getElementById('modalItemDescription').textContent = itemDescription;
                document.getElementById('modalItemPrice').textContent = itemPrice.toFixed(2);
                document.getElementById('modalQuantity').value = 1;
                
                const modalImage = document.getElementById('modalItemImage');
                if (itemImage) {
                    modalImage.src = itemImage;
                    modalImage.alt = itemName;
                } else {
                    modalImage.src = '/placeholder.svg?height=300&width=300';
                    modalImage.alt = itemName;
                }
                
                const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
                modal.show();
            }

            // Checkout
            function proceedToCheckout() {
                if (cart.length === 0) return;
                
                // Show checkout form modal
                showCheckoutModal();
            }

            function showCheckoutModal() {
                const modalHtml = `
                    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="checkoutForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Customer Information</h6>
                                                <div class="mb-3">
                                                    <label for="customerName" class="form-label">Full Name *</label>
                                                    <input type="text" class="form-control" id="customerName" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="customerEmail" class="form-label">Email *</label>
                                                    <input type="email" class="form-control" id="customerEmail" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="customerPhone" class="form-label">Phone *</label>
                                                    <input type="tel" class="form-control" id="customerPhone" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Order Details</h6>
                                                <div class="mb-3">
                                                    <label for="orderType" class="form-label">Order Type *</label>
                                                    <select class="form-select" id="orderType" required onchange="toggleDeliveryAddress()">
                                                        <option value="pickup">Pickup</option>
                                                        <option value="delivery">Delivery</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3" id="deliveryAddressGroup" style="display: none;">
                                                    <label for="deliveryAddress" class="form-label">Delivery Address *</label>
                                                    <textarea class="form-control" id="deliveryAddress" rows="3"></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="orderNotes" class="form-label">Special Instructions</label>
                                                    <textarea class="form-control" id="orderNotes" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border-top pt-3">
                                            <h6>Order Summary</h6>
                                            <div id="checkoutSummary"></div>
                                            <div class="d-flex justify-content-between mt-3">
                                                <strong>Total: $<span id="checkoutTotal">0.00</span></strong>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-success" onclick="submitOrder()" id="submitOrderBtn">
                                        Place Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('checkoutModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Populate order summary
                updateCheckoutSummary();
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
                modal.show();
            }

            function toggleDeliveryAddress() {
                const orderType = document.getElementById('orderType').value;
                const deliveryGroup = document.getElementById('deliveryAddressGroup');
                const deliveryAddress = document.getElementById('deliveryAddress');
                
                if (orderType === 'delivery') {
                    deliveryGroup.style.display = 'block';
                    deliveryAddress.required = true;
                } else {
                    deliveryGroup.style.display = 'none';
                    deliveryAddress.required = false;
                }
            }

            function updateCheckoutSummary() {
                const summaryDiv = document.getElementById('checkoutSummary');
                const totalSpan = document.getElementById('checkoutTotal');
                
                let total = 0;
                let summaryHtml = '';
                
                cart.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    summaryHtml += `
                        <div class="d-flex justify-content-between">
                            <span>${item.name} x ${item.quantity}</span>
                            <span>$${itemTotal.toFixed(2)}</span>
                        </div>
                    `;
                });
                
                summaryDiv.innerHTML = summaryHtml;
                totalSpan.textContent = total.toFixed(2);
            }

            async function submitOrder() {
                const form = document.getElementById('checkoutForm');
                const submitBtn = document.getElementById('submitOrderBtn');
                
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                try {
                    console.log('[v0] Starting checkout process...');
                    
                    // Get CSRF token
                    console.log('[v0] Fetching CSRF token...');
                    const csrfResponse = await fetch('includes/csrf.php?action=get_token');
                    console.log('[v0] CSRF response status:', csrfResponse.status);
                    
                    if (!csrfResponse.ok) {
                        throw new Error('Failed to get CSRF token');
                    }
                    
                    const csrfData = await csrfResponse.json();
                    console.log('[v0] CSRF token received:', csrfData);
                    
                    const orderData = {
                        cart: cart,
                        customer_name: document.getElementById('customerName').value,
                        customer_email: document.getElementById('customerEmail').value,
                        customer_phone: document.getElementById('customerPhone').value,
                        order_type: document.getElementById('orderType').value,
                        delivery_address: document.getElementById('deliveryAddress').value,
                        notes: document.getElementById('orderNotes').value,
                        csrf_token: csrfData.token
                    };
                    
                    console.log('[v0] Sending order data:', orderData);
                    
                    const response = await fetch('process_checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(orderData)
                    });
                    
                    console.log('[v0] Checkout response status:', response.status);
                    console.log('[v0] Checkout response headers:', response.headers);
                    
                    const responseText = await response.text();
                    console.log('[v0] Raw response:', responseText);
                    
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('[v0] JSON parse error:', parseError);
                        throw new Error('Invalid response format: ' + responseText);
                    }
                    
                    console.log('[v0] Parsed result:', result);
                    
                    if (result.success) {
                        // Clear cart
                        cart = [];
                        localStorage.removeItem('savoriaCart');
                        updateCartDisplay();
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                        modal.hide();
                        
                        // Show success message
                        alert(`Order placed successfully! Order ID: ${result.order_id}\nTotal: $${result.total_amount.toFixed(2)}`);
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('[v0] Checkout error:', error);
                    alert('An error occurred while processing your order. Please try again.\nError: ' + error.message);
                } finally {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Place Order';
                }
            }

            // Existing functions
            function showAllCategories() {
                document.querySelectorAll('.menu-category').forEach(category => {
                    category.style.display = 'block';
                });
                
                // Update active button
                document.querySelectorAll('.btn-menu').forEach(btn => {
                    btn.classList.remove('btn-menu-active');
                });
                event.target.classList.add('btn-menu-active');
            }

            function showCategory(categoryName) {
                document.querySelectorAll('.menu-category').forEach(category => {
                    if (category.dataset.category === categoryName) {
                        category.style.display = 'block';
                    } else {
                        category.style.display = 'none';
                    }
                });
                
                // Update active button
                document.querySelectorAll('.btn-menu').forEach(btn => {
                    btn.classList.remove('btn-menu-active');
                });
                event.target.classList.add('btn-menu-active');
            }

            // Initialize cart display on page load
            document.addEventListener('DOMContentLoaded', function() {
                updateCartDisplay();
            });
        </script>
    <?php else: ?>
        <!-- Simplified script for guest users -->
        <script>
            // Existing functions for category filtering
            function showAllCategories() {
                document.querySelectorAll('.menu-category').forEach(category => {
                    category.style.display = 'block';
                });
                
                // Update active button
                document.querySelectorAll('.btn-menu').forEach(btn => {
                    btn.classList.remove('btn-menu-active');
                });
                event.target.classList.add('btn-menu-active');
            }

            function showCategory(categoryName) {
                document.querySelectorAll('.menu-category').forEach(category => {
                    if (category.dataset.category === categoryName) {
                        category.style.display = 'block';
                    } else {
                        category.style.display = 'none';
                    }
                });
                
                // Update active button
                document.querySelectorAll('.btn-menu').forEach(btn => {
                    btn.classList.remove('btn-menu-active');
                });
                event.target.classList.add('btn-menu-active');
            }
        </script>
    <?php endif; ?>
</body>
</html>
