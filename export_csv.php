<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "library_db";
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get export type
$type = $_GET['type'] ?? null;

if (!$type) {
    die("Invalid request");
}

// Define query and filename based on type
switch ($type) {
    case 'books':
        $sql = "SELECT book_id, title, author, available FROM books";
        $headers = ['ID', 'Title', 'Author', 'Available'];
        $filename = "Books_List.csv";
        break;

    case 'users':
        $sql = "SELECT user_id, name, email, created_at FROM users";
        $headers = ['ID', 'Name', 'Email', 'Created At'];
        $filename = "Users_List.csv";
        break;

    case 'borrowed':
        $sql = "SELECT borrow_id, book_id, user_id, borrow_date, borrow_time FROM borrowed_books";
        $headers = ['ID', 'Book ID', 'User ID', 'Borrow Date', 'Borrow Time'];
        $filename = "Borrowed_List.csv";
        break;
    
    case 'returned':
        $sql = "SELECT borrow_id, book_id, user_id, return_date, return_time FROM borrowed_books";
        $headers = ['ID', 'Book ID', 'User ID', 'Return Date', 'Return Time'];
        $filename = "Returned_List.csv";
        break;

    default:
        die("Invalid type");
}

// Execute query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write headers
    fputcsv($output, $headers);

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
} else {
    echo "No data found.";
}

$conn->close();
?>
