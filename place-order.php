<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

requireUserLogin();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = isset($_POST['customer_name']) ? trim(htmlspecialchars($_POST['customer_name'])) : '';
    $phone   = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'])) : '';
    $address = isset($_POST['address']) ? trim(htmlspecialchars($_POST['address'])) : '';

    if (empty($name) || empty($phone) || empty($address)) {
        // Changed flash type to danger (red)
        setFlash('danger', 'Please provide all delivery details.');
        redirect('checkout.php');
    }

    $subtotal = getCartSubtotal($conn, $userId);
    if ($subtotal <= 0) {
        redirect('cart.php');
    }

    $deliveryCharge = 40.00;
    $grandTotal = $subtotal + $deliveryCharge;

    // Fetch Cart Items with current stock
    $stmt = $conn->prepare("SELECT c.quantity, f.id as food_id, f.name, f.price, f.stock 
                            FROM cart c JOIN foods f ON c.food_id = f.id 
                            WHERE c.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($cartItems)) {
        redirect('cart.php');
    }

    // Validate stock for all items before starting transaction
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            setFlash('danger', 'Insufficient stock for "' . htmlspecialchars($item['name']) . '". Only ' . $item['stock'] . ' left in stock.');
            redirect('cart.php');
        }
    }

    $conn->begin_transaction();

    try {
        // Insert order + items
        $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, phone, address, total_amount, delivery_charge) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdd", $userId, $name, $phone, $address, $grandTotal, $deliveryCharge);
        $stmt->execute();
        $orderId = $conn->insert_id;
        $stmt->close();

        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, food_id, food_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $stmtItem->bind_param("iisdi", $orderId, $item['food_id'], $item['name'], $item['price'], $item['quantity']);
            $stmtItem->execute();
        }
        $stmtItem->close();

        // Directly decrease admin panel stock items for each food ordered
        $stmtStock = $conn->prepare("UPDATE foods SET stock = GREATEST(0, stock - ?) WHERE id = ?");
        foreach ($cartItems as $item) {
            $stmtStock->bind_param("ii", $item['quantity'], $item['food_id']);
            $stmtStock->execute();
        }
        $stmtStock->close();

        $stmtClear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmtClear->bind_param("i", $userId);
        $stmtClear->execute();
        $stmtClear->close();

        $conn->commit();

        // Changed success flash to danger (red theme)
        setFlash('danger', 'Order placed successfully! Order ID #' . $orderId);
        redirect('my-orders.php');

    } catch (Exception $e) {
        $conn->rollback();
        setFlash('danger', 'Failed to process order. Please try again.');
        redirect('checkout.php');
    }
} else {
    redirect('shop.php');
}
