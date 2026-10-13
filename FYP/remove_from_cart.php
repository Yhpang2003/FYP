<?php
require_once '_base.php';

require_auth();

$product_id = get('id');
$user_id = $_user->user_id;

if(!$product_id || !is_numeric($product_id)){
    flash('error','Invalid product.');
    redirect('/FYP/cart.php');
}

$conn = db_connect();

$stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($cart) {
    $cart_id = $cart['cart_id'];

    $stmt = $conn->prepare("DELETE FROM Cart_Item WHERE cart_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $cart_id, $product_id);

    if ($stmt->execute()) {
        flash('success', 'Item removed from cart.');
    } else {
        flash('error', 'Failed to remove item.');
    }
    $stmt->close();
} else {
    flash('error','You do not have an active cart.');
}

$conn->close();

redirect('cart.php');

?>