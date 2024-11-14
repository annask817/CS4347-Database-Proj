<?php
// login.php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT user_id, user_name FROM User WHERE user_name = ? AND user_email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['user_name'];
        header('Location: overview.php');
        exit;
    } else {
        $error = "Invalid username or email";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <button type="submit">Login</button>
            <?php if (isset($error)): ?>
                <div id="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>