<?php
include('config.php');
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'You need to log in to share posts.']);
    exit;
}

$user_id = $_SESSION['id'];
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($post_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit;
}

// Insert share entry
$insert_sql = "INSERT INTO Shares (PostID, UserID, ShareDate) VALUES ($post_id, $user_id, NOW())";
if (mysqli_query($conn, $insert_sql)) {
    echo json_encode(['success' => true, 'message' => 'Post shared successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to share the post.']);
}

exit;
?>
