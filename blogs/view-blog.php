<?php
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$blogId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

// Handle blog update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_blog"])) {
    $newTitle = trim($_POST["title"]);
    $newContent = trim($_POST["content"]);

    if (!empty($newTitle) && !empty($newContent)) {
        $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $newTitle, $newContent, $blogId, $userId);
        $stmt->execute();
        $stmt->close();
        header("Location: view-blog.php?id=" . $blogId); // redirect to remove POST data
        exit;
    }
}

// Handle blog delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_blog"])) {
    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $blogId, $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: ../dashboard.php");
    exit;
}

// Fetch blog
$stmt = $conn->prepare("SELECT title, content, published_at FROM blogs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $blogId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if (!$blog) {
    echo "Blog not found.";
    exit;
}

$editMode = isset($_GET["edit"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($blog["title"]) ?></title>
  <link rel="stylesheet" href="../assets/css/view-blog.css">
  

</head>
<body>

<a href="../dashboard.php" class="back-link">← Back to Dashboard</a>

<div class="blog-box">
  <?php if ($editMode): ?>
    <form method="POST">
      <input type="text" name="title" value="<?= htmlspecialchars($blog["title"]) ?>" required>
      <textarea name="content" rows="10" required><?= htmlspecialchars($blog["content"]) ?></textarea>
      <div class="actions">
        <button type="submit" name="update_blog">💾 Save Changes</button>
        <a href="view-blog.php?id=<?= $blogId ?>">Cancel</a>
      </div>
    </form>
  <?php else: ?>
    <h2><?= htmlspecialchars($blog["title"]) ?></h2>
    <small>🗓️ <?= htmlspecialchars($blog["published_at"]) ?></small>
    <p><?= nl2br(htmlspecialchars($blog["content"])) ?></p>
    <div class="actions">
      <a href="?id=<?= $blogId ?>&edit=true">✏️ Edit</a>
      <button onclick="document.getElementById('deleteModal').style.display='flex'">🗑️ Delete</button>
    </div>
  <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal" style="display: none;">
  <div class="modal-content">
    <p>Are you sure you want to delete this blog?</p>
    <form method="POST">
      <button type="submit-btn" name="delete_blog">Yes, Delete</button>
      <button type="cancel-button" onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
    </form>
  </div>
</div>

</body>
</html>
