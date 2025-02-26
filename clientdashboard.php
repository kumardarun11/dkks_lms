<?php
session_start(); // Start the session

// Set timezone to your preferred timezone
date_default_timezone_set('Asia/Kolkata'); // Replace 'Your/Timezone' with your actual timezone string, e.g., 'Asia/Kolkata'

// Database connection
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: clientsigninup.php");
    exit();
}

// Fetch the logged-in user's name and user_id from the session
$loggedInUser = $_SESSION['username'];
$userQuery = $conn->query("SELECT user_id, status FROM users WHERE name = '$loggedInUser'");
$userData = $userQuery->fetch_assoc();

if (!$userData) {
    // If no user data is found, destroy the session and redirect
    session_destroy();
    header("Location: clientsigninup.php");
    exit();
}

$userId = $userData['user_id'];
$status = strtolower($userData['status']); // Ensure case consistency

// If status is NOT "active", log out the user
if ($status !== 'active') {
    session_destroy();
    echo "<script>alert('Your account has been deactivated. You have been logged out.'); window.location.href='clientsigninup.php';</script>";
    exit();
}

// Fetch borrowed books data for the logged-in user (not returned yet)
$borrowedBooks = $conn->query("
    SELECT bb.borrow_id, b.book_id, b.title, b.author, bb.borrow_date, bb.borrow_time 
    FROM borrowed_books bb 
    INNER JOIN books b ON bb.book_id = b.book_id 
    WHERE bb.user_id = '$userId' AND bb.return_date IS NULL
");

// Fetch returned books data for the logged-in user
$returnedBooks = $conn->query("
    SELECT b.book_id, b.title, b.author, bb.return_date, bb.return_time 
    FROM borrowed_books bb 
    INNER JOIN books b ON bb.book_id = b.book_id 
    WHERE bb.user_id = '$userId' AND bb.return_date IS NOT NULL
");

// Fetch all books for the book list table
$books = $conn->query("SELECT * FROM books");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            overflow-y: auto;
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
            width: 100%;
        }

        /* Sidebar */
        .sidebar {
            width: 180px;
            background-color: rgba(43, 47, 60, 1);
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
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
            margin-left: 220px;
            padding: 20px;
            flex-grow: 1;
        }

        header {
            margin-bottom: 20px;
            color: #fff;
        }

        header .highlight {
            color: #ff6b6b;
        }

        .list-container {
            display: flex;
            gap: 20px;
            flex-direction: column;
        }

        .books-list, .borrow-list, .return-list {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 10px;
            padding: 20px;
            color: #fff;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <!-- Side Navigation Bar -->
        <div class="sidebar">
            <h2>Library Menu</h2>
            <a href="./clientdashboard.php">Dashboard</a>
            <a href="clientlogout.php" class="logout-button">Logout</a>
        </div>

        <!-- Main Content -->
        <div class="dashboard-container">
            <header>
                <h1>Hi, <span class="highlight"><?= htmlspecialchars($loggedInUser) ?>!</span> Welcome to Your Dashboard</h1>
                <p><?= date("M d, Y | l, h:i A") ?></p>
            </header>

            <div class="list-container">
                <!-- Borrowed Books -->
                <div class="borrow-list">
                    <h3>Borrowed Books</h3>
                    <div class="scrollable-table">
                        <table>
                            <tr>
                                <th>Borrow ID</th>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Borrow Date</th>
                                <th>Borrow Time</th>
                            </tr>
                            <?php while ($borrow = $borrowedBooks->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $borrow['borrow_id'] ?></td>
                                    <td><?= $borrow['book_id'] ?></td>
                                    <td><?= $borrow['title'] ?></td>
                                    <td><?= $borrow['author'] ?></td>
                                    <td><?= $borrow['borrow_date'] ?></td>
                                    <td><?= $borrow['borrow_time'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>

                <!-- Returned Books -->
                <div class="return-list">
                    <h3>Returned Books</h3>
                    <div class="scrollable-table">
                        <table>
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Return Date</th>
                                <th>Return Time</th>
                            </tr>
                            <?php while ($return = $returnedBooks->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $return['book_id'] ?></td>
                                    <td><?= $return['title'] ?></td>
                                    <td><?= $return['author'] ?></td>
                                    <td><?= $return['return_date'] ?></td>
                                    <td><?= $return['return_time'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>

                <!-- Book List -->
                <div class="books-list">
                    <h3>Books List</h3>
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
        </div>
    </div>
</body>
</html>
