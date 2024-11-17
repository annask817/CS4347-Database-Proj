<?php
session_start();
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update User Type (Vulnerable)</title>
</head>
<body>
    <h2>Update User Type</h2>
    <form method="POST">
        <div>
            <label>Username:</label>
            <input type="text" name="username" placeholder="Try: ' OR '1'='1">
        </div>
        <div>
            <label>New Type:</label>
            <select name="type">
                <option value="personal">Personal</option>
                <option value="business">Business</option>
            </select>
        </div>
        <button type="submit">Update User</button>
    </form>
    
    <?php
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $conn = connectDB();
        
        // Vulnerable query
        $query = "UPDATE User 
                 SET user_type = '" . $_POST['type'] . "' 
                 WHERE user_name = '" . $_POST['username'] . "'";
                 
        echo "<div>Query: " . htmlspecialchars($query) . "</div>";
        
        if($conn->query($query)) {
            // Show affected users
            $result = $conn->query("SELECT user_id, user_name, user_type FROM User");
            echo "<h3>Updated Users:</h3>";
            while($row = $result->fetch_assoc()) {
                echo "<div>";
                echo "ID: " . htmlspecialchars($row['user_id']) . " | ";
                echo "Name: " . htmlspecialchars($row['user_name']) . " | ";
                echo "Type: " . htmlspecialchars($row['user_type']);
                echo "</div>";
            }
        } else {
            echo "<div>Update failed: " . $conn->error . "</div>";
        }
        $conn->close();
    }
    ?>
</body>
</html>