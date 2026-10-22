<?php
// 1. START OUTPUT BUFFERING
ob_start();

require_once '_base.php';
require_auth('Member');

$user_id = $_user->user_id;
$conn = db_connect();

// --- CONFIGURATION ---
const STRIPE_SECRET_KEY = 'sk_test_51SagLPKCx8DLT9Z9FjeT1aKlHEpYWSBvDbAMGrNLPkqswnVLeUtqSwMW9ZI7KLReKQz8NAuBfDZSG3oPvhz9s75Z006YHtEJXy'; 
const STRIPE_PUB_KEY    = 'pk_test_51SagLPKCx8DLT9Z9QnWJFMyHQF69VVW59rI1TIFhSXLMxoWwWi64edhuoFzOFSnrWySff6RLpgyQakK4FL70KnMy00k5I7tg1d'; 

// --- HELPER: Shipping Calculator ---
function calculate_shipping_fee($state) {
    $east_my = ['Sabah', 'Sarawak', 'Labuan'];
    return in_array($state, $east_my) ? 20.00 : 10.00;
}

// --- HELPER: Create Stripe Payment Intent ---
function create_stripe_payment_intent($amount_myr, $metadata = []) {
    $url = 'https://api.stripe.com/v1/payment_intents';
    $data = [
        'amount' => round($amount_myr * 100), 
        'currency' => 'myr',
        'automatic_payment_methods' => ['enabled' => 'true'], 
        'metadata' => $metadata // Store address here 
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':'); 
    
    $result = curl_exec($ch);
    if ($result === false) throw new Exception("CURL Error: " . curl_error($ch));
    $response = json_decode($result, true);
    curl_close($ch);
    return $response;
}

