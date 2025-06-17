<?php

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}
$conn = new mysqli ("localhost", "root", "", "mind_manager");
//to-do list dashboard
$userId = $_SESSION["user_id"];
$today = date("Y-m-d");

// Add new task
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["new_task"])) {
    $task = trim($_POST["new_task"]);
    if (!empty($task)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task, status, created_at) VALUES (?, ?, 'pending', ?)");
        $stmt->bind_param("iss", $userId, $task, $today);
        $stmt->execute();
        $stmt->close();
    }
}

// Mark task as done
if (isset($_GET["done"])) {
    $taskId = intval($_GET["done"]);
    $stmt = $conn->prepare("UPDATE tasks SET status = 'done' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $taskId, $userId);
    $stmt->execute();
    $stmt->close();
}

// Delete task
if (isset($_GET["delete"])) {
    $taskId = intval($_GET["delete"]);
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $taskId, $userId);
    $stmt->execute();
    $stmt->close();
}

// Fetch today’s tasks
$stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND created_at = ? ORDER BY id DESC");
$stmt->bind_param("is", $userId, $today);
$stmt->execute();
$result = $stmt->get_result();
$todos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mind Manager - Dashboard</title>
  <link rel="stylesheet" href="assets/css/dashboard.css" />
</head>
<body>

  <!-- Top Bar -->
  <header class="dashboard-header">
  <div class="account-menu">
    <button class="account-button">
      👤 <?= htmlspecialchars($_SESSION["username"] ?? "Account") ?> ▼
    </button>
    <!-- dropdown menu (optional later) -->
  </div>
  <div class="dashboard-title">🧠 Mind Manager</div>

</header>


  <main class="dashboard-content">

    <!-- To-Do List -->
    <section class="todo-section">
      <h2>📝 To-Do List</h2>
      <div class="todo-container">
        <form action="" method="POST">
    <input type="text" name="new_task" placeholder="Add a new task..." required>
    <button type="submit">Add</button>
  </form>

  <ul class="todo-list">
    <?php if (empty($todos)): ?>
      <li>No tasks for today.</li>
    <?php else: ?>
      <?php foreach ($todos as $todo): ?>
        <li style="<?= $todo['status'] === 'done' ? 'text-decoration: line-through; opacity: 0.6;' : '' ?>">
    <?= htmlspecialchars($todo["task"]) ?>
    <?php if ($todo['status'] !== 'done'): ?>
        <div><a href="?done=<?= $todo["id"] ?>">✔️</a>
    <?php endif; ?>
    <a href="?delete=<?= $todo["id"] ?>">🗑️</a></div>
</li>

      <?php endforeach; ?>
    <?php endif; ?>
  </ul>
      </div>
    </section>

    <!-- Diary Section -->
    <section class="diary-section">
      <h2>📔 Diary</h2>
      <textarea placeholder="What's on your mind today?" class="diary-input"></textarea>
    </section>

    <!-- Blog Section -->
    <section class="blog-section">
      <h2>✍️ Write a Blog</h2>
      <form class="blog-form">
        <input type="text" placeholder="Blog Title" class="blog-title" />
        <textarea placeholder="Your blog content..." class="blog-content"></textarea>
        <button type="submit">Publish</button>
      </form>

      <div class="blog-feed">
        <h3>Recommended Blogs</h3>
        <!-- Blog entries will go here -->
      </div>
    </section>

  </main>

</body>
</html>
