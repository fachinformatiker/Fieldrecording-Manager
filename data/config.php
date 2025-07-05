<?php
// change this please
define('ADMIN_PASSWORD', 'supersecretpassword');

// please don't change anything below here
define('DATA_DIR', __DIR__ . '/data');
define('DB_PATH', __DIR__ . '/data/database.sqlite');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('COUNTER_FILE', __DIR__ . '/data/counter.txt');
define('LOG_DIR', __DIR__ . '/logs');
define('LOG_FILE', __DIR__ . '/logs/app.log');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
