
<?php
session_start();
require_once("../includes/db.php");

// Optional: redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}
 $userId = $_SESSION["user_id"];
$sql = "SELECT entry_date, content, created_at FROM diaries WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Your Diary Entries</title>
    <link rel="stylesheet" href="../assets/css/diary-entry.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
  <h2>📚 Your Diary Entries</h2>
  <a href="../dashboard.php">← Back to Dashboard</a>
  <hr><br>

  <?php while ($row = $result->fetch_assoc()): ?>
    <div class="entry">
      <div class="entry-date"><?= htmlspecialchars($row["entry_date"]) ?></div>
      <p><?= nl2br(htmlspecialchars($row["content"])) ?></p>
    </div>
  <?php endwhile; ?>

  <?php
  $stmt->close();
  $conn->close();
  ?>
</body>
</html>
