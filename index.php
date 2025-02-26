<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup</title>
    <style>
        /* Basic styles for the background and popup */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
        }

        #popup {
            width: 350px;
            padding: 30px;
            background: #fff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        #popup h2 {
            margin-bottom: 25px;
            font-size: 22px;
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 10px 5px;
            font-size: 16px;
            color: #fff;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn.admin {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
        }

        .btn.user {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div id="popup">
        <h2>Are you an Admin or a Client?</h2>
        <a href="adminsigninup.php" class="btn admin">Admin</a>
        <a href="clientsigninup.php" class="btn user">Client</a>
    </div>
</body>
</html>
