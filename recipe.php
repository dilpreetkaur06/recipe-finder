<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$recipe_id = $_GET['id'];
$stmt = $conn->prepare("
    SELECT r.*, GROUP_CONCAT(CONCAT(ri.quantity, ' ', i.name) SEPARATOR '\n') as ingredients_list
    FROM recipes r
    LEFT JOIN recipe_ingredients ri ON r.id = ri.recipe_id
    LEFT JOIN ingredients i ON ri.ingredient_id = i.id
    WHERE r.id = ?
    GROUP BY r.id
");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$recipe = $result->fetch_assoc();

// Debug information
echo "<!-- Debug Info:
Recipe ID: " . $recipe_id . "
Recipe Title: " . $recipe['title'] . "
Ingredients List: " . ($recipe['ingredients_list'] ?? 'NULL') . "
-->";

// Let's also check the ingredients directly
$ingredients_stmt = $conn->prepare("
    SELECT i.name, ri.quantity 
    FROM recipe_ingredients ri 
    JOIN ingredients i ON ri.ingredient_id = i.id 
    WHERE ri.recipe_id = ?
");
$ingredients_stmt->bind_param("i", $recipe_id);
$ingredients_stmt->execute();
$ingredients_result = $ingredients_stmt->get_result();

// Debug ingredients count
echo "<!-- Number of ingredients found: " . $ingredients_result->num_rows . " -->";

// Check if user has liked/saved this recipe
$liked = false;
$saved = false;
if (isset($_SESSION['user_id'])) {
    $check_like = $conn->prepare("SELECT id FROM recipe_likes WHERE recipe_id = ? AND user_id = ?");
    $check_like->bind_param("ii", $recipe_id, $_SESSION['user_id']);
    $check_like->execute();
    $liked = $check_like->get_result()->num_rows > 0;

    $check_save = $conn->prepare("SELECT id FROM saved_recipes WHERE recipe_id = ? AND user_id = ?");
    $check_save->bind_param("ii", $recipe_id, $_SESSION['user_id']);
    $check_save->execute();
    $saved = $check_save->get_result()->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - Recipe Finder</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .recipe-detail {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .recipe-detail img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .recipe-detail h1 {
            color: #ff6b6b;
            margin-bottom: 1rem;
        }
        .recipe-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            color: #666;
        }
        .recipe-ingredients {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }
        .recipe-ingredients h2 {
            color: #ff6b6b;
            margin-bottom: 1rem;
        }
        .recipe-ingredients ul {
            list-style-position: inside;
        }
        .recipe-instructions {
            line-height: 1.8;
        }
        .recipe-instructions h2 {
            color: #ff6b6b;
            margin-bottom: 1rem;
        }
        .recipe-rating {
            margin: 2rem 0;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            color: #ddd;
            padding: 0 0.2rem;
        }
        .star-rating input:checked ~ label {
            color: #ffd700;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffd700;
        }
        .rating-form textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 100px;
        }
        .recipe-actions {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
        }
        .btn-like, .btn-save {
            padding: 0.5rem 1rem;
            border: 1px solid #ff6b6b;
            background: white;
            color: #ff6b6b;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-like:hover, .btn-save:hover,
        .btn-like.active, .btn-save.active {
            background: #ff6b6b;
            color: white;
        }
        .btn-like i, .btn-save i {
            font-size: 1.2rem;
        }
    </style>
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
        <article class="recipe-detail">
            <?php if (!empty($recipe['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
            <?php endif; ?>

            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
            
            <div class="recipe-meta">
                <?php if ($recipe['cooking_time']): ?>
                    <span>Cooking Time: <?php echo htmlspecialchars($recipe['cooking_time']); ?> minutes</span>
                <?php endif; ?>
                <?php if ($recipe['servings']): ?>
                    <span>Servings: <?php echo htmlspecialchars($recipe['servings']); ?></span>
                <?php endif; ?>
            </div>

            <div class="recipe-ingredients">
                <h2>Ingredients</h2>
                <ul>
                    <?php
                    if ($ingredients_result->num_rows > 0) {
                        while ($ingredient = $ingredients_result->fetch_assoc()) {
                            echo '<li>' . htmlspecialchars($ingredient['quantity'] . ' ' . $ingredient['name']) . '</li>';
                        }
                    } else {
                        echo '<li>No ingredients listed</li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="recipe-instructions">
                <h2>Instructions</h2>
                <?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?>
            </div>

            <div class="recipe-rating">
                <h3>Rate this Recipe</h3>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="rate_recipe.php" method="POST" class="rating-form">
                        <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" required>
                            <label for="star5">★</label>
                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4">★</label>
                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3">★</label>
                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2">★</label>
                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1">★</label>
                        </div>
                        <textarea name="comment" placeholder="Write your review (optional)"></textarea>
                        <button type="submit" class="btn-primary">Submit Rating</button>
                    </form>
                <?php else: ?>
                    <p>Please <a href="login.php">login</a> to rate this recipe.</p>
                <?php endif; ?>
            </div>

            <div class="recipe-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn-like <?php echo $liked ? 'active' : ''; ?>" 
                            data-recipe-id="<?php echo $recipe_id; ?>">
                        <i class="fas fa-heart"></i> 
                        <span><?php echo $liked ? 'Liked' : 'Like'; ?></span>
                    </button>
                    <button class="btn-save <?php echo $saved ? 'active' : ''; ?>"
                            data-recipe-id="<?php echo $recipe_id; ?>">
                        <i class="fas fa-bookmark"></i>
                        <span><?php echo $saved ? 'Saved' : 'Save'; ?></span>
                    </button>
                <?php else: ?>
                    <p>Please <a href="login.php">login</a> to like or save recipes.</p>
                <?php endif; ?>
            </div>
        </article>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Recipe Finder. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 