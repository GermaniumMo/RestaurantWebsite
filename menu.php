<?php
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/flash.php';
    require_once __DIR__ . '/includes/csrf.php';
    if (! is_logged_in()) {
        $can_order = false;
    } else {
        $can_order = true;
    }

    $categories = db_fetch_all("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC");
    $menu_items = db_fetch_all(
        "SELECT m.*, c.name as category_name
     FROM menu_items m
     LEFT JOIN categories c ON m.category_id = c.id
     WHERE m.is_available = 1
     ORDER BY c.display_order ASC, m.display_order ASC, m.name ASC"
    );
    $menu_by_category = [];
    foreach ($menu_items as $item) {
        $category_name                      = $item['category_name'] ?: 'Other';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
   <?php include 'includes/header.php'; ?>

    <section class="introduction-section">
        <div class="introduction-container">
            <div class="text-center">
                <h1>Our Menu</h1>
                <p>Discover our carefully crafted dishes made with the finest ingredients</p>
            </div>
        </div>
    </section>

    <?php flash_show_all(); ?>

<?php if (! empty($categories)): ?>
<section class="btn-container position-relative">
    <div class="d-flex justify-content-center flex-wrap gap-2 position-relative">
        <div class="active-bg "></div>

        <button class="btn btn-menu btn-menu-active" onclick="showCategoryWithBg(this, 'all')">All</button>
        <?php foreach ($categories as $category): ?>
            <button class="btn btn-menu" onclick="showCategoryWithBg(this, '<?php echo htmlspecialchars($category['name']) ?>')">
                <?php echo htmlspecialchars($category['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

    <section class="container-menu">
        <?php if (empty($menu_items)): ?>
            <div class="text-center py-5">
                <h3>Menu Coming Soon</h3>
                <p class="text-muted">We're working on our delicious menu. Please check back soon!</p>
            </div>
        <?php else: ?>
<?php foreach ($menu_by_category as $category_name => $items): ?>
                <div class="menu-category mb-5" data-category="<?php echo htmlspecialchars($category_name) ?>">
                    <h2 class="text-center mb-4" style="font-family: 'Cormorant Garamond', serif; color: #ea580c;">
                        <?php echo htmlspecialchars($category_name) ?>
                    </h2>

                    <div class="row g-4">
                        <?php foreach ($items as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']) ?>"
                                             class="card-img-top menu-card-img-top"
                                             alt="<?php echo htmlspecialchars($item['name']) ?>"
                                             style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top menu-card-img-top bg-light d-flex align-items-center justify-content-center">
                                            <i style="font-size: 3rem;">🍽️</i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($item['name']) ?></h5>
                                        <?php if ($item['description']): ?>
                                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($item['description']) ?></p>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <span>
                                                $<?php echo number_format($item['price'], 2) ?>
                                            </span>
                                            <?php if ($item['is_featured']): ?>
                                                <span style="
                        color:#ea580c;
                        font-size:1rem;
                        border:1px solid #ea580c;
                        background-color:transparent;
                        padding:6px 8px;
                        border-radius:6px; text-shadow: none;">Featured</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-3">
                                            <?php if ($can_order): ?>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <button class="btn btn-outline-secondary btn-sm" onclick="decreaseQuantity(<?php echo $item['id'] ?>)">-</button>
                                                        <input type="number" id="quantity-<?php echo $item['id'] ?>" class="form-control mx-2 text-center"
                                                               style="width: 60px;" value="1" min="1" max="10">
                                                        <button class="btn btn-outline-secondary btn-sm" onclick="increaseQuantity(<?php echo $item['id'] ?>)">+</button>
                                                    </div>
                                                    <button class="btn btn-primary" onclick="addToCart(event,                                                                                                                                                                                                                                                                       <?php echo $item['id'] ?>, '<?php echo htmlspecialchars($item['name'], ENT_QUOTES) ?>',<?php echo $item['price'] ?>)">
                                                        Add to Cart
                                                    </button>
                                                </div>
                                                <button class="btn btn-details btn-sm mt-2 w-100" onclick="viewDetails(<?php echo $item['id'] ?>, '<?php echo htmlspecialchars($item['name'], ENT_QUOTES) ?>', '<?php echo htmlspecialchars($item['description'], ENT_QUOTES) ?>',<?php echo $item['price'] ?>, '<?php echo htmlspecialchars($item['image_url']) ?>')">
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
    <?php include 'includes/footer.php'; ?>
<script src="js/main.js"></script>
    <?php if ($can_order): ?>
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
        <div class="position-fixed bottom-0 end-0 m-4" style="z-index: 1000;">
            <button class="btn btn-primary rounded-circle p-3 d-flex justify-content-center align-items-center" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar"
                    style="width: 60px; height: 60px; position: relative;">
                <i class="bi bi-cart" style="color: white; font-size: 1.5rem; font-weight: 700;"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount" style="display: none;">
                    0
                </span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($can_order): ?>
        <script>
            let cart = JSON.parse(localStorage.getItem('savoriaCart')) || [];
            let currentModalItem = null;
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
            function addToCart(event, itemId, itemName, itemPrice) {
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
                    const modal = bootstrap.Modal.getInstance(document.getElementById('itemDetailsModal'));
                    modal.hide();
                }
            }

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
            function proceedToCheckout() {
                if (cart.length === 0) return;
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
                const existingModal = document.getElementById('checkoutModal');
                if (existingModal) {
                    existingModal.remove();
                }
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                updateCheckoutSummary();
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
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';

                try {
                    const csrfResponse = await fetch('includes/csrf.php?action=get_token');
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

                    const response = await fetch('process_checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(orderData)
                    });
                    const responseText = await response.text();
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('[v0] JSON parse error:', parseError);
                        throw new Error('Invalid response format: ' + responseText);
                    }
                    if (result.success) {
                        cart = [];
                        localStorage.removeItem('savoriaCart');
                        updateCartDisplay();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                        modal.hide();
                        alert(`Order placed successfully! Order ID: ${result.order_id}\nTotal: $${result.total_amount.toFixed(2)}`);
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('[v0] Checkout error:', error);
                    alert('An error occurred while processing your order. Please try again.\nError: ' + error.message);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Place Order';
                }
            }
       function showCategoryWithBg(button, categoryName) {
    const bg = document.querySelector(".active-bg");
    const container = button.parentElement;
    const rect = button.getBoundingClientRect();
    const containerRect = container.getBoundingClientRect();
    bg.style.left = (rect.left - containerRect.left) + "px";
    bg.style.width = rect.width + "px";
    document.querySelectorAll(".btn-menu").forEach((btn) => btn.classList.remove("btn-menu-active"));
    button.classList.add("btn-menu-active");
    if (categoryName === 'all') {
        document.querySelectorAll('.menu-category').forEach(category => category.style.display = 'block');
    } else {
        document.querySelectorAll('.menu-category').forEach(category => {
            category.style.display = category.dataset.category === categoryName ? 'block' : 'none';
        });
    }
}
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();
    const activeBtn = document.querySelector('.btn-menu-active');
    if (activeBtn) {
        const bg = document.querySelector(".active-bg");
        const container = activeBtn.parentElement;
        const rect = activeBtn.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();
        bg.style.left = (rect.left - containerRect.left) + "px";
        bg.style.width = rect.width + "px";
    }
});
            document.addEventListener('DOMContentLoaded', function() {
                updateCartDisplay();
            });
        </script>
    <?php else: ?>
        <script>
            function showAllCategories() {
                document.querySelectorAll('.menu-category').forEach(category => {
                    category.style.display = 'block';
                });
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
                document.querySelectorAll('.btn-menu').forEach(btn => {
                    btn.classList.remove('btn-menu-active');
                });
                event.target.classList.add('btn-menu-active');
            }
        </script>
    <?php endif; ?>

</body>
</html>
