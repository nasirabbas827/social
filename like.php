<?php
include('config.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'You need to log in to like posts.']);
    exit;
}

$user_id = $_SESSION['id'];
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($post_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit;
}

// Check if the user already liked the post
$check_sql = "SELECT * FROM Likes WHERE PostID = $post_id AND UserID = $user_id";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    // Unlike the post
    $delete_sql = "DELETE FROM Likes WHERE PostID = $post_id AND UserID = $user_id";
    if (mysqli_query($conn, $delete_sql)) {
        // Get the updated like count
        $like_count_sql = "SELECT COUNT(*) AS like_count FROM Likes WHERE PostID = $post_id";
        $like_count_result = mysqli_query($conn, $like_count_sql);
        $like_count = mysqli_fetch_assoc($like_count_result)['like_count'];

        echo json_encode(['success' => true, 'like_count' => $like_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to unlike the post.']);
    }
} else {
    // Like the post
    $insert_sql = "INSERT INTO Likes (PostID, UserID) VALUES ($post_id, $user_id)";
    if (mysqli_query($conn, $insert_sql)) {
        // Get the updated like count
        $like_count_sql = "SELECT COUNT(*) AS like_count FROM Likes WHERE PostID = $post_id";
        $like_count_result = mysqli_query($conn, $like_count_sql);
        $like_count = mysqli_fetch_assoc($like_count_result)['like_count'];

        echo json_encode(['success' => true, 'like_count' => $like_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to like the post.']);
    }
}

exit;
?>
