<?php
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    die("No ID specified.");
}

$recordingId = (int) $_GET['id'];
$db = getDb();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if ($password === ADMIN_PASSWORD) {
        $stmt = $db->prepare("UPDATE recordings SET deleted = 1 WHERE id = :id");
        $stmt->execute([':id' => $recordingId]);
        $message = "Recording was moved to the trash.";
    } else {
        $message = "Wrong Password!";
    }
}

include 'header.php';
?>

<h2>Delete recording</h2>
<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
    <p><a href="index.php">Back to the list</a></p>
<?php else: ?>
    <p>Are you sure to remove the recording with the ID <?php echo $recordingId; ?> ? I (I will set the deleted-flag)</p>
    <form action="delete.php?id=<?php echo $recordingId; ?>" method="post">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <input type="submit" value="Delete">
    </form>
<?php endif; ?>

<?php include 'footer.php'; ?>
