<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipe_id = $_POST['recipe_id'];
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    try {
        // Check if user has already rated this recipe
        $check_stmt = $conn->prepare("SELECT id FROM recipe_ratings WHERE recipe_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $recipe_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing rating
            $stmt = $conn->prepare("UPDATE recipe_ratings SET rating = ?, comment = ? WHERE recipe_id = ? AND user_id = ?");
            $stmt->bind_param("isii", $rating, $comment, $recipe_id, $user_id);
        } else {
            // Insert new rating
            $stmt = $conn->prepare("INSERT INTO recipe_ratings (recipe_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $recipe_id, $user_id, $rating, $comment);
        }

        $stmt->execute();
        header("Location: recipe.php?id=" . $recipe_id . "&rated=1");
        exit();
    } catch (Exception $e) {
        header("Location: recipe.php?id=" . $recipe_id . "&error=1");
        exit();
    }
}
?>
