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

$allowed_domain = '@admin.com';

// Handle Signup Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if email ends with the allowed domain
    if (strpos($email, $allowed_domain) === false) {
        echo "<script>alert('Only emails ending with $allowed_domain are allowed.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT id FROM ausers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "<script>alert('Email already exists!');</script>";
        } else {
            // Insert new user into database
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO ausers (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            if ($stmt->execute()) {
                echo "<script>alert('Signup successful! Please login.');</script>";
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<script>alert('Signup failed! Please try again.');</script>";
            }
        }
        $stmt->close();
    }
}

// Handle Login Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email ends with the allowed domain
    if (strpos($email, $allowed_domain) === false) {
        echo "<script>alert('Only emails ending with $allowed_domain are allowed.');</script>";
    } else {
        // Fetch user details from database
        $stmt = $conn->prepare("SELECT id, name, password FROM ausers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                // Store session variables
                $_SESSION['username'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_id'] = $id;

                header("Location: dashboard.php");
                exit();
            } else {
                echo "<script>alert('Incorrect password!');</script>";
            }
        } else {
            echo "<script>alert('No account found with this email!');</script>";
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
      <div class="title login">Admin Login</div>
      <div class="title signup">Admin Signup</div>
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
    // Validate email domain
    function validateEmailDomain(email) {
        const allowedDomain = "@admin.com";
        if (!email.endsWith(allowedDomain)) {
            alert("Only emails ending with " + allowedDomain + " are allowed.");
            return false;
        }
        return true;
    }

    // Attach validation to forms
    document.querySelector('.signup form').onsubmit = function(e) {
        const email = document.querySelector('.signup input[name="email"]').value;
        if (!validateEmailDomain(email)) {
            e.preventDefault();
        }
    };

    document.querySelector('.login form').onsubmit = function(e) {
        const email = document.querySelector('.login input[name="email"]').value;
        if (!validateEmailDomain(email)) {
            e.preventDefault();
        }
    };
  </script>
</body>
</html>
