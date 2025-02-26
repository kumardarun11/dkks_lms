<?php
session_start(); // Start the session

// Set timezone to your preferred timezone
date_default_timezone_set('Asia/Kolkata');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: adminsigninup.php");
    exit();
}

// Fetch the logged-in user's name from the session
$loggedInUser = $_SESSION['username'];

// Fetch statistics from the database
$totalVisitorsQuery = "SELECT COUNT(*) as total_visitors FROM clients";
$totalVisitors = $conn->query($totalVisitorsQuery)->fetch_assoc()['total_visitors'];

$borrowedBooksQuery = "SELECT COUNT(*) as borrowed_books FROM borrowed_books WHERE return_date IS NULL";
$borrowedBooks = $conn->query($borrowedBooksQuery)->fetch_assoc()['borrowed_books'];

$overdueBooksQuery = "SELECT COUNT(*) as overdue_books FROM borrowed_books WHERE return_date IS NULL AND return_time IS NULL";
$overdueBooks = $conn->query($overdueBooksQuery)->fetch_assoc()['overdue_books'];

$newMembersQuery = "SELECT COUNT(*) as new_members FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$newMembers = $conn->query($newMembersQuery)->fetch_assoc()['new_members'];
// Fetch data for Borrowed List
$borrowedList = $conn->query("
    SELECT borrow_id, book_id, user_id, borrow_date, borrow_time
    FROM borrowed_books
    WHERE return_date IS NULL
");

// Fetch data for Returned List
$returnedList = $conn->query("
    SELECT borrow_id, book_id, user_id, borrow_date, borrow_time, return_date, return_time
    FROM borrowed_books
    WHERE return_date IS NOT NULL
");

// Fetch users and books for display
$users = $conn->query("SELECT * FROM users");
$books = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Dashboard</title>
    <style>
        /* General CSS Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
            overflow: hidden; /* Prevent body scroll */
            height: 100vh; /* Ensure full height */
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
        
        .content {
            display: flex;
            height: 100%; /* Ensure content fills full height */
        }
        
        .sidebar {
            width: 160px;
            background-color: rgba(43, 47, 60, 1);
            color: #fff;
            height: 100vh; /* Fixed full height for the sidebar */
            position: fixed; /* Keeps it fixed while scrolling */
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
            z-index: 1000; /* Keeps the sidebar above other content */
            overflow-y: auto; /* Allow scrolling within the sidebar if needed */
        }
        
        .sidebar h2 {
            color: #ff6b6b;
            margin-bottom: 30px;
        }
        
        .sidebar a {
            color: #a9b0c3;
            text-decoration: none;
            margin: 12px 0;
            padding: 10px;
            display: block;
            border-radius: 8px;
            transition: background 0.3s, color 0.3s;
        }
        
        .sidebar a:hover {
            background-color: #ff6b6b;
            color: #fff;
        }
        
        .dashboard-container {
            margin-left: 200px; /* Space for the fixed sidebar */
            padding: 20px;
            flex-grow: 1;
            height: 100vh; /* Full height for independent scrolling */
            overflow-y: auto; /* Enable scrolling for content */
            box-sizing: border-box; /* Ensure proper padding behavior */
        }
        
        header {
            margin-bottom: 20px;
            color: #fff;
        }
        
        header .highlight {
            color: #ff6b6b;
        }
        
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 10px;
            padding: 20px;
            flex: 1;
            text-align: center;
            color: #fff;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h2 {
            margin: 0;
            font-size: 40px;
        }
        
        .stat-card p {
            margin-top: 5px;
            color: #f0f0f0;
        }
        
        .list-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px; /* Space between different rows */
        }
        
        .users-list, .books-list {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 10px;
            padding: 20px;
            flex: 1;
            color: #fff;
            margin-bottom: 20px; /* Add spacing below each list */
        }
        
        .scrollable-table {
            max-height: 250px;
            overflow-y: scroll;
            margin-top: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        th {
            background-color: rgba(43, 47, 60, 1);
            color: #ffffff;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        
        .action-button {
            cursor: pointer;
            background-color: #ff6b6b;
            border: none;
            color: #fff;
            padding: 6px 12px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        
        .action-button:hover {
            background-color: #e05757;
        }
        
        @media (max-width: 768px) {
            .stats-container, .list-container {
                flex-direction: column;
            }
        
            .dashboard-container {
                margin-left: 0;
            }
        }
        .btn {
    display: inline-block;
    background-color: #ff6b6b;
    color: #ffffff;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 3px;
    font-weight: bold;
    text-align: center;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
}

.btn:hover {
    background-color:rgb(225, 44, 44);
    transform: scale(1.05);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
}

.btn i {
    margin-right: 6px;
    font-size: 12px;
    vertical-align: middle;
}


    </style>
</head>
<body>
    <div class="content">
        <div class="sidebar">
            <h2>Library Menu</h2>
            <a href="./dashboard.php">Dashboard</a>
            <a href="./borrow.php">Borrow/Return</a>
            <a href="./add.php">Add/Remove</a>
            <a href="./visitors.php">Visitors Record</a>
            <a href="manageusers.php">Manage Users</a>
            <a href="reports.php">Reports</a>
            <a href="adminlogout.php" class="logout-button">Logout</a>
        </div>

        <div class="dashboard-container">
            <header>
                <h1>Hi, <span class="highlight"><?= htmlspecialchars($loggedInUser) ?>!</span> Welcome to Library Management System</h1>
                <p><?= date("M d, Y | l, h:i A") ?></p>
            </header>

            <div class="stats-container">
                <div class="stat-card">
                    <h2><?= $totalVisitors ?></h2>
                    <p>Total Visitors</p>
                </div>
                <div class="stat-card">
                    <h2><?= $borrowedBooks ?></h2>
                    <p>Borrowed Books</p>
                </div>
                <div class="stat-card">
                    <h2><?= $overdueBooks ?></h2>
                    <p>Overdue Books</p>
                </div>
                <div class="stat-card">
                    <h2><?= $newMembers ?></h2>
                    <p>New Members</p>
                </div>
            </div>

            <div class="list-container">
                <div class="users-list">
                    <h2>Users List</h2>
                    <a href="export_csv.php?type=users" class="btn">
                        <i class="fas fa-download"></i> Export Users List
                    </a>

                    <div class="scrollable-table">
                        <table>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created At</th>
                            </tr>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $user['user_id'] ?></td>
                                    <td><?= $user['name'] ?></td>
                                    <td><?= $user['email'] ?></td>
                                    <td><?= $user['created_at'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>

                <div class="books-list">
                    <h2>Books List</h2>
                    <a href="export_csv.php?type=books" class="btn">
                        <i class="fas fa-download"></i>Export Books List
                    </a>

                    <div class="scrollable-table">
                        <table>
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Available</th>
                            </tr>
                            <?php while ($book = $books->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $book['book_id'] ?></td>
                                    <td><?= $book['title'] ?></td>
                                    <td><?= $book['author'] ?></td>
                                    <td><?= $book['available'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="list-container">
    <div class="users-list">
        <h2>Borrowed List</h2>
        <a href="export_csv.php?type=borrowed" class="btn">
            <i class="fas fa-download"></i>Export Borrowed List
        </a>
        <div class="scrollable-table">
            <table>
                <tr>
                    <th>Borrow ID</th>
                    <th>Book ID</th>
                    <th>User ID</th>
                    <th>Borrow Date</th>
                    <th>Borrow Time</th>
                </tr>
                <?php while ($borrowed = $borrowedList->fetch_assoc()): ?>
                    <tr>
                        <td><?= $borrowed['borrow_id'] ?></td>
                        <td><?= $borrowed['book_id'] ?></td>
                        <td><?= $borrowed['user_id'] ?></td>
                        <td><?= $borrowed['borrow_date'] ?></td>
                        <td><?= $borrowed['borrow_time'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <div class="books-list">
        <h2>Returned List</h2>
        <a href="export_csv.php?type=returned" class="btn">
            <i class="fas fa-download"></i>Export Returned List
        </a>
        <div class="scrollable-table">
            <table>
                <tr>
                    <th>Borrow ID</th>
                    <th>Book ID</th>
                    <th>User ID</th>
                    <th>Borrow Date</th>
                    <th>Borrow Time</th>
                    <th>Return Date</th>
                    <th>Return Time</th>
                </tr>
                <?php while ($returned = $returnedList->fetch_assoc()): ?>
                    <tr>
                        <td><?= $returned['borrow_id'] ?></td>
                        <td><?= $returned['book_id'] ?></td>
                        <td><?= $returned['user_id'] ?></td>
                        <td><?= $returned['borrow_date'] ?></td>
                        <td><?= $returned['borrow_time'] ?></td>
                        <td><?= $returned['return_date'] ?></td>
                        <td><?= $returned['return_time'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

        </div>
    </div>
</body>
</html>
