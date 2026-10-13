<?php
require_once '../_base.php';

// --- 1. Get and Validate Product ID ---
$id = get('id');
if (!$id || !is_numeric($id)) {
    flash('error', 'Invalid product ID specified.');
    redirect('manage_product.php');
}

$conn = db_connect();

// --- 2. Fetch the current status of the product ---
$stmt = $conn->prepare("SELECT status FROM Product WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    flash('error', 'Product could not be found.');
    redirect('manage_product.php');
}

// --- 3. Determine the new status ---
// This is a simple toggle: if it's 'Available', make it 'Disabled', and vice versa.
$current_status = $product['status'];
$new_status = ($current_status === 'Available') ? 'Disabled' : 'Available';

// --- 4. Update the database with the new status ---
$stmt = $conn->prepare("UPDATE Product SET status = ? WHERE product_id = ?");
$stmt->bind_param("si", $new_status, $id);

if ($stmt->execute()) {
    flash('success', "Product status has been updated to '$new_status'.");
} else {
    flash('error', 'Failed to update product status.');
}

$stmt->close();
$conn->close();

// --- 5. Redirect back to the manage products page ---
redirect('manage_product.php');
?>
