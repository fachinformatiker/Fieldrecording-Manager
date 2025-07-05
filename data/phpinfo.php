<?php
// Temporary file to check PHP configuration
echo '<h2>PHP Upload Configuration</h2>';
echo '<table border="1" cellpadding="5">';
echo '<tr><th>Setting</th><th>Value</th></tr>';
echo '<tr><td>upload_max_filesize</td><td>' . ini_get('upload_max_filesize') . '</td></tr>';
echo '<tr><td>post_max_size</td><td>' . ini_get('post_max_size') . '</td></tr>';
echo '<tr><td>max_file_uploads</td><td>' . ini_get('max_file_uploads') . '</td></tr>';
echo '<tr><td>max_execution_time</td><td>' . ini_get('max_execution_time') . '</td></tr>';
echo '<tr><td>max_input_time</td><td>' . ini_get('max_input_time') . '</td></tr>';
echo '<tr><td>memory_limit</td><td>' . ini_get('memory_limit') . '</td></tr>';
echo '<tr><td>file_uploads</td><td>' . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . '</td></tr>';
echo '<tr><td>upload_tmp_dir</td><td>' . (ini_get('upload_tmp_dir') ?: 'Default') . '</td></tr>';
echo '</table>';

echo '<h3>$_FILES Debug (if form submitted)</h3>';
if (!empty($_FILES)) {
    echo '<pre>';
    print_r($_FILES);
    echo '</pre>';
} else {
    echo '<p>No files uploaded yet.</p>';
}

echo '<h3>Test Upload Form</h3>';
echo '<form method="post" enctype="multipart/form-data">';
echo '<input type="file" name="test_file" accept="audio/*">';
echo '<input type="submit" value="Test Upload">';
echo '</form>';
?>