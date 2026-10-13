<?php
require_once '_base.php';



require_auth('Member');

$user_id = $_user->user_id;
$conn = db_connect();

// --- Handle Order Placement (POST request from this page) ---
if (is_post() && post('action') === 'place_order') {
    // 1. Get data from checkout form
    $address = post('address');
    $payment_method = post('payment_method');
    // Get the selected items, which are passed again as a hidden field
    $selected_ids = post('selected_items');
    
    // Simple validation for mock fields (no need to save them)
    $payment_valid = false;
    if ($payment_method === 'CreditCard' && post('card_number') && post('card_cvv')) {
        $payment_valid = true;
    } else if ($payment_method === 'BankTransfer' && post('bank_reference')) {
        $payment_valid = true;
    } else if ($payment_method === 'E-Wallet' && post('ewallet_phone')) {
        $payment_valid = true;
    }

    if (empty($address) || empty($payment_method) || empty($selected_ids) || !$payment_valid) {
        flash('error', 'Please fill in all required fields, including payment details.');
        // We must re-fetch the data to re-display the form
        $_POST['selected_items'] = $selected_ids; // Put it back for the GET logic to use
    } else {
        // --- Start Database Transaction ---
        $conn->begin_transaction();
        try {
            // 2. Fetch selected items from DB (to verify price & stock one last time)
            // Ensure $selected_ids is an array before using count()
            if (!is_array($selected_ids)) {
                 flash('error', 'Invalid items selected.');
                 redirect('cart.php');
            }
             // Ensure selected_ids are integers before count
             $selected_ids = array_map('intval', $selected_ids);
             if (empty($selected_ids)) {
                 flash('error', 'No items selected.');
                 redirect('cart.php');
             }

            $id_placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
            $types = str_repeat('i', count($selected_ids));
            $sql = "SELECT p.product_id, p.name, p.price, p.stock_quantity, ci.quantity 
                    FROM Cart_Item ci
                    JOIN Product p ON ci.product_id = p.product_id
                    JOIN Cart c ON ci.cart_id = c.cart_id
                    WHERE c.user_id = ? AND p.product_id IN ($id_placeholders)";
            
            $stmt = $conn->prepare($sql);
            $bind_params = array_merge([$user_id], $selected_ids);
            $stmt->bind_param("i" . $types, ...$bind_params);
            $stmt->execute();
            $items_to_order = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

             // Verify that the number of items fetched matches the number selected
             if (count($items_to_order) !== count($selected_ids)) {
                 flash('error', 'Some selected cart items could not be found or verified.');
                 $conn->rollback();
                 redirect('cart.php');
             }


            $total_amount = 0;
            $items_valid = true;

            // 3. Verify stock and calculate total
            foreach ($items_to_order as $item) {
                if ($item['quantity'] > $item['stock_quantity']) {
                    $items_valid = false;
                    flash('error', 'Item "' . e($item['name']) . '" does not have enough stock.');
                    break;
                }
                $total_amount += $item['price'] * $item['quantity'];
            }

            if ($items_valid) {
                // 4. Create the Order
                $stmt = $conn->prepare("INSERT INTO `Order` (user_id, total_amount, address, payment_method) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("idss", $user_id, $total_amount, $address, $payment_method);
                $stmt->execute();
                $order_id = $stmt->insert_id;
                $stmt->close();

                 if (!$order_id) {
                     throw new Exception("Failed to create order.");
                 }


                // 5. Create Order_Items and Update Product Stock
                $stmt_order_item = $conn->prepare("INSERT INTO Order_Item (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt_update_stock = $conn->prepare("UPDATE Product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $stmt_delete_cart = $conn->prepare("DELETE FROM Cart_Item WHERE cart_id = (SELECT cart_id FROM Cart WHERE user_id = ?) AND product_id = ?");

                foreach ($items_to_order as $item) {
                    // Add to Order_Item
                    $stmt_order_item->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                     if(!$stmt_order_item->execute()){
                         throw new Exception("Failed to insert order item: " . $stmt_order_item->error);
                    }

                    // Update Product stock
                    $stmt_update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
                     if(!$stmt_update_stock->execute()){
                         throw new Exception("Failed to update stock: " . $stmt_update_stock->error);
                    }


                    // Remove from Cart_Item
                    $stmt_delete_cart->bind_param("ii", $user_id, $item['product_id']);
                     if(!$stmt_delete_cart->execute()){
                         throw new Exception("Failed to delete cart item: " . $stmt_delete_cart->error);
                    }
                }
                // Close statements only after the loop is finished
                $stmt_order_item->close();
                $stmt_update_stock->close();
                $stmt_delete_cart->close();


                // 6. Commit transaction
                $conn->commit();

                // --- *** NEW: SEND ORDER CONFIRMATION EMAIL *** ---
                $email_subject = "Your DIY-Store Order Confirmation (Order #$order_id)";
                
                // Build the HTML email body
                $email_body = "<h1>Thank you for your order, " . e($_user->username) . "!</h1>";
                $email_body .= "<p>Your order (ID: <b>$order_id</b>) has been placed successfully.</p>";
                $email_body .= "<h2>Order Summary</h2>";
                $email_body .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
                $email_body .= "<thead style='background-color: #f4f4f4;'><tr><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>";
                $email_body .= "<tbody>";

                foreach ($items_to_order as $item) {
                    $item_total = $item['price'] * $item['quantity'];
                    $email_body .= "<tr>";
                    $email_body .= "<td>" . e($item['name']) . "</td>";
                    $email_body .= "<td>" . e($item['quantity']) . "</td>";
                    $email_body .= "<td>RM" . number_format($item['price'], 2) . "</td>";
                    $email_body .= "<td>RM" . number_format($item_total, 2) . "</td>";
                    $email_body .= "</tr>";
                }
                
                $email_body .= "<tr><td colspan='3' style='text-align:right;'><b>Total:</b></td><td><b>RM" . number_format($total_amount, 2) . "</b></td></tr>";
                $email_body .= "</tbody></table>";
                
                $email_body .= "<h3 style='margin-top: 20px;'>Shipping Address:</h3>";
                $email_body .= "<p>" . nl2br(e($address)) . "</p>"; // nl2br converts newlines
                
                $email_body .= "<h3>Payment Method:</h3>";
                $email_body .= "<p>" . e($payment_method) . "</p>";
                
                $email_body .= "<p style='margin-top: 20px;'>You can view your order details anytime by visiting your Order History page:</p>";
                $email_body .= "<a href='http://localhost/FYP/order_details.php?order_id=$order_id' style='display:inline-block; padding: 10px 15px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px;'>View Your Order</a>";

                
                send_email($_user->email, $_user->username, $email_subject, $email_body);
                // --- *** END OF EMAIL LOGIC *** ---


                // 7. Redirect to the order details page
                flash('success', 'Order placed successfully!'); // Set flash message
                redirect('order_details.php?order_id=' . $order_id);
            } else {
                // Rollback if stock check failed
                $conn->rollback();
                redirect('cart.php'); // Send back to cart if stock is bad
            }

        } catch (Exception $e) {
            $conn->rollback();
            // Log the detailed error for the developer
            error_log("Order placement error: " . $e->getMessage());
            // Show a generic message to the user
            flash('error', 'An error occurred while placing your order. Please try again.');
             // Redirect back to cart on general error
            redirect('cart.php');
        } finally {
            // Ensure connection is closed only if it was successfully opened and is still active
             if ($conn) {
                 $conn->close();
             }
        }
    }
} else { // Handle the GET request part for displaying the checkout page OR handle POST validation failure

    // --- Display Checkout Page (GET request or from cart POST) ---
    $selected_items_details = []; // Renamed to avoid confusion with $selected_ids
    $total_price = 0;
    // Use POST 'selected_items' (coming from cart form OR from failed validation)
    $selected_ids = $_POST['selected_items'] ?? []; 

    if (empty($selected_ids) || !is_array($selected_ids)) {
        flash('error', 'You did not select any items to checkout.');
        redirect('cart.php');
    }

    // Ensure all IDs are integers for security
    $selected_ids = array_map('intval', $selected_ids);
    // Remove potential 0s if intval failed or if 0 was passed
    $selected_ids = array_filter($selected_ids, function($id) { return $id > 0; });

     if (empty($selected_ids)) {
        flash('error', 'Invalid items selected for checkout.');
        redirect('cart.php');
     }


    // Fetch details for only the selected items
    $id_placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $types = str_repeat('i', count($selected_ids));

    $sql = "SELECT p.product_id, p.name, p.price, p.image, ci.quantity 
            FROM Cart_Item ci
            JOIN Product p ON ci.product_id = p.product_id
            JOIN Cart c ON ci.cart_id = c.cart_id
            WHERE c.user_id = ? AND p.product_id IN ($id_placeholders)";

    $stmt = $conn->prepare($sql);
    // Ensure parameters are passed correctly for bind_param
    $bind_params = array_merge([$user_id], $selected_ids);
    $stmt->bind_param("i" . $types, ...$bind_params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $selected_items_details[] = $item; // Populate the details array
        $total_price += $item['price'] * $item['quantity'];
    }
    $stmt->close();
    
    // Check if the fetched items match the selected IDs count
    if (count($selected_items_details) !== count($selected_ids)) {
        // This can happen if items were removed or invalid IDs were somehow sent
        flash('error', 'Some selected items could not be found.');
         if ($conn) $conn->close(); // Close connection before redirect
        redirect('cart.php');
    }
    
    // --- ALL LOGIC IS DONE. NOW WE CAN PRINT THE HTML ---
    require_once '_head.php'; 
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Checkout</h1>
    
    <?php flash_messages(); ?>

    <form id="checkout-form" action="checkout.php" method="POST">
        <!-- Hidden field to mark this as the final order submission -->
        <input type="hidden" name="action" value="place_order">
        
        <!-- Pass the selected item IDs along to the processing logic -->
        <?php foreach ($selected_ids as $id): ?>
            <input type="hidden" name="selected_items[]" value="<?= e($id) ?>">
        <?php endforeach; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Shipping & Payment Details -->
            <div class="lg:w-2/3">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-4 mb-4">Shipping & Payment</h2>
                    
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Shipping Address:</label>
                        <!-- Use the value submitted if validation failed -->
                        <textarea id="address" name="address" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?= e(post('address')) ?></textarea>
                        <?php err('address'); ?>
                    </div>

                    <div class="mb-4">
                        <label for="payment_method" class="block text-gray-700 text-sm font-bold mb-2">Payment Method:</label>
                        <select id="payment_method" name="payment_method" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">-- Select a Method --</option>
                            <!-- Keep selected value if validation failed -->
                            <option value="CreditCard" <?= post('payment_method') == 'CreditCard' ? 'selected' : '' ?>>Credit Card</option>
                            <option value="BankTransfer" <?= post('payment_method') == 'BankTransfer' ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="E-Wallet" <?= post('payment_method') == 'E-Wallet' ? 'selected' : '' ?>>E-Wallet</option>
                        </select>
                        <?php err('payment_method'); ?>
                    </div>

                    <!-- Mock Payment Fields -->
                    <div id="payment-details" class="mt-6 border-t pt-6">
                        <!-- Credit Card Fields -->
                        <div id="credit-card-fields" class="payment-fields hidden space-y-4">
                            <div>
                                <label for="card_number" class="block text-gray-700 text-sm font-bold mb-2">Card Number:</label>
                                <input type="text" id="card_number" name="card_number" value="<?= e(post('card_number')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="1234 5678 1234 5678">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="card_expiry" class="block text-gray-700 text-sm font-bold mb-2">Expiry Date:</label>
                                    <input type="text" id="card_expiry" name="card_expiry" value="<?= e(post('card_expiry')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="MM/YY">
                                </div>
                                <div>
                                    <label for="card_cvv" class="block text-gray-700 text-sm font-bold mb-2">CVV:</label>
                                    <input type="text" id="card_cvv" name="card_cvv" value="<?= e(post('card_cvv')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="123">
                                </div>
                            </div>
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div id="bank-transfer-fields" class="payment-fields hidden">
                            <p class="text-gray-700 mb-2">Please transfer to the following bank account:</p>
                            <p class="font-mono p-2 bg-gray-100 rounded">Maybank: 1234567890 (DIY Store)</p>
                            <div class="mt-4">
                                <label for="bank_reference" class="block text-gray-700 text-sm font-bold mb-2">Reference ID:</label>
                                <input type="text" id="bank_reference" name="bank_reference" value="<?= e(post('bank_reference')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="Your Transaction ID">
                            </div>
                        </div>

                        <!-- E-Wallet Fields -->
                        <div id="e-wallet-fields" class="payment-fields hidden">
                            <p class="text-gray-700 mb-2">Please pay to the following number:</p>
                            <p class="font-mono p-2 bg-gray-100 rounded">TNG E-Wallet: 012-3456789 (DIY Store)</p>
                            <div class="mt-4">
                                <label for="ewallet_phone" class="block text-gray-700 text-sm font-bold mb-2">Your Phone Number:</label>
                                <input type="text" id="ewallet_phone" name="ewallet_phone" value="<?= e(post('ewallet_phone')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="01x-xxxxxxx">
                            </div>
                        </div>
                    </div>
                    <!-- End Mock Payment Fields -->

                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white p-6 rounded-lg shadow-md sticky top-8">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-4 mb-4">Your Order</h2>
                    
                    <?php if (!empty($selected_items_details)):?>
                        <?php foreach ($selected_items_details as $item): ?>
                        <div class="flex justify-between items-center py-2 border-b">
                            <div class="flex items-center">
                                <img src="/FYP/<?= e($item['image'] ?? 'https://placehold.co/100x100/e2e8f0/cbd5e0?text=No+Image') ?>" alt="<?= e($item['name']) ?>" class="w-12 h-12 object-cover rounded-md mr-3">
                                <div>
                                    <span class="font-semibold"><?= e($item['name']) ?></span>
                                    <span class="block text-sm text-gray-600">Qty: <?= e($item['quantity']) ?></span>
                                </div>
                            </div>
                            <span class="font-semibold">RM<?= e(number_format($item['price'] * $item['quantity'], 2)) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <p class="text-gray-600">No items selected for checkout.</p>
                    <?php endif; ?>


                    <div class="flex justify-between text-xl font-bold border-t pt-4 mt-4">
                        <span>Total</span>
                        <span>RM<?= e(number_format($total_price, 2)) ?></span>
                    </div>
                    
                    <button type="submit" class="block text-center w-full mt-6 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for payment fields -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const paymentMethod = document.getElementById('payment_method');
    const allPaymentFields = document.querySelectorAll('.payment-fields');
    const ccFields = document.getElementById('credit-card-fields');
    const ccNumber = document.getElementById('card_number');
    const ccCvv = document.getElementById('card_cvv');
    const bankFields = document.getElementById('bank-transfer-fields');
    const bankRef = document.getElementById('bank_reference');
    const ewalletFields = document.getElementById('e-wallet-fields');
    const ewalletPhone = document.getElementById('ewallet_phone');

    paymentMethod.addEventListener('change', () => {
        // Hide all fields first
        allPaymentFields.forEach(f => f.classList.add('hidden'));
        
        // Disable 'required' on all inputs
        ccNumber.required = false;
        ccCvv.required = false;
        bankRef.required = false;
        ewalletPhone.required = false;

        // Show the selected one and set 'required'
        const selectedMethod = paymentMethod.value;
        if (selectedMethod === 'CreditCard') {
            ccFields.classList.remove('hidden');
            ccNumber.required = true;
            ccCvv.required = true;
        } else if (selectedMethod === 'BankTransfer') {
            bankFields.classList.remove('hidden');
            bankRef.required = true;
        } else if (selectedMethod === 'E-Wallet') {
            ewalletFields.classList.remove('hidden');
            ewalletPhone.required = true;
        }
    });

    // Trigger change event on page load if a method is already selected (e.g., on form error)
    if (paymentMethod.value) {
        paymentMethod.dispatchEvent(new Event('change'));
    }
});
</script>

<?php
} // End the else block for handling GET request / POST validation failure

// Only close connection if it's still open and the script hasn't exited via redirect
// REMOVED: Deprecated ping check
if ($conn && $conn instanceof mysqli && $conn->thread_id) {
     $conn->close();
}
require_once '_foot.php';
?>

