<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($book_id === 0) {
    header('Location: books.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    header('Location: books.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

$page_title = "Place Order";

include '../includes/header.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = intval($_POST['quantity'] ?? 1);
    $order_type = $_POST['order_type'] ?? $book['type'];
    $payment_method = $_POST['payment_method'] ?? '';
    $shipping_address = trim($_POST['shipping_address'] ?? $user['address']);
    
    if ($quantity < 1) {
        $error_message = 'Please enter a valid quantity.';
    } elseif ($quantity > $book['stock']) {
        $error_message = 'Requested quantity exceeds available stock.';
    } elseif (empty($payment_method)) {
        $error_message = 'Please select a payment method.';
    } elseif (in_array($order_type, ['cd', 'hardcopy']) && empty($shipping_address)) {
        $error_message = 'Shipping address is required for physical items.';
    } else {
        $unit_price = $book['is_free'] ? 0 : $book['price'];
        $total_amount = $unit_price * $quantity;
        
        $shipping_cost = 0;
        if (in_array($order_type, ['cd', 'hardcopy'])) {
            $shipping_cost = 5.99 * $quantity;
            $total_amount += $shipping_cost;
        }
        
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, book_id, quantity, order_type, total_amount, status, order_date) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $order_stmt->bind_param("iiisd", $user_id, $book_id, $quantity, $order_type, $total_amount);
        
        if ($order_stmt->execute()) {
            $order_id = $order_stmt->insert_id;
            $order_stmt->close();
            
            $payment_status = $book['is_free'] ? 'completed' : 'pending';
            $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, payment_status, payment_date) VALUES (?, ?, ?, ?, NOW())");
            $payment_stmt->bind_param("isds", $order_id, $payment_method, $total_amount, $payment_status);
            $payment_stmt->execute();
            $payment_stmt->close();
            
            $update_stock = $conn->prepare("UPDATE books SET stock = stock - ? WHERE book_id = ?");
            $update_stock->bind_param("ii", $quantity, $book_id);
            $update_stock->execute();
            $update_stock->close();
            
            $success_message = 'Order placed successfully! Order ID: #' . $order_id;
            
            $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error_message = 'Failed to place order. Please try again.';
        }
    }
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-5 fw-bold mb-3">Place Your Order</h1>
                <p class="lead mb-4">Complete your purchase in a few simple steps</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="books.php">Books</a></li>
                        <li class="breadcrumb-item active">Order</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Order Section -->
