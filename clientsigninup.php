<?php
// Start the session
session_start();

// Database connection
$host = "localhost"; // Change as needed
$username = "root"; // Default username for MySQL
$password = ""; // Default password for MySQL
$dbname = "library_db"; // Name of your database
$conn = new mysqli($host, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


// Handle Signup Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
  $name = $_POST['name']; // Retrieve the name field
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  // Prepare and execute query to insert user
  $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $email, $password);
  if ($stmt->execute()) {
      echo "<script>alert('Signup successful! Please login.');</script>";
  } else {
      echo "<script>alert('Signup failed! Email may already exist.');</script>";
  }
  $stmt->close();
}


// Handle Login Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
  $email = trim($_POST['email']); // Trim spaces
  $password = trim($_POST['password']);

  // Prepare SQL statement
  $stmt = $conn->prepare("SELECT user_id, name, email, password, status FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
      $stmt->bind_result($id, $name, $email, $hashed_password, $status);
      $stmt->fetch();
      
      // Convert status to lowercase to prevent case issues
      $status = strtolower(trim($status)); // Ensures "Pending" and "pending" are treated the same

      // 🚨 Block users with "pending" status
      if ($status === 'pending') {
          echo "<script>alert('Your account is pending approval. Please wait for admin activation.');</script>";
          exit(); // Stop further execution
      }

      // Verify password if user is active
      if (password_verify($password, $hashed_password)) {
          $_SESSION['username'] = $name;
          $_SESSION['user_email'] = $email;
          header("Location: clientdashboard.php");
          exit();
      } else {
          echo "<script>alert('Incorrect password!');</script>";
      }
  } else {
      echo "<script>alert('No account found with this email!');</script>";
  }
  $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login & Signup Form</title>
  <style>
    /* Include CSS styling here */
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    html, body {
      display: grid;
      height: 100%;
      width: 100%;
      place-items: center;
      background: url('background.jpg') no-repeat center center fixed; /* Replace 'your-image-url.jpg' with your actual image URL */
      background-size: cover;
    }
    ::selection {
      background: #1a75ff;
      color: #fff;
    }
    .wrapper {
  overflow: hidden;
  max-width: 390px;
  background: rgba(255, 255, 255, 0.5); /* Semi-transparent white background */
  backdrop-filter: blur(10px); /* Adds a blur effect */
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.1);
}

    .wrapper .title-text {
      display: flex;
      width: 200%;
    }
    .wrapper .title {
      width: 50%;
      font-size: 35px;
      font-weight: 600;
      text-align: center;
      transition: all 0.6s cubic-bezier(0.68,-0.55,0.265,1.55);
    }
    .wrapper .slide-controls {
      position: relative;
      display: flex;
      height: 50px;
      width: 100%;
      overflow: hidden;
      margin: 30px 0 10px 0;
      justify-content: space-between;
      border: 1px solid lightgrey;
      border-radius: 15px;
    }
    .slide-controls .slide {
      height: 100%;
      width: 100%;
      color: #fff;
      font-size: 18px;
      font-weight: 500;
      text-align: center;
      line-height: 48px;
      cursor: pointer;
      z-index: 1;
      transition: all 0.6s ease;
    }
    .slide-controls label.signup {
      color: #000;
    }
    .slide-controls .slider-tab {
      position: absolute;
      height: 100%;
      width: 50%;
      left: 0;
      z-index: 0;
      border-radius: 15px;
      background: -webkit-linear-gradient(left, #003366, #004080, #0059b3, #0073e6);
      transition: all 0.6s cubic-bezier(0.68,-0.55,0.265,1.55);
    }
    input[type="radio"] {
      display: none;
    }
    #signup:checked ~ .slider-tab {
      left: 50%;
    }
    #signup:checked ~ label.signup {
      color: #fff;
      cursor: default;
      user-select: none;
    }
    #signup:checked ~ label.login {
      color: #000;
    }
    #login:checked ~ label.signup {
      color: #000;
    }
    #login:checked ~ label.login {
      cursor: default;
      user-select: none;
    }
    .wrapper .form-container {
      width: 100%;
      overflow: hidden;
    }
    .form-container .form-inner {
      display: flex;
      width: 200%;
    }
    .form-container .form-inner form {
      width: 50%;
      transition: all 0.6s cubic-bezier(0.68,-0.55,0.265,1.55);
    }
    form .field {
      height: 50px;
      width: 100%;
      margin-top: 20px;
    }
    form .field input {
      height: 100%;
      width: 100%;
      outline: none;
      padding-left: 15px;
      border-radius: 15px;
      border: 1px solid lightgrey;
      border-bottom-width: 2px;
      font-size: 17px;
      transition: all 0.3s ease;
    }
    form .field input:focus {
      border-color: #1a75ff;
    }
    form .field input::placeholder {
      color: #999;
      transition: all 0.3s ease;
    }
    form .field input:focus::placeholder {
      color: #1a75ff;
    }
    form .btn {
      height: 50px;
      width: 100%;
      border-radius: 15px;
      position: relative;
      overflow: hidden;
    }
    form .btn .btn-layer {
      height: 100%;
      width: 300%;
      position: absolute;
      left: -100%;
      background: -webkit-linear-gradient(right, #003366, #004080, #0059b3, #0073e6);
      border-radius: 15px;
      transition: all 0.4s ease;
    }
    form .btn:hover .btn-layer {
      left: 0;
    }
    form .btn input[type="submit"] {
      height: 100%;
      width: 100%;
      z-index: 1;
      position: relative;
      background: none;
      border: none;
      color: #fff;
      padding-left: 0;
      border-radius: 15px;
      font-size: 20px;
      font-weight: 500;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="title-text">
      <div class="title login">Client Login</div>
      <div class="title signup">Client Signup</div>
    </div>
    <div class="form-container">
      <div class="slide-controls">
        <input type="radio" name="slide" id="login" checked>
        <input type="radio" name="slide" id="signup">
        <label for="login" class="slide login">Login</label>
        <label for="signup" class="slide signup">Signup</label>
        <div class="slider-tab"></div>
      </div>
      <div class="form-inner">
        <!-- Login Form -->
        <form action="" method="POST" class="login">
          <div class="field">
            <input type="text" name="email" placeholder="Email Address" required>
          </div>
          <div class="field">
            <input type="password" name="password" placeholder="Password" required>
          </div>
          <div class="pass-link"><a href="#">Forgot password?</a></div>
          <div class="field btn">
            <div class="btn-layer"></div>
            <input type="submit" name="login" value="Login">
          </div>
          <div class="signup-link">Not a member? <a href="">Signup now</a></div>
        </form>

       <!-- Signup Form -->
<form action="" method="POST" class="signup">
  <div class="field">
    <input type="text" name="name" placeholder="Full Name" required>
  </div>
  <div class="field">
    <input type="text" name="email" placeholder="Email Address" required>
  </div>
  <div class="field">
    <input type="password" name="password" placeholder="Password" required>
  </div>
  <div class="field">
    <input type="password" name="confirm_password" placeholder="Confirm password" required>
  </div>
  <div class="field btn">
    <div class="btn-layer"></div>
    <input type="submit" name="signup" value="Signup">
  </div>
</form>

      </div>
    </div>
  </div>
  <script>
    const loginText = document.querySelector(".title-text .login");
    const loginForm = document.querySelector("form.login");
    const loginBtn = document.querySelector("label.login");
    const signupBtn = document.querySelector("label.signup");
    const signupLink = document.querySelector("form .signup-link a");

    signupBtn.onclick = () => {
      loginForm.style.marginLeft = "-50%";
      loginText.style.marginLeft = "-50%";
    };

    loginBtn.onclick = () => {
      loginForm.style.marginLeft = "0%";
      loginText.style.marginLeft = "0%";
    };

    signupLink.onclick = () => {
      signupBtn.click();
      return false;
    };
  </script>
</body>
</html>
