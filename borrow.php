<?php
// Set the PHP timezone
date_default_timezone_set('Asia/Kolkata');

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

$message = ""; // Message to display to the user
$borrow_id_message = ""; // To display the Borrow ID

// Handle Borrow Book form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrow'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_POST['user_id'];
    $borrow_date = date('Y-m-d');
    $borrow_time = date('H:i:s');

    if (empty($book_id) || empty($user_id)) {
        $message = "All fields are required!";
    } else {
        // Check if the book is available
        $result = $conn->query("SELECT available FROM books WHERE book_id = $book_id");
        $book = $result->fetch_assoc();

        if ($book && $book['available'] > 0) {
            // Borrow the book and retrieve Borrow ID
            $stmt = $conn->prepare("INSERT INTO borrowed_books (book_id, user_id, borrow_date, borrow_time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $book_id, $user_id, $borrow_date, $borrow_time);
            if ($stmt->execute()) {
                $borrow_id = $stmt->insert_id;
                $conn->query("UPDATE books SET available = available - 1 WHERE book_id = $book_id");
                $borrow_id_message = "Your Borrow ID is: $borrow_id";
                $message = "Book borrowed successfully!";
            } else {
                $message = "Error: Unable to borrow the book.";
            }
            $stmt->close();
        } else {
            $message = "This book is not available right now.";
        }
    }
}

// Handle Return Book form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return'])) {
    $borrow_id = $_POST['borrow_id'];
    $return_date = date('Y-m-d');
    $return_time = date('H:i:s');

    if (empty($borrow_id)) {
        $message = "Borrow ID is required to return a book!";
    } else {
        // Check if the book has already been returned
        $result = $conn->query("SELECT return_date FROM borrowed_books WHERE borrow_id = $borrow_id");
        $borrowed_book = $result->fetch_assoc();

        if ($borrowed_book) {
            if ($borrowed_book['return_date'] === null) {
                // Proceed with the return process
                $stmt = $conn->prepare("UPDATE borrowed_books SET return_date = ?, return_time = ? WHERE borrow_id = ?");
                $stmt->bind_param("ssi", $return_date, $return_time, $borrow_id);
                if ($stmt->execute()) {
                    $result = $conn->query("SELECT book_id FROM borrowed_books WHERE borrow_id = $borrow_id");
                    $borrowed_book = $result->fetch_assoc();
                    $book_id = $borrowed_book['book_id'];
                    $conn->query("UPDATE books SET available = available + 1 WHERE book_id = $book_id");
                    $message = "Book returned successfully!";
                } else {
                    $message = "Error: Unable to return the book.";
                }
                $stmt->close();
            } else {
                // The book has already been returned
                $message = "This book has already been returned.";
            }
        } else {
            $message = "Invalid Borrow ID. Please check and try again.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrow/Return Book</title>
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
      background: rgba(255, 255, 255, 0.45);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .title {
      text-align: center;
      font-size: 24px;
      font-weight: 600;
      font-weight: bold;
      margin-bottom: 20px;
      color:rgb(0, 51, 102);
    }
    .slide-controls {
      display: flex;
      justify-content: space-between;
      position: relative;
      margin-bottom: 20px;
      border: 1px solid grey;
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
      color:rgba(6, 6, 6, 0.63);
      font-weight: bold;
      z-index: 2;
    }
    #borrow:checked + label[for="borrow"],
    #return:checked + label[for="return"] {
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
    #return:checked ~ .slider-tab {
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
    .message {
      margin-bottom: 15px;
      text-align: center;
      font-size: 16px;
      color: #0073e6;
    }
    button {
      padding: 10px;
      border: none;
      border-radius: 5px;
      background: #0073e6;
      color: #ffffff;
      font-weight: bold;
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

    body {
      position: relative;
      margin: 0;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <a href="dashboard.php" class="back-to-dashboard">
      <span class="icon">&#8592;</span> <span class="text">Back to Dashboard</span>
    </a>
    <div class="title">Library Management</div>
    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>
    <?php if (!empty($borrow_id_message)) echo "<div class='message'>$borrow_id_message</div>"; ?>
    <div class="slide-controls">
      <input type="radio" name="toggle" id="borrow" checked>
      <label for="borrow">Borrow Book</label>
      <input type="radio" name="toggle" id="return">
      <label for="return">Return Book</label>
      <div class="slider-tab"></div>
    </div>
    <div class="form-container">
      <form id="borrow-form" method="POST" class="active">
        <div class="field">
          <input type="text" name="book_id" placeholder="Book ID" required>
        </div>
        <div class="field">
          <input type="text" name="user_id" placeholder="User ID" required>
        </div>
        <button type="submit" name="borrow">Borrow Book</button>
      </form>
      <form id="return-form" method="POST">
        <div class="field">
          <input type="text" name="borrow_id" placeholder="Borrow ID" required>
        </div>
        <button type="submit" name="return">Return Book</button>
      </form>
    </div>
  </div>
  <script>
    const borrowRadio = document.getElementById('borrow');
    const returnRadio = document.getElementById('return');
    const borrowForm = document.getElementById('borrow-form');
    const returnForm = document.getElementById('return-form');

    borrowRadio.addEventListener('change', () => {
      borrowForm.classList.add('active');
      returnForm.classList.remove('active');
    });

    returnRadio.addEventListener('change', () => {
      returnForm.classList.add('active');
      borrowForm.classList.remove('active');
    });
  </script>
</body>
</html>
