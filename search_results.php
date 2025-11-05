<?php
session_start();
include 'config.php';

$recipe_name = isset($_GET['recipe_name']) ? trim($_GET['recipe_name']) : '';
$ingredients = isset($_GET['ingredients']) ? trim($_GET['ingredients']) : '';

$recipes = [];
$search_type = '';
$error_message = '';

try {
    if (!empty($recipe_name)) {
        // Search by recipe name
        $search_type = 'name';
        $search_term = '%' . $recipe_name . '%';
        
        $sql = "SELECT DISTINCT r.* FROM recipes r WHERE r.title LIKE ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Failed to prepare query: " . $conn->error);
        }
        
        $stmt->bind_param("s", $search_term);
        
    } elseif (!empty($ingredients)) {
        // Search by ingredients
        $search_type = 'ingredients';
        $ingredient_list = array_filter(array_map('trim', explode("\n", $ingredients)));
        
        if (empty($ingredient_list)) {
            throw new Exception("No valid ingredients provided");
        }
        
        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($ingredient_list) - 1) . '?';
        
        $sql = "SELECT r.*, COUNT(DISTINCT i.id) as ingredient_match_count 
                FROM recipes r 
                JOIN recipe_ingredients ri ON r.id = ri.recipe_id 
                JOIN ingredients i ON ri.ingredient_id = i.id 
                WHERE i.name IN ($placeholders) 
                GROUP BY r.id 
                ORDER BY ingredient_match_count DESC";
                
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Failed to prepare query: " . $conn->error);
        }
        
        // Create array of parameters for bind_param
        $types = str_repeat('s', count($ingredient_list));
        $params = array_merge([$types], $ingredient_list);
        $refs = array();
        foreach($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        
        // Use call_user_func_array to bind the parameters with references
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Recipe Finder</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Recipe Finder</h1>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="container">
        <section class="search-results">
            <h2>Search Results</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php else: ?>
                <?php if ($search_type === 'name'): ?>
                    <p class="search-info">Showing results for: "<?php echo htmlspecialchars($recipe_name); ?>"</p>
                <?php elseif ($search_type === 'ingredients'): ?>
                    <p class="search-info">Showing recipes with ingredients:</p>
                    <ul class="ingredient-list">
                        <?php foreach ($ingredient_list as $ingredient): ?>
                            <li><?php echo htmlspecialchars($ingredient); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="recipe-grid">
                    <?php
                    if (isset($stmt)) {
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="recipe-card">';
                                if (!empty($row['image_url'])) {
                                    echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                                }
                                echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                                echo '<p>' . substr(htmlspecialchars($row['description']), 0, 100) . '...</p>';
                                echo '<a href="recipe.php?id=' . $row['id'] . '" class="view-recipe">View Recipe</a>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="no-results">No recipes found matching your search criteria.</p>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="search-again">
            <h3>Search Again</h3>
            <form action="search_results.php" method="GET" class="search-form">
                <div class="search-by-name">
                    <input type="text" name="recipe_name" placeholder="Search recipes by name...">
                </div>
                <div class="search-by-ingredients">
                    <textarea name="ingredients" placeholder="Enter ingredients (one per line)"></textarea>
                </div>
                <button type="submit" class="search-btn">Search Recipes</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Recipe Finder. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 