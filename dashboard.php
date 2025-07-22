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

//diary
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_diary"])) {
    $entry = trim($_POST["diary_entry"]);
    $userId = $_SESSION["user_id"];

    if (!empty($entry)) {
        $stmt = $conn->prepare("INSERT INTO diaries (user_id, entry_date, content, created_at) VALUES (?, CURDATE(), ?, NOW())");
        $stmt->bind_param("is", $userId, $entry);
        $stmt->execute();
        $stmt->close();
    }
}

// Blog Post
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["publish_blog"])) {
    $title = trim($_POST["blog_title"]);
    $content = trim($_POST["blog_content"]);

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO blogs (user_id, title, content, published_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $userId, $title, $content);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch blogs
$stmt = $conn->prepare("SELECT id, title, content, published_at FROM blogs WHERE user_id = ? ORDER BY published_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);
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
      👤 <?= htmlspecialchars($_SESSION["username"] ?? "Account") ?></button>
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

  <form method="POST" action="">
    <textarea name="diary_entry" placeholder="What's on your mind today?" class="diary-input" required></textarea>
    <br>
    <button type="submit" name="add_diary" class="btn-diary">Add Entry</button>
    <a href="diary/index.php" class="view-link">📚 View All Entries</a>
  </form>
</section>


    <!-- Blog Section -->
    <section class="blog-section">
  <h2>✍️ Write a Blog</h2>

  <form class="blog-form" method="POST" action="">
    <input type="text" name="blog_title" placeholder="Blog Title" class="blog-title" required />
    <textarea name="blog_content" placeholder="Your blog content..." class="blog-content" required></textarea>
    <button type="submit" name="publish_blog">Publish</button>
  </form>

  <div class="blog-feed">
    <h3>📰 Your Recent Blogs</h3>
    <?php if (empty($blogs)): ?>
      <p>You haven’t written anything yet.</p>
    <?php else: ?>
      <?php foreach ($blogs as $blog): ?>
        <div class="blog-entry">
          <h4><?= htmlspecialchars($blog["title"]) ?></h4>
          <small>🗓️ <?= htmlspecialchars($blog["published_at"]) ?></small>
          <p><?= nl2br(htmlspecialchars($blog["content"])) ?></p>
          <a href="blogs/view-blog.php?id=<?= $blog["id"] ?>" class="blog-view-btn">🔍 View Full Blog</a>

        </div>        
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>


  </main>

</body>
</html>