// --- HELPER: Retrieve Intent (Verify Payment) ---
function retrieve_stripe_intent($intent_id) {
    // We add '?expand[]=payment_method' to get details like 'grabpay' or 'card'
    $url = 'https://api.stripe.com/v1/payment_intents/' . $intent_id . '?expand[]=payment_method';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':'); 
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// --- API: HANDLE AJAX REQUEST (Prepare Payment) ---
if (is_post() && post('action') === 'create_intent') {
    ob_clean();
    header('Content-Type: application/json');

    try {
        $addr_line1 = post('addr_line1');
        $addr_city  = post('addr_city');
        $addr_zip   = post('addr_zip');
        $addr_state = post('addr_state');
        $selected_ids = post('selected_items'); 

        if (empty($addr_line1) || empty($addr_state) || empty($selected_ids)) {
            throw new Exception("Please complete the shipping address.");
        }

        $full_address = "$addr_line1, $addr_zip $addr_city, $addr_state";
        
        // 1. Calculate Total (But don't save to DB yet)
        $selected_ids = array_map('intval', explode(',', $selected_ids));
        $id_placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $types = str_repeat('i', count($selected_ids));
        
        $sql = "SELECT p.product_id, p.name, p.price, p.stock_quantity, ci.quantity 
                FROM cart_item ci
                JOIN product p ON ci.product_id = p.product_id
                JOIN cart c ON ci.cart_id = c.cart_id
                WHERE c.user_id = ? AND p.product_id IN ($id_placeholders)";
        
        $stmt = $conn->prepare($sql);
        $bind_params = array_merge([$user_id], $selected_ids);
        $stmt->bind_param("i" . $types, ...$bind_params);
        $stmt->execute();
        $items_to_order = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($items_to_order) === 0) throw new Exception("No items found in cart.");

        $subtotal = 0;
        foreach ($items_to_order as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                throw new Exception("Item '{$item['name']}' is out of stock.");
            }
            $subtotal += $item['price'] * $item['quantity'];
        }
        $final_total = $subtotal + calculate_shipping_fee($addr_state);
        
        if ($final_total < 2.00) throw new Exception("Minimum order is RM 2.00");

        // 2. Create Stripe Intent (Save Address in Metadata)
        // We pass 'selected_ids' string too so we know what to buy later
        $intent = create_stripe_payment_intent($final_total, [
            'user_id' => $user_id,
            'address' => $full_address,
            'item_ids' => post('selected_items') 
        ]);

        if (isset($intent['error'])) throw new Exception($intent['error']['message']);

        echo json_encode([
            'status' => 'success',
            'clientSecret' => $intent['client_secret']
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 3. FLUSH BUFFER
ob_end_flush();

// --- HANDLE SUCCESS RETURN (Insert Order HERE) ---
if (get('payment_intent') && get('redirect_status') === 'succeeded') {
    
    $intent_id = get('payment_intent');
    
    // 1. Verify with Stripe that money is actually received
    $verify = retrieve_stripe_intent($intent_id);
    
    if ($verify['status'] === 'succeeded') {
        $meta = $verify['metadata'];
        $full_address = $meta['address'];
        $items_str = $meta['item_ids'];
        $total_amount = $verify['amount'] / 100; // Convert cents back to RM
        
        // 2. DETECT PAYMENT TYPE & MAP TO DB ENUM
        
        $stripe_type = $verify['payment_method']['type'] ?? 'card';
        
        $db_payment_method = 'CreditCard'; // Default
        
        if ($stripe_type === 'fpx') {
            $db_payment_method = 'BankTransfer';
        } elseif ($stripe_type !== 'card') {
            
            $db_payment_method = 'E-Wallet';
        }

        $conn->begin_transaction();
        try {
            // A. Create Order (No Status Column Needed)
            // Now using dynamic $db_payment_method instead of hardcoded 'CreditCard'
            $stmt = $conn->prepare("INSERT INTO `order` (user_id, total_amount, address, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $user_id, $total_amount, $full_address, $db_payment_method);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();
            
            // B. Re-fetch Items to Insert into Order_Item & Deduct Stock
            $selected_ids = array_map('intval', explode(',', $items_str));
            $id_placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
            $types = str_repeat('i', count($selected_ids));
            
            $sql = "SELECT p.product_id,p.name , p.price, ci.quantity 
                    FROM cart_item ci JOIN product p ON ci.product_id = p.product_id
                    JOIN cart c ON ci.cart_id = c.cart_id
                    WHERE c.user_id = ? AND p.product_id IN ($id_placeholders)";
            
            $stmt = $conn->prepare($sql);
            $bind_params = array_merge([$user_id], $selected_ids);
            $stmt->bind_param("i" . $types, ...$bind_params);
            $stmt->execute();
            $items_to_order = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            $stmt_item = $conn->prepare("INSERT INTO `order_item` (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $stmt_cart  = $conn->prepare("DELETE FROM cart_item WHERE cart_id = (SELECT cart_id FROM cart WHERE user_id = ?) AND product_id = ?");
            $stmt_admin = $conn ->prepare("SELECT * FROM User WHERE role='Admin'");
            $stmt_admin->execute();
            $admin = $stmt_admin->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($items_to_order as $item) {
                $stmt_item->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt_item->execute();
                
                $stmt_stock->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt_stock->execute();
                
                $stmt_cart->bind_param("ii", $user_id, $item['product_id']);
                $stmt_cart->execute();
            }
            
            $conn->commit();
            
// --- 3. SEND USER SUMMARY EMAIL ---
            if (function_exists('send_email')) {
                // Build HTML Table for the email
                $email_content = "<h1>Order #$order_id Confirmed</h1>";
                $email_content .= "<p>Thank you for your purchase!</p>";
                $email_content .= "<h3>Order Details:</h3>";
                $email_content .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse; width:100%;'>";
                $email_content .= "<tr style='background:#f2f2f2;'><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>";
                
                foreach ($items_to_order as $itm) {
                    $row_total = $itm['price'] * $itm['quantity'];
                    $email_content .= "<tr>";
                    $email_content .= "<td>" . e($itm['name']) . "</td>";
                    $email_content .= "<td style='text-align:center;'>" . e($itm['quantity']) . "</td>";
                    $email_content .= "<td>RM " . number_format($itm['price'], 2) . "</td>";
                    $email_content .= "<td>RM " . number_format($row_total, 2) . "</td>";
                    $email_content .= "</tr>";
                }
                
                $email_content .= "</table>";
                $email_content .= "<h3>Grand Total: RM " . number_format($total_amount, 2) . "</h3>";
                $email_content .= "<p><strong>Shipping Address:</strong><br>" . nl2br(e($full_address)) . "</p>";
                $email_content .= "<p>Payment Method: " . e($db_payment_method) . "</p>";

                // Send the email
                send_email($_user->email, $_user->username, "Order #$order_id Confirmed", $email_content);
            }

            // --- 4. SEND ADMIN LOW STOCK ALERT ---
            // Fetch admins
            $stmt_admin = $conn->query("SELECT email, username FROM User WHERE role='Admin'");
            $admins = $stmt_admin->fetch_all(MYSQLI_ASSOC);

            // Calculate remaining stock for each item we just bought
            foreach ($items_to_order as $sold_item) {
                
                $stmt_check = $conn->prepare("SELECT stock_quantity, name FROM product WHERE product_id = ?");
                $stmt_check->bind_param("i", $sold_item['product_id']);
                $stmt_check->execute();
                $current_product = $stmt_check->get_result()->fetch_assoc();
                $stmt_check->close();

            $remaining_stock = $current_product['stock_quantity'];
    
            // If stock drops to 5 or below, notify ALL admins
                if ($remaining_stock <= 5) {
                $alert_subject = "Low Stock Alert: " . $current_product['name'];
                $alert_body = "<h1>Stock Alert</h1>";
                $alert_body .= "<p>Product <strong>" . e($current_product['name']) . "</strong> (ID: " . $sold_item['product_id'] . ") is running low.</p>";
                $alert_body .= "<p>Remaining Quantity: <strong>$remaining_stock</strong></p>";
                $alert_body .= "<p>Please restock immediately.</p>";

                foreach ($admins as $admin) {
                    if (function_exists('send_email')) {
                   send_email($admin['email'], $admin['username'], $alert_subject, $alert_body);
                        }
                    }
                }
            }

            flash('success', 'Payment Successful! Order placed.');
            redirect("order_details.php?order_id=$order_id");

        } catch (Exception $e) {
            $conn->rollback();
            flash('error', 'Payment succeeded but order creation failed: ' . $e->getMessage());
            redirect('cart.php');
        }
    }
}

// --- DISPLAY PAGE ---
$selected_ids = $_POST['selected_items'] ?? [];
if (empty($selected_ids) && !get('payment_intent')) redirect('cart.php');

if (!empty($selected_ids)) {
    $selected_ids_safe = array_map('intval', $selected_ids);
    $placeholders = implode(',', array_fill(0, count($selected_ids_safe), '?'));
    
    $sql = "SELECT p.product_id, p.name, p.price, p.image, ci.quantity 
            FROM cart_item ci JOIN product p ON ci.product_id = p.product_id
            JOIN cart c ON ci.cart_id = c.cart_id
            WHERE c.user_id = ? AND p.product_id IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    $params = array_merge([$user_id], $selected_ids_safe);
    $stmt->bind_param("i" . str_repeat('i', count($selected_ids_safe)), ...$params);
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}

require_once '_head.php'; 
?>

<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Checkout</h1>
    <?php flash_messages(); ?>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3 space-y-6">
            <form id="address-form">
                <input type="hidden" id="selected_items_str" value="<?= implode(',', $selected_ids ?? []) ?>">

                <!-- Address Section -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">1. Shipping Address</h2>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm mb-2">Address Line 1</label>
                        <input type="text" id="addr_line1" name="addr_line1" class="w-full border rounded p-2 outline-none focus:ring-2 focus:ring-blue-500" placeholder="123 Jalan ABC" required value="<?= e(post('addr_line1')) ?>">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm mb-2">City</label>
                            <input type="text" id="addr_city" name="addr_city" class="w-full border rounded p-2 outline-none focus:ring-2 focus:ring-blue-500" placeholder="Kuala Lumpur" required value="<?= e(post('addr_city')) ?>">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm mb-2">Postcode</label>
                            <input type="text" id="addr_zip" name="addr_zip" class="w-full border rounded p-2 outline-none focus:ring-2 focus:ring-blue-500" placeholder="53300" required value="<?= e(post('addr_zip')) ?>">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm mb-2">State</label>
                        <select id="addr_state" name="addr_state" class="w-full border rounded p-2 outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Select State --</option>
                            <optgroup label="West Malaysia">
                                <option value="Johor">Johor</option>
                                <option value="Kedah">Kedah</option>
                                <option value="Kelantan">Kelantan</option>
                                <option value="Kuala Lumpur">Kuala Lumpur</option>
                                <option value="Melaka">Melaka</option>
                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                <option value="Pahang">Pahang</option>
                                <option value="Perak">Perak</option>
                                <option value="Perlis">Perlis</option>
                                <option value="Pulau Pinang">Pulau Pinang</option>
                                <option value="Putrajaya">Putrajaya</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Terengganu">Terengganu</option>
                            </optgroup>
                            <optgroup label="East Malaysia">
                                <option value="Labuan">Labuan</option>
                                <option value="Sabah">Sabah</option>
                                <option value="Sarawak">Sarawak</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Payment Section -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold text-gray-800 mb-4">2. Payment Method</h2>
                
                <div id="payment-element">
                    <!-- Stripe injects UI here -->
                    <div class="text-gray-500 text-sm p-4 bg-gray-50 rounded">
                        Please fill in your address above to load payment options.
                    </div>
                </div>
                
                <div id="payment-message" class="hidden mt-4 text-red-500 text-sm"></div>
            </div>
        </div>

        <!-- Summary -->
        <div class="lg:w-1/3">
            <div class="bg-white p-6 rounded-lg shadow-md sticky top-8">
                <h2 class="text-2xl font-bold text-gray-800 border-b pb-4 mb-4">Summary</h2>
                <div class="border-t pt-4 space-y-2">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span><span>RM<?= number_format($subtotal ?? 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping <span id="shipping-region" class="text-xs text-blue-500">(West MY)</span></span>
                        <span id="shipping-display">RM10.00</span>
                    </div>summar
                    <div class="flex justify-between text-2xl font-bold text-gray-900 pt-2 border-t mt-2">
                        <span>Total</span><span id="grand-total-display">RM<?= number_format(($subtotal ?? 0) + 10, 2) ?></span>
                    </div>
                </div>
                
                <button id="submit-btn" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Pay Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// --- FRONTEND LOGIC ---
const stripe = Stripe('<?= STRIPE_PUB_KEY ?>');
let elements;

// Constants
const subtotal = <?= $subtotal ?? 0 ?>;
const stateSelect = document.getElementById('addr_state');
const shippingDisplay = document.getElementById('shipping-display');
const regionDisplay = document.getElementById('shipping-region');
const grandTotalDisplay = document.getElementById('grand-total-display');
const submitBtn = document.getElementById('submit-btn');

// 2. Shipping Calculation UI
function updateTotals() {
    const state = stateSelect.value;
    let shippingFee = 10.00;
    let region = "(West MY)";
    
    if (['Sabah', 'Sarawak', 'Labuan'].includes(state)) {
        shippingFee = 20.00;
        region = "(East MY)";
    }

    shippingDisplay.textContent = 'RM' + shippingFee.toFixed(2);
    regionDisplay.textContent = region;
    grandTotalDisplay.textContent = 'RM' + (subtotal + shippingFee).toFixed(2);
}
stateSelect.addEventListener('change', updateTotals);

// 3. Handle "Pay Now" Click
submitBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    
    // Validate Form
    const form = document.getElementById('address-form');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    setLoading(true);

    // CRITICAL: Call elements.submit() BEFORE anything else
    // This triggers validation on Stripe fields
    const { error: submitError } = await elements.submit();
    if (submitError) {
        showMessage(submitError.message);
        setLoading(false);
        return; // Stop execution if Stripe form is invalid
    }

    // Step A: Create PaymentIntent on Server (NO DB INSERT YET)
    const formData = new FormData(form);
    formData.append('action', 'create_intent');
    formData.append('selected_items', document.getElementById('selected_items_str').value);

    try {
        const response = await fetch('checkout.php', {
            method: 'POST',
            body: formData
        });
        
        const textResponse = await response.text();
        let data;
        try {
            data = JSON.parse(textResponse);
        } catch (e) {
            console.error("Server Raw Response:", textResponse);
            throw new Error("Server Error (Not JSON). Check Console for details.");
        }

        if (data.status === 'error') {
            throw new Error(data.message);
        }

        // Step B: Confirm Payment with Stripe
        const { error } = await stripe.confirmPayment({
            elements,
            clientSecret: data.clientSecret,
            confirmParams: {
                // IMPORTANT: We redirect BACK to checkout.php, where we will insert the order
                return_url: window.location.origin + window.location.pathname,
                payment_method_data: {
                    billing_details: {
                        address: {
                            line1: formData.get('addr_line1'),
                            city: formData.get('addr_city'),
                            postal_code: formData.get('addr_zip'),
                            state: formData.get('addr_state'),
                            country: 'MY', // Hardcoded for this project
                        }
                    }
                }
            },
        });

        if (error) {
            showMessage(error.message);
            setLoading(false);
        }

    } catch (err) {
        showMessage(err.message);
        setLoading(false);
    }
});

// 4. Initialize Stripe Elements (Pre-load)
async function initializeStripe() {
    const appearance = { theme: 'stripe' };
    elements = stripe.elements({ 
        appearance, 
        mode: 'payment', 
        currency: 'myr', 
        amount: Math.round((subtotal + 10) * 100), // Approx initial amount
        locale: 'en'
    });

    const paymentElement = elements.create("payment");
    paymentElement.mount("#payment-element");
}

initializeStripe();

function setLoading(isLoading) {
    if (isLoading) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Processing...";
    } else {
        submitBtn.disabled = false;
        submitBtn.textContent = "Pay Now";
    }
}

function showMessage(messageText) {
    const messageContainer = document.querySelector("#payment-message");
    messageContainer.classList.remove("hidden");
    messageContainer.textContent = messageText;
    
    setTimeout(() => {
        messageContainer.classList.add("hidden");
        messageContainer.textContent = "";
    }, 10000);
}
</script>

<?php require_once '_foot.php'; ?>