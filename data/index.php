<?php
if (file_exists(__DIR__ . "/install.php")) {
    header("Location: install.php");
    exit;
}

require_once 'config.php';
require_once 'functions.php';

$db = getDb();

$whereClauses = [];
$params = [];

if (!empty($_GET['name'])) {
    $whereClauses[] = "name LIKE :name";
    $params[':name'] = "%" . sanitizeInput($_GET['name']) . "%";
}

if (!empty($_GET['date'])) {
    $whereClauses[] = "date(created_at) = :date";
    $params[':date'] = sanitizeInput($_GET['date']);
}

$query = "SELECT * FROM recordings WHERE deleted = 0";
if (count($whereClauses) > 0) {
    $query .= " AND " . implode(" AND ", $whereClauses);
}
$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$recordings = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<center><h2>All Recordings</h2></center>

<div class="one-column-container">
    <div class="one-column">

<center>
<form method="get" action="index.php">
    <input type="text" name="name" id="name" placeholder="Name" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
    <input type="text" name="date" id="date" placeholder="Date (YYYY-MM-DD)" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
    <button type="submit">Filter</button>
</form>
</center>

<br>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Uploaded on</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recordings as $record): ?>
            <tr>
                <td><?php echo $record['id']; ?></td>
                <td><?php echo htmlspecialchars($record['name']); ?></td>
                <td><?php echo $record['created_at']; ?></td>
                <td>
                    <a href="detail.php?id=<?php echo $record['id']; ?>">Details</a> | 
                    <a href="edit.php?id=<?php echo $record['id']; ?>">Edit</a> | 
                    <a href="delete.php?id=<?php echo $record['id']; ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>
</div>

<?php include 'footer.php'; ?>
