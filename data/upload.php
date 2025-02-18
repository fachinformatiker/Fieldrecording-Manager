<?php
require 'config.php';
require 'functions.php';

$db = getDb();

$tags = $db->query("SELECT id, tag FROM tags ORDER BY tag")->fetchAll(PDO::FETCH_ASSOC);

$locations = $db->query("SELECT id, location FROM locations ORDER BY location")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php' ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Field Recording</title>
</head>
<body>
    <h2>Upload Field Recording</h2>
    <form action="upload_process.php" method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Recording Name" required><br>
        <input type="file" name="file" placeholder="File to be uploaded" required><br>

        <label for="tags">Tags:</label>
        <select name="tags[]" multiple>
            <?php foreach ($tags as $tag): ?>
                <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['tag']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="locations">Locations:</label>
        <select name="locations[]" multiple>
            <?php foreach ($locations as $location): ?>
                <option value="<?= htmlspecialchars($location['id']) ?>"><?= htmlspecialchars($location['location']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <textarea name="notes" placeholder="Notes"></textarea><br>

        <button type="submit">Upload</button>
    </form>
</body>
</html>

<?php include 'footer.php'; ?>
