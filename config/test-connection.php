<?php
require_once 'config.php';

// Test the connection
$conn = connectDB();

if ($conn) {
    echo "Successfully connected to the database!";
    
    // Optional: Test a simple query
    $result = $conn->query("SELECT * FROM User LIMIT 1");
    if ($result) {
        echo "<br>Successfully queried the User table!";
        $row = $result->fetch_assoc();
        echo "<br>First user: " . $row['user_name'];
    }
    
    $conn->close();
} else {
    echo "Connection failed!";
}
?>