<section class="order-section py-5">
    <div class="container">
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <div class="text-center mb-4">
            <a href="profile.php" class="btn btn-primary me-2">
                <i class="bi bi-person me-2"></i>View My Orders
            </a>
            <a href="books.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Continue Shopping
            </a>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Order Form -->
            <div class="col-lg-8 mb-4">
                <div class="order-card">
                    <h4 class="order-card-title">
                        <i class="bi bi-cart-check me-2"></i>Order Details
                    </h4>
                    
                    <form method="POST" action="" class="order-form needs-validation" novalidate>
                        <!-- Book Information -->
                        <div class="book-info-section mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="../assets/images/books/default.jpg" 
                                         alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                         class="img-fluid rounded">
                                </div>
                                <div class="col-md-9">
                                    <span class="book-category-badge"><?php echo htmlspecialchars($book['category']); ?></span>
                                    <h5 class="mb-2"><?php echo htmlspecialchars($book['title']); ?></h5>
                                    <p class="text-muted mb-2">By <?php echo htmlspecialchars($book['author']); ?></p>
                                    <div class="book-price-display">
                                        <?php if ($book['is_free']): ?>
                                            <span class="price-tag price-free">FREE</span>
                                        <?php else: ?>
                                            <span class="price-tag">$<?php echo number_format($book['price'], 2); ?></span>
                                        <?php endif; ?>
                                        <span class="stock-badge">
                                            <i class="bi bi-box-seam"></i> <?php echo $book['stock']; ?> in stock
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Order Type -->
                        <div class="form-section mb-4">
                            <h6 class="form-section-title">Select Format</h6>
                            <div class="order-type-options">
                                <label class="order-type-option">
                                    <input type="radio" name="order_type" value="pdf" 
                                           <?php echo $book['type'] === 'pdf' ? 'checked' : ''; ?> required>
                                    <div class="option-content">
                                        <i class="bi bi-file-pdf"></i>
                                        <span class="option-label">PDF Download</span>
                                        <small>Instant access</small>
                                    </div>
                                </label>
                                
                                <label class="order-type-option">
                                    <input type="radio" name="order_type" value="cd" 
                                           <?php echo $book['type'] === 'cd' ? 'checked' : ''; ?>>
                                    <div class="option-content">
                                        <i class="bi bi-disc"></i>
                                        <span class="option-label">CD</span>
                                        <small>+$5.99 shipping</small>
                                    </div>
                                </label>
                                
                                <label class="order-type-option">
                                    <input type="radio" name="order_type" value="hardcopy" 
                                           <?php echo $book['type'] === 'hardcopy' ? 'checked' : ''; ?>>
                                    <div class="option-content">
                                        <i class="bi bi-book"></i>
                                        <span class="option-label">Hard Copy</span>
                                        <small>+$5.99 shipping</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="form-section mb-4">
                            <h6 class="form-section-title">Quantity</h6>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn" onclick="decreaseQty()">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" id="quantity" name="quantity" class="qty-input" 
                                       value="1" min="1" max="<?php echo $book['stock']; ?>" required>
                                <button type="button" class="qty-btn" onclick="increaseQty()">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="form-section mb-4" id="shippingSection">
                            <h6 class="form-section-title">Shipping Address</h6>
                            <textarea name="shipping_address" class="form-control" rows="3" 
                                      placeholder="Enter your complete shipping address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            <small class="text-muted">Required for CD and Hard Copy orders</small>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section mb-4">
                            <h6 class="form-section-title">Payment Method</h6>
                            <div class="payment-methods">
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="credit_card" required>
                                    <div class="payment-content">
                                        <i class="bi bi-credit-card"></i>
                                        <span>Credit Card</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="paypal">
                                    <div class="payment-content">
                                        <i class="bi bi-paypal"></i>
                                        <span>PayPal</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <div class="payment-content">
                                        <i class="bi bi-bank"></i>
                                        <span>Bank Transfer</span>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="cod">
                                    <div class="payment-content">
                                        <i class="bi bi-cash"></i>
                                        <span>Cash on Delivery</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-check-circle me-2"></i>Place Order
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5 class="summary-title">Order Summary</h5>
                    
                    <div class="summary-item">
                        <span>Book Price</span>
                        <span id="bookPrice">$<?php echo number_format($book['is_free'] ? 0 : $book['price'], 2); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Quantity</span>
                        <span id="qtyDisplay">1</span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span id="subtotal">$<?php echo number_format($book['is_free'] ? 0 : $book['price'], 2); ?></span>
                    </div>
                    
                    <div class="summary-item" id="shippingCostRow" style="display: none;">
                        <span>Shipping</span>
                        <span id="shippingCost">$0.00</span>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="summary-total">
                        <span>Total Amount</span>
                        <span id="totalAmount">$<?php echo number_format($book['is_free'] ? 0 : $book['price'], 2); ?></span>
                    </div>
                    
                    <div class="summary-note">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>You will receive order confirmation via email</small>
                    </div>
                </div>

                <div class="features-box mt-4">
                    <div class="feature-item">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>Secure Payment</strong>
                            <small>Your data is encrypted</small>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-truck"></i>
                        <div>
                            <strong>Fast Delivery</strong>
                            <small>3-5 business days</small>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-arrow-repeat"></i>
                        <div>
                            <strong>Easy Returns</strong>
                            <small>30-day return policy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const bookPrice = <?php echo $book['is_free'] ? 0 : $book['price']; ?>;
const maxStock = <?php echo $book['stock']; ?>;

function updateSummary() {
    const qty = parseInt(document.getElementById('quantity').value) || 1;
    const orderType = document.querySelector('input[name="order_type"]:checked')?.value || 'pdf';
    
    const subtotal = bookPrice * qty;
    const shipping = (orderType === 'pdf') ? 0 : 5.99 * qty;
    const total = subtotal + shipping;
    
    document.getElementById('qtyDisplay').textContent = qty;
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('shippingCost').textContent = '$' + shipping.toFixed(2);
    document.getElementById('totalAmount').textContent = '$' + total.toFixed(2);
    
    const shippingRow = document.getElementById('shippingCostRow');
    const shippingSection = document.getElementById('shippingSection');
    
    if (orderType === 'pdf') {
        shippingRow.style.display = 'none';
        shippingSection.style.display = 'none';
    } else {
        shippingRow.style.display = 'flex';
        shippingSection.style.display = 'block';
    }
}

function increaseQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) < maxStock) {
        input.value = parseInt(input.value) + 1;
        updateSummary();
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        updateSummary();
    }
}

document.getElementById('quantity').addEventListener('input', updateSummary);
document.querySelectorAll('input[name="order_type"]').forEach(radio => {
    radio.addEventListener('change', updateSummary);
});

(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php
include '../includes/footer.php';
?>