<?php
$host = 'tommy.heliohost.org';
$port = 3306;
$dbname = 'magickeeper_budget';
$user = 'magickeeper_acc';
$pass = 'Magic#425';

$db = new mysqli($host, $user, $pass, $dbname, $port);

if($db->connect_error) {
  die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['username'];
  $email = $_POST['email'];

  $query = $db->prepare("SELECT * FROM User WHERE user_name = '$name' AND user_email = '$email';");
  $query->execute();
  $result = $query->get_result();
  
  if ($result->num_rows > 0) {
    echo "Login successful!<br>";
    while($row = $result->fetch_assoc()) {
      printf ("User ID: %s -- Username: %s -- User Type: %s -- User Email: %s -- Time created: -- %s<br>", $row["user_id"], $row["user_name"], $row["user_type"], $row["user_email"], $row["time_created"]);
    }
    $result->free();
  }
  else
    echo "Invalid username or email.";
  $query->close();
}
$db->close();
?>