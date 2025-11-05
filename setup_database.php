<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection without database selected
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "Connected successfully<br>";

    // Read and execute database structure SQL
    $dbStructure = file_get_contents('database.sql');
    if ($dbStructure === false) {
        throw new Exception("Could not read database.sql");
    }

    // Split SQL into individual queries
    $queries = array_filter(array_map('trim', explode(';', $dbStructure)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            if (!$conn->query($query)) {
                throw new Exception("Error executing query: " . $conn->error . "\nQuery: " . $query);
            }
        }
    }
    
    echo "Database structure created successfully<br>";

    // Select the database for sample data
    $conn->select_db("recipe_db");

    // Read and execute sample data SQL
    $sampleData = file_get_contents('sample_data.sql');
    if ($sampleData === false) {
        throw new Exception("Could not read sample_data.sql");
    }

    // Split SQL into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sampleData)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            if (!$conn->query($query)) {
                throw new Exception("Error executing query: " . $conn->error . "\nQuery: " . $query);
            }
        }
    }
    
    echo "Sample data imported successfully<br>";
    echo "Database setup completed successfully!<br>";
    echo "<a href='index.php'>Go to homepage</a>";

} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 