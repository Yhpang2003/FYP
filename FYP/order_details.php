<?php
require_once '_base.php';
// Delay including head until after potential redirects
// require_once '_head.php'; 

// Order details are for logged-in members only
require_auth('Member');

$user_id = $_user->user_id;
$order_id = get('order_id');

if (!$order_id || !is_numeric($order_id)) {
    flash('error', 'Invalid order ID.');
    redirect('order_history.php');
}

$conn = db_connect();

// Fetch the main order details, ensuring it belongs to the current user
$sql_order = "SELECT * FROM `Order` WHERE order_id = ? AND user_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$order = $stmt_order->get_result()->fetch_assoc();
$stmt_order->close();

if (!$order) {
    flash('error', 'Order not found or you do not have permission to view it.');
    redirect('order_history.php');
}

// Fetch the items associated with this order
$sql_items = "SELECT oi.*, p.name, p.image 
              FROM Order_Item oi 
              JOIN Product p ON oi.product_id = p.product_id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$order_items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();
$conn->close();


require_once '_head.php'; 
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md print-area"> 
        <h1 class="text-3xl font-bold text-gray-900 mb-6 border-b pb-4 text-center">Order Receipt</h1> 


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Order Information</h2>
                <p><span class="font-semibold">Order ID:</span> #<?= e($order['order_id']) ?></p>
                <p><span class="font-semibold">Order Date:</span> <?= e(date('d M Y, H:i', strtotime($order['order_date']))) ?></p>
                <p><span class="font-semibold">Total Amount:</span> RM<?= e(number_format($order['total_amount'], 2)) ?></p>
                <p><span class="font-semibold">Payment Method:</span> <?= e($order['payment_method']) ?></p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Shipping Address</h2>
                <p><?= nl2br(e($order['address'])) ?></p>
            </div>
        </div>

        <h2 class="text-xl font-semibold text-gray-800 mb-4 pt-4 border-t">Items Ordered</h2>
        <div class="space-y-4">
            <?php foreach ($order_items as $item): ?>
                <div class="flex justify-between items-center py-2 border-b">
                    <div class="flex items-center">
                        <img src="/FYP/<?= e($item['image'] ?? 'https://placehold.co/100x100/e2e8f0/cbd5e0?text=No+Image') ?>" alt="<?= e($item['name']) ?>" class="w-16 h-16 object-cover rounded-md mr-4 print-hide-image"> <!-- Added print-hide-image -->
                        <div>
                            <span class="font-semibold text-gray-800"><?= e($item['name']) ?></span>
                            <span class="block text-sm text-gray-600">Qty: <?= e($item['quantity']) ?></span>
                            <span class="block text-sm text-gray-600">Price: RM<?= e(number_format($item['price'], 2)) ?></span>
                        </div>
                    </div>
                    <span class="font-semibold text-gray-800">RM<?= e(number_format($item['price'] * $item['quantity'], 2)) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Grand Total Section for Clarity -->
        <div class="flex justify-end text-xl font-bold border-t pt-4 mt-6">
            <span>Total:</span>
            <span class="ml-4">RM<?= e(number_format($order['total_amount'], 2)) ?></span>
        </div>


        
        <div class="flex flex-col sm:flex-row justify-end mt-8 space-y-4 sm:space-y-0 sm:space-x-4 no-print"> 
             <button onclick="printReceipt()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                Print Receipt
            </button>
             <a href="order_history.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-center">
                Back to Order History
            </a>
        </div>
    </div>
</div>

<!-- Simple Print CSS and JS -->
<style>
@media print {
  body * {
    visibility: hidden;
  }
  .print-area, .print-area * {
    visibility: visible;
  }
  .print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 0;
    border: none;
    box-shadow: none;
  }
  .no-print {
    display: none !important; /* Hide buttons when printing */
  }
   .print-hide-image {
      /* Optionally hide images during print for faster/simpler receipts */
      display: none !important; 
  }
}
</style>

<script>
function printReceipt() {
  window.print();
}
</script>


<?php
require_once '_foot.php';
?>

