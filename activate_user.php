<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if POST request and ID is set
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $userId = intval($_POST["id"]); // Convert to integer for safety

    // Debugging: Check if ID is received
    error_log("User ID received: " . $userId);

    // Update query
    $sql = "UPDATE users SET status='Active' WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        error_log("Database error: " . $stmt->error);
        echo "error";
    }

    $stmt->close();
}

$conn->close();
?>
