<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection without database
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected successfully<br>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS recipe_db";
    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    echo "Database created successfully<br>";
    
    // Select the database
    $conn->select_db("recipe_db");
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS recipes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            instructions TEXT,
            cooking_time INT,
            servings INT,
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS ingredients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS recipe_ingredients (
            recipe_id INT,
            ingredient_id INT,
            quantity VARCHAR(50),
            FOREIGN KEY (recipe_id) REFERENCES recipes(id),
            FOREIGN KEY (ingredient_id) REFERENCES ingredients(id),
            PRIMARY KEY (recipe_id, ingredient_id)
        )"
    ];
    
    foreach ($tables as $sql) {
        if ($conn->query($sql) === FALSE) {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }
    
    echo "Tables created successfully<br>";
    
    // Insert sample ingredients
    $ingredients = [
        'chicken breast',
        'olive oil',
        'garlic',
        'onion',
        'tomatoes',
        'pasta',
        'basil',
        'parmesan cheese',
        'salt',
        'black pepper',
        'rice',
        'soy sauce',
        'ginger',
        'carrots',
        'bell peppers'
    ];
    
    foreach ($ingredients as $ingredient) {
        $stmt = $conn->prepare("INSERT IGNORE INTO ingredients (name) VALUES (?)");
        $stmt->bind_param("s", $ingredient);
        $stmt->execute();
    }
    
    echo "Sample ingredients added successfully<br>";
    
    // Insert sample recipes
    $recipes = [
        [
            'title' => 'Classic Spaghetti with Tomato Sauce',
            'description' => 'A delicious Italian pasta dish with fresh tomato sauce',
            'instructions' => "1. Boil pasta according to package instructions\n2. Heat olive oil in a pan\n3. Sauté garlic and onions until fragrant\n4. Add tomatoes and cook for 15 minutes\n5. Season with salt and pepper\n6. Add fresh basil\n7. Serve with grated parmesan",
            'cooking_time' => 30,
            'servings' => 4,
            'image_url' => 'C:\xampp\htdocs\project\images\p2.jpg'
        ],
        [
            'title' => 'Chicken Stir Fry',
            'description' => 'Quick and easy Asian-inspired chicken stir fry',
            'instructions' => "1. Cut chicken into bite-sized pieces\n2. Heat oil in a wok\n3. Cook chicken until golden\n4. Add vegetables and stir fry\n5. Add soy sauce and ginger\n6. Serve hot with rice",
            'cooking_time' => 25,
            'servings' => 3,
            'image_url' => 'C:\xampp\htdocs\project\images\p2.jpg'
        ]
    ];
    
    foreach ($recipes as $recipe) {
        $stmt = $conn->prepare("INSERT INTO recipes (title, description, instructions, cooking_time, servings, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $recipe['title'], $recipe['description'], $recipe['instructions'], $recipe['cooking_time'], $recipe['servings'], $recipe['image_url']);
        $stmt->execute();
        
        $recipe_id = $conn->insert_id;
        
        // Add recipe ingredients
        if ($recipe['title'] === 'Classic Spaghetti with Tomato Sauce') {
            $ingredients_with_quantity = [
                ['pasta', '500g'],
                ['olive oil', '3 tablespoons'],
                ['garlic', '3 cloves'],
                ['onion', '1 medium'],
                ['tomatoes', '4 large'],
                ['basil', '1/2 cup'],
                ['parmesan cheese', '1/2 cup'],
                ['salt', 'to taste'],
                ['black pepper', 'to taste']
            ];
        } else {
            $ingredients_with_quantity = [
                ['chicken breast', '500g'],
                ['olive oil', '2 tablespoons'],
                ['garlic', '2 cloves'],
                ['carrots', '2 medium'],
                ['bell peppers', '2 medium'],
                ['soy sauce', '3 tablespoons'],
                ['ginger', '1 tablespoon'],
                ['rice', '2 cups']
            ];
        }
        
        foreach ($ingredients_with_quantity as $ing) {
            $stmt = $conn->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity) 
                                  SELECT ?, id, ? FROM ingredients WHERE name = ?");
            $stmt->bind_param("iss", $recipe_id, $ing[1], $ing[0]);
            $stmt->execute();
        }
    }
    
    echo "Sample recipes and their ingredients added successfully<br>";
    echo '<div style="margin-top: 20px;">Setup completed successfully! <a href="index.php">Go to homepage</a></div>';

} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 