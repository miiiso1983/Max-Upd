<?php
echo "PHP is working!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
phpinfo();
?>
