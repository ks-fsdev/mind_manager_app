<?php
session_start();

require_once "../includes/db.php";

$username = $email = $password = "";
$errors = []; // This array will hold any error messages

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql); // Prevent SQL injection with prepared statements
        $stmt->bind_param("ss", $username, $email); // Bind user input to the placeholders
        $stmt->execute();
        $stmt->store_result(); // Store result so we can count rows

        if ($stmt->num_rows > 0) {
            $errors[] = "Username or email already taken.";
        }
        $stmt->close(); 
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            //redirect to login page
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind Manager - Register</title>
    <link rel="stylesheet" href="../assets/css/auth-style.css">
</head>
<body>
<div class="wrapper">
    <h2>Register</h2>

        <?php
            if (!empty($errors)) {
            echo "<ul style='color: red;'>";
            foreach ($errors as $e) {
                echo "<li>$e</li>";
            }
            echo "</ul>";
            }
        ?>
    
    <form action="" method="post">
        <label>Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <button type="submit">Register</button>

    </form>

        <p>Already have an account? <a href="login.php"> log in</a></p>
</div>
</body>
</html> 