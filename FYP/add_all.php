<?php
require_once '_base.php';


require_auth('Member');

$user_id = $_user->user_id;
$project_id = post('project_id');

if (!is_post() || !$project_id || !is_numeric($project_id)) {
    redirect('projects.php');
}

$conn = db_connect();

// 1. Get the user's cart (or create one)
$stmt_cart = $conn->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$cart = $stmt_cart->get_result()->fetch_assoc();
$stmt_cart->close();

$cart_id = null;
if ($cart) {
    $cart_id = $cart['cart_id'];
} else {
    $stmt_create_cart = $conn->prepare("INSERT INTO Cart (user_id) VALUES (?)");
    $stmt_create_cart->bind_param("i", $user_id);
    $stmt_create_cart->execute();
    $cart_id = $stmt_create_cart->insert_id;
    $stmt_create_cart->close();
}

// 2. Get all materials for the project
$sql_materials = "SELECT pm.product_id, pm.quantity, p.stock_quantity, p.name 
                  FROM Project_Material pm
                  JOIN Product p ON pm.product_id = p.product_id
                  WHERE pm.project_id = ?";
$stmt_materials = $conn->prepare($sql_materials);
$stmt_materials->bind_param("i", $project_id);
$stmt_materials->execute();
$materials = $stmt_materials->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_materials->close();

if (empty($materials)) {
    flash('error', 'This project has no materials listed.');
    redirect('project_details.php?id=' . $project_id);
}

// 3. Loop through each material and add/update it in the cart
$conn->begin_transaction();
try {
    $stmt_check_item = $conn->prepare("SELECT quantity FROM Cart_Item WHERE cart_id = ? AND product_id = ?");
    $stmt_update_item = $conn->prepare("UPDATE Cart_Item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
    $stmt_insert_item = $conn->prepare("INSERT INTO Cart_Item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
    // this one for log user activity
    $stmt_log = $conn->prepare("INSERT INTO Browse_History (user_id, product_id, activity_type) VALUES (?, ?, 'add_to_cart')");

    $all_items_ok = true;

    foreach ($materials as $material) {
        $product_id = $material['product_id'];
        $required_quantity = $material['quantity'];
        
        // Check stock
        if ($required_quantity > $material['stock_quantity']) {
            flash('error', 'Not enough stock for "' . e($material['name']) . '". Item not added.');
            $all_items_ok = false;
            continue; // Skip this item
        }

        // Check if item is already in cart
        $stmt_check_item->bind_param("ii", $cart_id, $product_id);
        $stmt_check_item->execute();
        $existing_item = $stmt_check_item->get_result()->fetch_assoc();
        
        $new_total_quantity = $required_quantity;
        if ($existing_item) {
            $new_total_quantity += $existing_item['quantity'];
        }

        // Check stock again (for combined quantity)
        if ($new_total_quantity > $material['stock_quantity']) {
            flash('error', 'Not enough stock for "' . e($material['name']) . '" (including items already in cart). Item not added/updated.');
            $all_items_ok = false;
            continue; 
        }

        // Add or update
        if ($existing_item) {
            $stmt_update_item->bind_param("iii", $new_total_quantity, $cart_id, $product_id);
            $stmt_update_item->execute();
        } else {
            $stmt_insert_item->bind_param("iii", $cart_id, $product_id, $required_quantity);
            $stmt_insert_item->execute();
        }
        //log user activity
        $stmt_log->bind_param("ii", $user_id, $product_id);
        $stmt_log->execute();
    }
    
    // Close prepared statements
    $stmt_check_item->close();
    $stmt_update_item->close();
    $stmt_insert_item->close();
    $stmt_log->close();

    // Commit transaction
    $conn->commit();
    
    if ($all_items_ok) {
        flash('success', 'All available project materials added to your cart!');
    } else {
        flash('warning', 'Some materials were not added due to insufficient stock.');
    }

} catch (Exception $e) {
    $conn->rollback();
    flash('error', 'An error occurred while adding items: ' . $e->getMessage());
}

$conn->close();
redirect('cart.php'); // Redirect user to their cart

?>