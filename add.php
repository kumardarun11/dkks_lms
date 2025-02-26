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

$activeTab = "add"; // Default tab to show

// Variables to retain form values
$addBookId = $addTitle = $addAuthor = $addAvailable = "";
$removeBookId = "";

// Handle Add Book form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $activeTab = "add"; // Stay on the Add tab after submission
    $addBookId = $_POST['book_id'];
    $addTitle = $_POST['title'];
    $addAuthor = $_POST['author'];
    $addAvailable = $_POST['available'];

    if (empty($addBookId) || empty($addTitle) || empty($addAuthor) || empty($addAvailable)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO books (book_id, title, author, available) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $addBookId, $addTitle, $addAuthor, $addAvailable);
        if ($stmt->execute()) {
            echo "<script>alert('Book added successfully!');</script>";
            $addBookId = $addTitle = $addAuthor = $addAvailable = ""; // Clear fields after success
        } else {
            echo "<script>alert('Error: Unable to add the book.');</script>";
        }
        $stmt->close();
    }
}

// Handle Remove Book form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove'])) {
    $activeTab = "remove"; // Stay on the Remove tab after submission
    $removeBookId = $_POST['book_id'];

    if (empty($removeBookId)) {
        echo "<script>alert('Book ID is required to remove a book!');</script>";
    } else {
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $removeBookId);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Book removed successfully!');</script>";
                $removeBookId = ""; // Clear field after success
            } else {
                echo "<script>alert('No book found with the given ID!');</script>";
            }
        } else {
            echo "<script>alert('Error: Unable to remove the book.');</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add/Remove Book</title>
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
      max-width: 400px;
      width: 100%;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .title {
      text-align: center;
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 20px;
      color: #003366;
    }
    .slide-controls {
      display: flex;
      justify-content: space-between;
      position: relative;
      margin-bottom: 20px;
      border: 1px solid lightgrey;
      border-radius: 10px;
      overflow: hidden;
      z-index: 1;
    }
    input[type="radio"] {
      display: none;
    }
    .slide-controls label {
      flex: 1;
      text-align: center;
      padding: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      color: #666;
      z-index: 2;
    }
    #add:checked + label[for="add"],
    #remove:checked + label[for="remove"] {
      color: white;
      font-weight: bold;
    }
    .slider-tab {
      position: absolute;
      height: 100%;
      width: 50%;
      top: 0;
      left: 0;
      background: #0073e6;
      transition: all 0.4s ease;
      z-index: 1;
    }
    #remove:checked ~ .slider-tab {
      left: 50%;
    }
    .form-container {
      display: flex;
      overflow: hidden;
      margin-top: 10px;
    }
    form {
      flex: 1;
      display: none;
      flex-direction: column;
    }
    form.active {
      display: flex;
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
    <div class="title">Library Management</div>
    <div class="slide-controls">
      <input type="radio" name="toggle" id="add" checked>
      <label for="add">Add Book</label>
      <input type="radio" name="toggle" id="remove">
      <label for="remove">Remove Book</label>
      <div class="slider-tab"></div>
    </div>
    <div class="form-container">
      <form id="add-form" method="POST" class="active">
        <div class="field">
          <input type="text" name="book_id" placeholder="Book ID" required>
        </div>
        <div class="field">
          <input type="text" name="title" placeholder="Title" required>
        </div>
        <div class="field">
          <input type="text" name="author" placeholder="Author" required>
        </div>
        <div class="field">
          <input type="number" name="available" placeholder="Available Copies" required>
        </div>
        <button type="submit" name="add">Add Book</button>
      </form>
      <form id="remove-form" method="POST">
        <div class="field">
          <input type="text" name="book_id" placeholder="Book ID" required>
        </div>
        <button type="submit" name="remove">Remove Book</button>
      </form>
    </div>
  </div>
  <script>
    const addRadio = document.getElementById('add');
    const removeRadio = document.getElementById('remove');
    const addForm = document.getElementById('add-form');
    const removeForm = document.getElementById('remove-form');

    addRadio.addEventListener('change', () => {
      addForm.classList.add('active');
      removeForm.classList.remove('active');
    });

    removeRadio.addEventListener('change', () => {
      removeForm.classList.add('active');
      addForm.classList.remove('active');
    });
  </script>
</body>
</html>
