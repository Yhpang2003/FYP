<?php
// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
// Start session *only* if one is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

// Database connection
function db_connect() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "fyp";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Is unique?
function is_unique($value, $table, $field) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
    $stmt->close();
    $conn->close();
    return $count == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    return !is_unique($value, $table, $field);
}

// ============================================================================
// Email Function (Updated for Gmail App Password)
// ============================================================================

/**
 * Sends an email using PHPMailer and Gmail SMTP.
 *
 * @param string $to_email The recipient's email address.
 * @param string $to_name The recipient's name.
 * @param string $subject The email subject.
 * @param string $body The HTML email body.
 * @return bool True on success, false on failure.
 */
function send_email($to_email, $to_name, $subject, $body) {

    // --- Add PHPMailer classes ---
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';

    
    
    $gmail_username = 'pangyh-wm22@student.tarc.edu.my'; 
    $gmail_app_password = 'kmijzvcwvebwnlru';    
    // --- *********************************** ---

    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $gmail_username;
        $mail->Password   = $gmail_app_password;
        $mail->SMTPSecure = 'tls'; // PHPMailer::ENCRYPTION_STARTTLS
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($gmail_username, 'DIY-Store Admin');
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo($gmail_username, 'Information');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Simple text version

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}


// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// ... (rest of your _base.php file) ...

// Get GET parameter
function get($key, $default = null) {
    return $_GET[$key] ?? $default;
}

// Get POST parameter
function post($key, $default = null) {
    return $_POST[$key] ?? $default;
}

// Redirect to URL
function redirect($url = null) {
    $url ??= 'index.php'; // Default to index.php if null
    header("Location: $url");
    exit();
}

// Set or get flash message
function flash($type, $message = null) {
    if ($message !== null) {
        // Set the message
        $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    } else {
        // Get the message
        $flash = $_SESSION['flash_message'] ?? null;
        if ($flash) {
            unset($_SESSION['flash_message']); // Clear after reading
            return $flash;
        }
    }
    return null;
}

// Helper to display flash messages
function flash_messages($filter_type = null) {
    $flash = flash(null); // Get the message
    if ($flash && (!$filter_type || $filter_type === $flash['type'])) {
        $type = $flash['type'];
        $message = e($flash['message']);
        echo "<div class='flash-message $type'>$message</div>";
    }
}

// Get uploaded file
function get_file($key) {
    $f = $_FILES[$key] ?? null;
    if ($f && $f['error'] == 0) {
        return (object)$f;
    }
    return null;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 400, $height = 400) {
    // Ensure the path uses forward slashes
    $folder = str_replace(DIRECTORY_SEPARATOR, '/', $folder);
    
    // Ensure the upload directory exists
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
    
    $photo_name = uniqid() . '.jpg';
    $full_path = $folder . '/' . $photo_name;

    require_once 'lib/SimpleImage.php'; // Make sure this path is correct
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height, 'center')
        ->toFile($full_path, 'image/jpeg');

    return $photo_name;
}

// ============================================================================
// Validation Functions
// ============================================================================

// Is money?
function is_money($value) {
    return preg_match('/^\d+(\.\d{1,2})?$/', $value);
}

// Is quantity? (whole number)
function is_quantity($value) {
    return preg_match('/^\d+$/', $value);
}

// Is email?
function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL);
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function e($value) {
    // Return empty string if value is null
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Global error array
$_err = [];

// Generate <span class'err-msg'> for an error
function err($key) {
    global $_err;
    if (isset($_err[$key])) {
        echo "<span class='text-red-500 text-xs italic mt-1'>{$_err[$key]}</span>";
    }
}

// ============================================================================
// Security & Authentication Functions
// ============================================================================

// Global user object
$_user = $_SESSION['user'] ?? null;

// Log in a user
function login($user) {
    // Convert array to object for consistency
    $_SESSION['user'] = (object)$user;
    session_regenerate_id(); // Regenerate session ID for security
}

// Log out a user
function logout() {
    unset($_SESSION['user']);
    session_regenerate_id();
}

// Check if user is logged in
function is_auth() {
    global $_user;
    return $_user !== null;
}

// Check if user is an admin
function is_admin() {
    global $_user;
    return is_auth() && $_user->role === 'Admin';
}

// Require a user to be logged in (can specify roles)
function require_auth(...$roles) {
    if (!is_auth()) {
        flash('error', 'You must be logged in to access that page.');
        redirect('/FYP/login.php');
    }
    if (!empty($roles) && !in_array($_SESSION['user']->role, $roles)) {
        flash('error', 'You do not have permission to access that page.');
        redirect('/FYP/products.php'); // Redirect non-admins to products page
    }
}

// Require a user to be an admin
function require_admin() {
    require_auth('Admin');
}

// Require a user to be a guest (not logged in)
function guest() {
    if (is_auth()) {
        redirect('/FYP/products.php'); // Redirect logged-in users away from login/register
    }
}
?>

