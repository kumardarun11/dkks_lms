<?php
// Database Configuration
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

// Get total users
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// Get total books
$totalBooks = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];

// Get available books
$availableBooks = $conn->query("SELECT SUM(available) AS total FROM books")->fetch_assoc()['total'];

// Get issued books
$issuedBooks = $conn->query("SELECT COUNT(*) AS total FROM borrowed_books WHERE return_date IS NULL")->fetch_assoc()['total'];

// Get recent transactions
$transactions = $conn->query("
    SELECT b.borrow_id, u.name AS user_name, bk.title AS book_title, 
    b.borrow_date, b.borrow_time, b.return_date, b.return_time
    FROM borrowed_books b
    JOIN users u ON b.user_id = u.user_id
    JOIN books bk ON b.book_id = bk.book_id
    ORDER BY b.borrow_id DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Reports</title>
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
            width: 80%;
            max-width: 900px;
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
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            flex: 1;
            background: #007BFF;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border: 1px solid lightgrey;
            text-align: center;
        }
        th {
            background: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
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
        <div class="title">Library Reports</div>
        <div class="stats">
            <div class="stat-box">Total Users: <?php echo $totalUsers; ?></div>
            <div class="stat-box">Total Books: <?php echo $totalBooks; ?></div>
            <div class="stat-box">Available Books: <?php echo $availableBooks; ?></div>
            <div class="stat-box">Issued Books: <?php echo $issuedBooks; ?></div>
        </div>
        
        <h3 style="text-align: center; color: #003366;">Recent Transactions</h3>
        <table>
            <tr>
                <th>Borrow ID</th>
                <th>User</th>
                <th>Book</th>
                <th>Borrow Date</th>
                <th>Borrow Time</th>
                <th>Return Date</th>
                <th>Return Time</th>
            </tr>
            <?php while ($row = $transactions->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['borrow_id']; ?></td>
                    <td><?php echo $row['user_name']; ?></td>
                    <td><?php echo $row['book_title']; ?></td>
                    <td><?php echo $row['borrow_date']; ?></td>
                    <td><?php echo $row['borrow_time']; ?></td>
                    <td><?php echo $row['return_date'] ?: '<span style="color:red;">Not Returned</span>'; ?></td>
                    <td><?php echo $row['return_time'] ?: '<span style="color:red;">Not Returned</span>'; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
