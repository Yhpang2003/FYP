<?php
require_once '_base.php';


// Profile page is for logged-in users only
require_auth(); 

$user_id = $_user->user_id;
$current_username = $_user->username; // Get current username from session
$current_email = $_user->email; // Get current email from session

// Handle form submission
if (is_post()) {
    $username = post('username');
    $current_password = post('current_password');
    $password = post('password'); // This is the NEW password
    $confirm_password = post('confirm_password');
    
    // Check if the user is trying to change their password
    $is_password_change = !empty($current_password) || !empty($password) || !empty($confirm_password);

    // --- Validation ---
    if (!$username) {
        $_err['username'] = 'Username is required.';
    } elseif (strlen($username) < 3) {
         $_err['username'] = 'Username must be at least 3 characters long.';
    } elseif ($username !== $current_username && !is_unique($username, 'User', 'username')) { 
         $_err['username'] = 'Username is already taken.';
    }

    // --- Password Validation (only if user is attempting to change it) ---
    if ($is_password_change) {
        
        // 1. Check current password
        if (empty($current_password)) {
            $_err['current_password'] = 'Please enter your current password to make changes.';
        } else {
            // Fetch current password from DB to verify
            $conn_check = db_connect();
            $stmt_check = $conn_check->prepare("SELECT password FROM User WHERE user_id = ?");
            $stmt_check->bind_param("i", $user_id);
            $stmt_check->execute();
            $user_data = $stmt_check->get_result()->fetch_assoc();
            $stmt_check->close();
            $conn_check->close();
            
            if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                $_err['current_password'] = 'Your current password is not correct.';
            }
        }

        // 2. Check new password (only if current password was correct)
        if (empty($_err['current_password'])) {
            if (empty($password)) {
                $_err['password'] = 'Please enter a new password.';
            } elseif (strlen($password) < 8) { // REQUIREMENT: At least 8 characters
                $_err['password'] = 'New password must be at least 8 characters long.';
            } elseif ($password !== $confirm_password) {
                $_err['confirm_password'] = 'New passwords do not match.';
            }
        }
    }
    
    // If no errors, update the database
    if (empty($_err)) {
        $conn = db_connect();
        
        if ($is_password_change) {
            // Update username AND password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE User SET username = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        } else {
            // Update username ONLY
            $stmt = $conn->prepare("UPDATE User SET username = ? WHERE user_id = ?");
            $stmt->bind_param("si", $username, $user_id);
        }

        if ($stmt->execute()) {
            // Update the session user object immediately
            $_user->username = $username; 
            $_SESSION['user'] = $_user; // Save updated user object back to session
            
            flash('success', 'Profile updated successfully!');
        } else {
            flash('error', 'Database error: Could not update profile.');
        }
        $stmt->close();
        $conn->close();
        
        // Redirect back to profile page to show flash message and prevent resubmission
        redirect('profile.php'); 
    }
    // If validation fails, the script continues and redisplays the form with errors
}


require_once '_head.php'; 
?>

<div class="w-full max-w-lg mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6 text-center">Update Your Profile</h1>
    
    <?php flash_messages(); ?>

    <form method="POST" action="profile.php">
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address:</label>
            <input type="email" id="email" name="email" value="<?= e($current_email) ?>" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-200" 
                   readonly disabled> 

        </div>

        <div class="mb-4">
            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
            <input type="text" id="username" name="username" value="<?= e(post('username', $current_username)) ?>" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['username']) ? 'border-red-500' : '' ?>">
            <?php err('username'); ?>
        </div>
        

        <!-- Password Change Toggle Button -->
        <div class="border-t pt-6 mt-6">
            <button type="button" id="change-password-btn" class="text-blue-500 hover:text-blue-700 text-sm font-semibold mb-4 focus:outline-none">
                Change Password
            </button>

            <!-- Hidden Password Fields -->
            <div id="password-section" class="hidden space-y-4">
                
                
                <div class="mb-4">
                    <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['current_password']) ? 'border-red-500' : '' ?>">
                    <?php err('current_password'); ?>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">New Password:</label>
                    <input type="password" id="password" name="password" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['password']) ? 'border-red-500' : '' ?>">
                    <?php err('password'); ?>
                </div>

                <div class="mb-6">
                    <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['confirm_password']) ? 'border-red-500' : '' ?>">
                    <?php err('confirm_password'); ?>
                </div>
            </div>
        </div>
        <!-- End Password Section -->


        <div class="flex items-center justify-center mt-6">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update Profile
            </button>
        </div>
    </form>
</div>

<!-- JavaScript to toggle the password fields -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const changeBtn = document.getElementById('change-password-btn');
    const passwordSection = document.getElementById('password-section');

    if (changeBtn && passwordSection) {
        changeBtn.addEventListener('click', () => {
            const isHidden = passwordSection.classList.toggle('hidden');
            if (isHidden) {
                changeBtn.textContent = 'Change Password';
            } else {
                changeBtn.textContent = 'Cancel Password Change';
                // Focus the first password field when shown
                document.getElementById('current_password').focus();
            }
        });

        // If there are password errors on page load, show the section automatically
        <?php if (isset($_err['current_password']) || isset($_err['password']) || isset($_err['confirm_password'])): ?>
            passwordSection.classList.remove('hidden');
            changeBtn.textContent = 'Cancel Password Change';
        <?php endif; ?>
    }
});
</script>


<?php
require_once '_foot.php';
?>

