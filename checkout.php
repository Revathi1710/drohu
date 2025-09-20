<?php
// Start the session to access cart data
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['mobile_number'])) {
    header("Location: login.php");
    exit();
}

// Check if the cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Redirect to the product page if cart is empty
    header("Location: index.php");
    exit();
}

// Calculate the total cart amount
$total_amount = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Water Delivery App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { background-color: #f4f7f9; }
        .checkout-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .cart-summary h3 { border-bottom: 2px solid #3498DB; padding-bottom: 10px; margin-bottom: 20px; }
        .cart-item-summary { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .cart-item-summary img { width: 50px; height: 50px; border-radius: 5px; margin-right: 15px; }
        .cart-item-details { flex-grow: 1; }
        .payment-options { margin-top: 30px; }
        .payment-option {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-option:hover, .payment-option.selected {
            border-color: #3498DB;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
        }
        .payment-option h4 { margin: 0; font-weight: 600; }
        .place-order-btn {
            width: 100%;
            padding: 15px;
            font-size: 1.2em;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="cart-summary">
        <h3>Order Summary</h3>
        <div id="cart-summary-items">
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="cart-item-summary">
                    <img src="./<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="cart-item-details">
                        <p class="mb-0"><strong><?php echo htmlspecialchars($item['name']); ?></strong></p>
                        <p class="mb-0">Qty: <?php echo $item['quantity']; ?></p>
                    </div>
                    <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-between mt-3 fw-bold">
            <span>Total Amount:</span>
            <span>₹<?php echo number_format($total_amount, 2); ?></span>
        </div>
    </div>

    <hr>

    <div class="payment-options">
        <h3>Choose Payment Method</h3>
        <div class="payment-option selected" data-method="razorpay">
            <h4><i class="fas fa-credit-card"></i> Pay Online with Razorpay</h4>
            <p class="mb-0">Secure payment via UPI, Credit/Debit Card, Netbanking, etc.</p>
        </div>
        <div class="payment-option" data-method="cod">
            <h4><i class="fas fa-money-bill-wave"></i> Cash on Delivery (COD)</h4>
            <p class="mb-0">Pay with cash when your order is delivered.</p>
        </div>
    </div>

    <button class="place-order-btn" id="place-order-btn">Place Order</button>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    const totalAmount = <?php echo $total_amount * 100; ?>; // Razorpay expects amount in paisa
    const placeOrderBtn = document.getElementById('place-order-btn');
    const paymentOptions = document.querySelectorAll('.payment-option');
    let selectedMethod = 'razorpay';

    // Highlight selected payment method
    paymentOptions.forEach(option => {
        option.addEventListener('click', () => {
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            selectedMethod = option.dataset.method;
        });
    });

    // Handle place order button click
    placeOrderBtn.addEventListener('click', () => {
        if (selectedMethod === 'cod') {
            // Handle COD logic
            window.location.href = 'process_order.php?method=cod';
        } else if (selectedMethod === 'razorpay') {
            // Handle Razorpay payment
            initiateRazorpayPayment();
        }
    });

    // Function to initiate Razorpay payment
    function initiateRazorpayPayment() {
        var options = {
            "key": "rzp_live_fuiflDFQLxFGf3", // Replace with your actual Key ID
            "amount": totalAmount,
            "currency": "INR",
            "name": "Water Delivery App",
            "description": "Payment for Water Order",
            "image": "https://example.com/your-app-logo.png",
            "handler": function (response) {
                // On successful payment, redirect to a success page with payment details
                window.location.href = `process_order.php?method=razorpay&payment_id=${response.razorpay_payment_id}`;
            },
            "prefill": {
                "name": "<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Customer'; ?>",
                "email": "<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'test@example.com'; ?>",
                "contact": "<?php echo $_SESSION['mobile_number']; ?>"
            },
            "theme": {
                "color": "#3498DB"
            }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    }
</script>

</body>
</html>