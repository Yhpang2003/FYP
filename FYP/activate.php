<?php
require_once '_base.php';

$token = get('token');

if (empty($token)) {
    flash('error', 'No activation token provided.');
    redirect('login.php');
}

$conn = db_connect();

// Find the user with this token
$stmt = $conn->prepare("SELECT * FROM User WHERE reset_token = ? AND status = 'Inactive'");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    // Found user, activate them!
    // Set status to 'Active' and clear the token so it can't be used again
    $stmt_activate = $conn->prepare("UPDATE User SET status = 'Active', reset_token = NULL WHERE user_id = ?");
    $stmt_activate->bind_param("i", $user['user_id']);
    $stmt_activate->execute();
    $stmt_activate->close();
    
    flash('success', 'Your account has been activated! You can now log in.');
    redirect('login.php');

} else {
    // No user found, or token is invalid/expired
    flash('error', 'This activation link is invalid or has already been used.');
    redirect('login.php');
}


$conn->close();

?>

