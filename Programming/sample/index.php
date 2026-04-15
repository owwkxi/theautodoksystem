<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f7fc;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .container {
        width: 100%;
        max-width: 500px;
        background-color: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 20px;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        font-size: 14px;
        margin-bottom: 5px;
        color: #555;
    }

    input[type="text"],
    input[type="password"],
    input[type="tel"] {
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }

    button {
        padding: 10px;
        font-size: 16px;
        color: white;
        background-color: #5cb85c;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #4cae4c;
    }

    .form-toggle {
        text-align: center;
        margin-top: 10px;
    }

    .form-toggle a {
        color: #007bff;
        text-decoration: none;
        font-size: 14px;
    }

    .form-toggle a:hover {
        text-decoration: underline;
    }

    .form-container {
        display: none;
    }

    .form-container.active {
        display: block;
    }

    .gif-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .gif-container img {
        max-width: 100%;
        height: auto;
    }
    </style>
</head>

<body>

    <div class="container">

        <!-- GIF at the top of the page -->
        <div class="gif-container">
            <img src="cat.gif" alt="Loading GIF">
        </div>

        <h2>Login</h2>

        <!-- Login Form -->
        <div class="form-container active">
            <form method="POST" action="authenticate.php">
                <label>Username:</label>
                <input type="text" name="username" required><br>
                <label>Password:</label>
                <input type="password" name="password" required><br>
                <button type="submit">Login</button>
            </form>
            <div class="form-toggle">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
    </div>

</body>

</html>