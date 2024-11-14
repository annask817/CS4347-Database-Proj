<?php
// register.php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $userType = $_POST['user_type'] ?? 'personal';
    
    $conn = connectDB();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM User WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Username already exists";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM User WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO User (user_name, user_type, user_email, time_created) VALUES (?, ?, ?, CURRENT_DATE())");
            $stmt->bind_param("sss", $username, $userType, $email);
            
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                header('Location: overview.php');
                exit;
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .register-link {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Register</h2>
        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required minlength="3" maxlength="15" pattern="[A-Za-z0-9]+" 
                       title="Username must be 3-15 characters long and contain only letters and numbers"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required maxlength="64"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label>Account Type</label>
                <select name="user_type" required>
                    <option value="personal" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'personal') ? 'selected' : ''; ?>>Personal</option>
                    <option value="business" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] === 'business') ? 'selected' : ''; ?>>Business</option>
                </select>
            </div>
            <button type="submit">Register</button>
            <?php if (isset($error)): ?>
                <div id="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </form>
        <div class="register-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>