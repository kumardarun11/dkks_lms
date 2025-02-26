<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "library_db";

// Connect to MySQL database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user activation
if (isset($_POST['activate'])) {
    $userId = $_POST['user_id'];
    $conn->query("UPDATE users SET status='Active' WHERE user_id=$userId");
}

// Handle user deactivation
if (isset($_POST['deactivate'])) {
    $userId = $_POST['user_id'];
    $conn->query("UPDATE users SET status='Pending' WHERE user_id=$userId");
}

// Fetch users
$result = $conn->query("SELECT user_id, name, email, status FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: url('background.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .wrapper {
      max-width: 600px;
      width: 100%;
      background: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .title {
      text-align: center;
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
      color: #003366;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table, th, td {
      border: 1px solid lightgrey;
    }
    th, td {
      padding: 10px;
      text-align: center;
    }
    button {
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      color: #fff;
    }
    .activate-btn {
      background: #28a745;
    }
    .deactivate-btn {
      background: #dc3545;
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
  <div class="wrapper">
    <a href="dashboard.php" class="back-to-dashboard">
      <span class="icon">&#8592;</span> <span class="text">Back to Dashboard</span>
    </a>
    <div class="title">Manage Users</div>
    <table>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
      <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
          <td><?php echo $row['name']; ?></td>
          <td><?php echo $row['email']; ?></td>
          <td><?php echo $row['status']; ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
              <?php if ($row['status'] == 'Pending') { ?>
                <button type="submit" name="activate" class="activate-btn">Activate</button>
              <?php } else { ?>
                <button type="submit" name="deactivate" class="deactivate-btn">Deactivate</button>
              <?php } ?>
            </form>
          </td>
        </tr>
      <?php } ?>
    </table>
  </div>
</body>
</html>

<?php $conn->close(); ?>
