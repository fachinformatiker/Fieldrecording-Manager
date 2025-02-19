<?php
require_once 'config.php';
require_once 'functions.php';

$db = getDb();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tag   = isset($_POST['tag']) ? sanitizeInput($_POST['tag']) : '';
    $notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
    $id    = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($tag) {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE tags SET tag = :tag, notes = :notes WHERE id = :id");
            $stmt->execute([':tag' => $tag, ':notes' => $notes, ':id' => $id]);
            $message = "Tag updated.";
        } else {
            $stmt = $db->prepare("INSERT INTO tags (tag, notes) VALUES (:tag, :notes)");
            $stmt->execute([':tag' => $tag, ':notes' => $notes]);
            $message = "Tag added.";
        }
    } else {
        $message = "Please enter a Tag.";
    }
}

$stmt = $db->query("SELECT * FROM tags ORDER BY tag ASC");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<h2>Tags</h2>
<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<div class="two-column-container">
    <div class="left-column">
        <h3>Add new Tag</h3>
        <form action="tags.php" method="post">
            <input type="text" placeholder="Name" name="tag" id="tag" required><br><br>
            <textarea name="notes" placeholder="Notes" id="notes" rows="3" cols="40"></textarea><br><br>
            <button type="submit">Add</button>
        </form>
    </div>
    <div class="right-column">
        <h3>All Tags</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tag</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td><?php echo $tag['id']; ?></td>
                        <td><?php echo htmlspecialchars($tag['tag']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($tag['notes'])); ?></td>
                        <td>
                            <form action="tags.php" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                                <input type="text" name="tag" value="<?php echo htmlspecialchars($tag['tag']); ?>" required>
                                <input type="text" name="notes" value="<?php echo htmlspecialchars($tag['notes']); ?>">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
