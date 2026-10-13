<?php
require_once '_base.php';
require_once '_head.php';

// Receipt page is for 'Member' roles only
require_auth('Member');

$user_id = $_user->user_id;
$order_id = get('order_id');

if (!$order_id || !is_numeric($order_id)) {
    flash('error', 'Invalid order ID.');
    redirect('order_history.php');
}

$conn = db_connect();

// 1. Get the Order details
$stmt = $conn->prepare("SELECT * FROM `Order` WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If order doesn't exist or doesn't belong to this user
if (!$order) {
    flash('error', 'Order not found.');
    redirect('order_history.php');
}

// 2. Get the Order Items
$stmt = $conn->prepare(
    "SELECT oi.*, p.name 
     FROM Order_Item oi
     JOIN Product p ON oi.product_id = p.product_id
     WHERE oi.order_id = ?"
);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!-- Specific styles for printing -->
<style>
@media print {
    body {
        background-color: #fff;
    }
    nav, footer, .print-buttons {
        /* Hide navbar, footer, and buttons when printing */
        display: none;
    }
    main.container {
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    .receipt-container {
        box-shadow: none;
        border: 1px solid #ccc;
    }
}
</style>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    
    <div class="bg-white p-8 rounded-lg shadow-md receipt-container">
        <h1 class="text-3xl font-bold text-center mb-6">Receipt</h1>
        
        <div class="border-b pb-4 mb-4">
            <h2 class="text-xl font-semibold mb-2">Order Summary</h2>
            <p class="text-gray-700">Order ID: #<?= e($order['order_id']) ?></p>
            <p class="text-gray-700">Payment Date: <?= e(date('j/n/Y H:i:s', strtotime($order['order_date']))) ?></p>
            <p class="text-gray-700">Payment Method: <?= e($order['payment_method']) ?></p>
            <p class="text-gray-700">Shipping Address: <?= e($order['address']) ?></p>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-2">Items</h2>
            <table class="w-full">
                <thead class="border-b">
                    <tr class="text-left text-gray-700">
                        <th class="py-2">Items</th>
                        <th class="py-2 text-center">Amount</th>
                        <th class="py-2 text-right">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr class="border-b">
                        <td class="py-2 font-medium"><?= e($item['name']) ?></td>
                        <td class="py-2 text-center text-gray-700"><?= e($item['quantity']) ?></td>
                        <td class="py-2 text-right text-gray-700">RM<?= e(number_format($item['price'] * $item['quantity'], 2)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold">
                        <td class="py-4" colspan="2">Total :</td>
                        <td class="py-4 text-right text-xl">RM<?= e(number_format($order['total_amount'], 2)) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

    <div class="mt-8 flex justify-between print-buttons">
        <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg">
            Print Receipt
        </button>
        <a href="/FYP/products.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">
            Back To Home Page
        </a>
    </div>
</div>

<?php
require_once '_foot.php';
?>
