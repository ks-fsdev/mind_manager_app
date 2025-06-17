<?php
session_start();

$conn = new mysqli("localhost", "root", "", "mind_manager");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = trim($_POST["identifier"]); // username or email
    $password = trim($_POST["password"]);

    if (empty($identifier) || empty($password)) {
        $errors[] = "Both fields are required.";
    }

    if (empty($errors)) {
        $sql = "SELECT id, username, password FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $username, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $username;

                header("Location: ../dashboard.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No user found.";
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
    <title>Mind Manager - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/auth-style.css">
</head>
<body>
<div class="wrapper">
    <h2>Login</h2>

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
        <label>Email or Username:</label><br>
        <input type="text" name="identifier" value="<?= htmlspecialchars($identifier ?? '') ?>">
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
