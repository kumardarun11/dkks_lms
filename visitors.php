<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "library_db";
date_default_timezone_set('Asia/Kolkata');
// Connect to MySQL database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
$conn->query("SET time_zone = '+05:30'"); // Adjust timezone as per your location

$message = ""; // Message to display to the user

// Handle Add/Update Client Entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['entry'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $entry_time = date('Y-m-d H:i:s');

    if (empty($user_id) || empty($name) || empty($phone) || empty($email)) {
        $message = "All fields are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO clients (user_id, name, phone, email, entry_time) 
                                VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE 
                                name = VALUES(name), 
                                phone = VALUES(phone), 
                                email = VALUES(email), 
                                entry_time = VALUES(entry_time), 
                                exit_time = NULL");
        $stmt->bind_param("issss", $user_id, $name, $phone, $email, $entry_time);
        if ($stmt->execute()) {
            $message = "Cust entry recorded successfully!";
        } else {
            $message = "Error: Unable to record entry.";
        }
        $stmt->close();
    }
}

// Handle Update Exit Time
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exit'])) {
    $user_id = $_POST['user_id'];
    $exit_time = date('Y-m-d H:i:s');

    if (empty($user_id)) {
        $message = "User ID is required!";
    } else {
        $stmt = $conn->prepare("UPDATE clients SET exit_time = ? WHERE user_id = ?");
        $stmt->bind_param("si", $exit_time, $user_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Client exit time updated successfully!";
        } else {
            $message = "Invalid User ID or no active entry found.";
        }
        $stmt->close();
    }
}

// Fetch Customer Borrow Details
$client_details = [];
if (!empty($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $result = $conn->query("SELECT c.*, b.book_id 
                            FROM clients c 
                            LEFT JOIN borrowed_books b ON c.user_id = b.user_id 
                            WHERE c.user_id = $user_id");
    if ($result->num_rows > 0) {
        $client_details = $result->fetch_assoc();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management - Customer Management</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .wrapper {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .wrapper .title {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #003366;
        }
        .form-container {
            margin-bottom: 20px;
        }
        .field {
            margin-bottom: 15px;
        }
        .field input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid lightgrey;
            transition: all 0.3s ease;
        }
        .field input:focus {
            border-color: #0073e6;
        }
        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #0073e6;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button:hover {
            background: #005bb5;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 16px;
            color: #0073e6;
        }
        .details {
            margin-top: 20px;
            font-size: 16px;
        }
        .back-to-dashboard {
      position: absolute;
      top: 20px;
      left: 20px;
      display: flex;
      align-items: center;
      font-size: 16px;
      text-decoration: none;
      font-weight: bold;
      color: white;
      background-color:rgb(6, 86, 167);
      padding: 10px 20px;
      border-radius: 5px;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    .back-to-dashboard:hover {
      background-color: #002244;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }
    .back-to-dashboard .icon {
      font-size: 18px;
      margin-right: 10px;
    }
    .back-to-dashboard .text {
      font-size: 16px;
    }
    </style>
</head>
<body>
<a href="dashboard.php" class="back-to-dashboard">
      <span class="icon">&#8592;</span> <span class="text">Back to Dashboard</span>
    </a>
    <div class="wrapper">
        <div class="title">Client Management</div>

        <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

        <!-- Client Entry Form -->
        <form method="POST">
            <div class="form-container">
                <div class="field">
                    <input type="text" name="user_id" placeholder="User ID" required>
                </div>
                <div class="field">
                    <input type="text" name="name" placeholder="Name">
                </div>
                <div class="field">
                    <input type="text" name="phone" placeholder="Phone">
                </div>
                <div class="field">
                    <input type="email" name="email" placeholder="Email">
                </div>
                <button type="submit" name="entry">Record Entry</button>
            </div>
        </form>

        <!-- Client Exit Form -->
        <form method="POST">
            <div class="form-container">
                <div class="field">
                    <input type="text" name="user_id" placeholder="User ID" required>
                </div>
                <button type="submit" name="exit">Record Exit</button>
            </div>
        </form>

        <!-- Client Details -->
        <?php if (!empty($client_details)) { ?>
            <div class="details">
                <p><strong>Name:</strong> <?= htmlspecialchars($client_details['name']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($client_details['phone']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($client_details['email']) ?></p>
                <p><strong>Entry Time:</strong> <?= htmlspecialchars($client_details['entry_time']) ?></p>
                <p><strong>Exit Time:</strong> <?= htmlspecialchars($client_details['exit_time'] ?? 'Not exited') ?></p>
                <p><strong>Book Borrowed:</strong> <?= htmlspecialchars($client_details['book_id'] ?? 'No book borrowed') ?></p>
            </div>
        <?php } ?>
    </div>
</body>
</html>
