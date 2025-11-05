<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe_id = $_POST['recipe_id'];
    $action = $_POST['action']; // 'like' or 'save'
    $user_id = $_SESSION['user_id'];

    try {
        if ($action === 'like') {
            $check_stmt = $conn->prepare("SELECT id FROM recipe_likes WHERE recipe_id = ? AND user_id = ?");
            $check_stmt->bind_param("ii", $recipe_id, $user_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                // Unlike
                $stmt = $conn->prepare("DELETE FROM recipe_likes WHERE recipe_id = ? AND user_id = ?");
                $message = 'Recipe unliked';
            } else {
                // Like
                $stmt = $conn->prepare("INSERT INTO recipe_likes (recipe_id, user_id) VALUES (?, ?)");
                $message = 'Recipe liked';
            }
        } else {
            $check_stmt = $conn->prepare("SELECT id FROM saved_recipes WHERE recipe_id = ? AND user_id = ?");
            $check_stmt->bind_param("ii", $recipe_id, $user_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                // Unsave
                $stmt = $conn->prepare("DELETE FROM saved_recipes WHERE recipe_id = ? AND user_id = ?");
                $message = 'Recipe removed from saved';
            } else {
                // Save
                $stmt = $conn->prepare("INSERT INTO saved_recipes (recipe_id, user_id) VALUES (?, ?)");
                $message = 'Recipe saved';
            }
        }

        $stmt->bind_param("ii", $recipe_id, $user_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error processing request']);
    }
}
?>
