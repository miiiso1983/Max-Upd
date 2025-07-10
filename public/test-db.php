<?php
/**
 * Database Connection Test for MaxCon SaaS
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $env = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$database = $_ENV['DB_DATABASE'] ?? 'maxcon_saas';
$username = $_ENV['DB_USERNAME'] ?? 'maxcon_saas';
$password = $_ENV['DB_PASSWORD'] ?? 'MaxCon@2025';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - Database Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .test-item {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .test-item.success { border-left-color: #28a745; background: #d4edda; }
        .test-item.error { border-left-color: #dc3545; background: #f8d7da; }
        .test-item.info { border-left-color: #17a2b8; background: #d1ecf1; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 MaxCon SaaS - Database Connection Test</h1>
        
        <?php
        echo '<div class="test-item info">';
        echo '<h3>📋 Configuration Info</h3>';
        echo '<p><strong>Host:</strong> ' . htmlspecialchars($host) . '</p>';
        echo '<p><strong>Database:</strong> ' . htmlspecialchars($database) . '</p>';
        echo '<p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>';
        echo '<p><strong>Password:</strong> ' . (strlen($password) > 0 ? str_repeat('*', strlen($password)) : 'Not set') . '</p>';
        echo '</div>';
        
        // Test 1: Check if MySQL extension is loaded
        echo '<div class="test-item ' . (extension_loaded('mysqli') ? 'success' : 'error') . '">';
        echo '<h3>🔧 MySQL Extension</h3>';
        if (extension_loaded('mysqli')) {
            echo '<p class="success">✅ MySQLi extension is loaded</p>';
        } else {
            echo '<p class="error">❌ MySQLi extension is not loaded</p>';
        }
        echo '</div>';
        
        // Test 2: Try to connect to database
        echo '<div class="test-item">';
        echo '<h3>🔗 Database Connection</h3>';
        
        try {
            $connection = new mysqli($host, $username, $password, $database);
            
            if ($connection->connect_error) {
                echo '<p class="error">❌ Connection failed: ' . htmlspecialchars($connection->connect_error) . '</p>';
                echo '<div class="test-item error">';
                echo '<h4>💡 Possible Solutions:</h4>';
                echo '<ul>';
                echo '<li>Check if the database exists in Cloudways panel</li>';
                echo '<li>Verify database credentials in Cloudways</li>';
                echo '<li>Make sure the database user has proper permissions</li>';
                echo '<li>Check if the database server is running</li>';
                echo '</ul>';
                echo '</div>';
            } else {
                echo '<p class="success">✅ Successfully connected to database!</p>';
                echo '<p class="info">Server info: ' . htmlspecialchars($connection->server_info) . '</p>';
                
                // Test 3: Check if we can query the database
                $result = $connection->query("SHOW TABLES");
                if ($result) {
                    $tables = [];
                    while ($row = $result->fetch_array()) {
                        $tables[] = $row[0];
                    }
                    
                    echo '<div class="test-item success">';
                    echo '<h4>📊 Database Tables (' . count($tables) . ' found)</h4>';
                    if (count($tables) > 0) {
                        echo '<p>Tables: ' . implode(', ', array_slice($tables, 0, 10));
                        if (count($tables) > 10) {
                            echo ' ... and ' . (count($tables) - 10) . ' more';
                        }
                        echo '</p>';
                    } else {
                        echo '<p class="info">No tables found. You may need to run migrations.</p>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="test-item error">';
                    echo '<p class="error">❌ Could not query database: ' . htmlspecialchars($connection->error) . '</p>';
                    echo '</div>';
                }
                
                $connection->close();
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Exception: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        echo '</div>';
        
        // Test 4: Check Laravel files
        echo '<div class="test-item">';
        echo '<h3>📁 Laravel Files</h3>';
        $files = [
            '../vendor/autoload.php' => 'Composer Autoloader',
            '../bootstrap/app.php' => 'Laravel Bootstrap',
            '../artisan' => 'Artisan CLI',
            '../.env' => 'Environment File'
        ];
        
        foreach ($files as $file => $name) {
            $exists = file_exists($file);
            $class = $exists ? 'success' : 'error';
            $icon = $exists ? '✅' : '❌';
            echo '<p class="' . $class . '">' . $icon . ' ' . $name . '</p>';
        }
        echo '</div>';
        
        // Test 5: PHP Version and Extensions
        echo '<div class="test-item info">';
        echo '<h3>🐘 PHP Environment</h3>';
        echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
        echo '<p><strong>Server Software:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</p>';
        
        $required_extensions = ['mysqli', 'pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'];
        echo '<h4>Required Extensions:</h4>';
        foreach ($required_extensions as $ext) {
            $loaded = extension_loaded($ext);
            $class = $loaded ? 'success' : 'error';
            $icon = $loaded ? '✅' : '❌';
            echo '<span class="' . $class . '">' . $icon . ' ' . $ext . '</span> ';
        }
        echo '</div>';
        ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">🔄 Try Laravel Again</a>
            <a href="index.bypass.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">🏠 Back to Home</a>
        </div>
        
        <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <h4>🛠️ Next Steps if Database Connection Failed:</h4>
            <ol>
                <li>Go to Cloudways panel → Application → Database</li>
                <li>Create a database named "maxcon_saas"</li>
                <li>Create a user "maxcon_saas" with password "MaxCon@2025"</li>
                <li>Grant all privileges to the user</li>
                <li>Run Laravel migrations: <code>php artisan migrate</code></li>
            </ol>
        </div>
    </div>
</body>
</html>
