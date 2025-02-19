<?php
require_once 'config.php';
require_once 'functions.php';

$db = getDb();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
    $notes    = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
    $id       = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($location) {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE locations SET location = :location, notes = :notes WHERE id = :id");
            $stmt->execute([':location' => $location, ':notes' => $notes, ':id' => $id]);
            $message = "Location updated.";
        } else {
            $stmt = $db->prepare("INSERT INTO locations (location, notes) VALUES (:location, :notes)");
            $stmt->execute([':location' => $location, ':notes' => $notes]);
            $message = "Location added.";
        }
    } else {
        $message = "Please enter a Location.";
    }
}

$stmt = $db->query("SELECT * FROM locations ORDER BY location ASC");
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<h2>Locations</h2>
<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<div class="two-column-container">
    <div class="left-column">

<h3>Add new Location</h3>
<form action="locations.php" method="post">
    <input type="text" placeholder="Name" name="location" id="location" required><br><br>
    <textarea name="notes" placeholder="Notes" id="notes" rows="3" cols="40"></textarea><br><br>
    <button type="submit">Add</button>
</form>

</div>
<div class="right-column">

<h3>All Locations</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Location</th>
            <th>Notes</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($locations as $loc): ?>
            <tr>
                <td><?php echo $loc['id']; ?></td>
                <td><?php echo htmlspecialchars($loc['location']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($loc['notes'])); ?></td>
                <td>
                    <form action="locations.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
                        <input type="text" name="location" value="<?php echo htmlspecialchars($loc['location']); ?>" required>
                        <input type="text" name="notes" value="<?php echo htmlspecialchars($loc['notes']); ?>">
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
