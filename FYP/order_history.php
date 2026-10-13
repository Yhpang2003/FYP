<?php
require_once '_base.php';
require_once '_head.php'; 

// Order history is for logged-in members only
require_auth('Member');

$user_id = $_user->user_id;
$conn = db_connect();

// Fetch all orders for the current user, newest first
$sql = "SELECT order_id, order_date, total_amount, payment_method 
        FROM `Order` 
        WHERE user_id = ? 
        ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();


?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Your Order History</h1>
    
    <?php flash_messages(); // Show success message from checkout ?>

    <?php if (empty($orders)): ?>
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <p class="text-xl text-gray-700">You haven't placed any orders yet.</p>
            <a href="/FYP/products.php" class="inline-block mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Order ID</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Total</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Payment Method</th>
                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="text-left py-3 px-4">#<?= e($order['order_id']) ?></td>
                            <td class="text-left py-3 px-4"><?= e(date('d M Y, H:i', strtotime($order['order_date']))) ?></td>
                            <td class="text-left py-3 px-4">RM<?= e(number_format($order['total_amount'], 2)) ?></td>
                            <td class="text-left py-3 px-4"><?= e($order['payment_method']) ?></td>
                            <td class="text-left py-3 px-4">
                                <a href="order_details.php?order_id=<?= e($order['order_id']) ?>" class="text-blue-500 hover:text-blue-700 font-semibold">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '_foot.php';
?>

