<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Finder</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Recipe Finder</h1>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php
                session_start();
                if (isset($_SESSION['user_id'])) {
                    echo '<li><a href="profile.php">Profile</a></li>';
                    echo '<li><a href="logout.php">Logout</a></li>';
                } else {
                    echo '<li><a href="login.php">Login</a></li>';
                    echo '<li><a href="register.php">Register</a></li>';
                }
                ?>
            </ul>
        </div>
    </nav>

    <main class="container">
        <section class="search-section">
            <h2>Find Your Perfect Recipe</h2>
            <div class="search-options">
                <form action="search_results.php" method="GET" class="search-form">
                    <div class="search-by-name">
                        <input type="text" name="recipe_name" placeholder="Search recipes by name...">
                    </div>
                    <div class="search-by-ingredients">
                        <textarea name="ingredients" placeholder="Enter ingredients (one per line)"></textarea>
                    </div>
                    <button type="submit" class="search-btn">Search Recipes</button>
                </form>
            </div>
        </section>

        <section class="featured-recipes">
            <h2>Featured Recipes</h2>
            <div class="recipe-grid">
                <?php
                include 'config.php';
                $sql = "SELECT * FROM recipes ORDER BY created_at DESC LIMIT 6";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="recipe-card">';
                        echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['title']) . '">';
                        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                        echo '<p>' . substr(htmlspecialchars($row['description']), 0, 100) . '...</p>';
                        echo '<a href="recipe.php?id=' . $row['id'] . '" class="view-recipe">View Recipe</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No recipes found</p>';
                }
                ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Recipe Finder. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html> 