<?php
$host = '127.0.0.1';
$db   = 'autopro_garage';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Create task_parts table dynamically if it doesn't exist for the new sync feature
    $pdo->exec("CREATE TABLE IF NOT EXISTS task_parts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        inventory_id INT NOT NULL,
        quantity INT NOT NULL,
        logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE
    )");
    
    // Create task_checklists table dynamically
    $pdo->exec("CREATE TABLE IF NOT EXISTS task_checklists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        item_name VARCHAR(100) NOT NULL,
        status VARCHAR(20) DEFAULT 'unchecked',
        notes TEXT,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
    )");
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
