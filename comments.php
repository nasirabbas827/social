<?php
include('config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$message = ""; // For displaying messages to the user

// Fetch posts of the logged-in author
$sql = "SELECT * FROM BlogPosts WHERE author_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Handle comment deletion
if (isset($_GET['delete_comment_id'])) {
    $comment_id = $_GET['delete_comment_id'];

    // Delete the comment from the database
    $delete_sql = "DELETE FROM comments WHERE id = ? AND post_id IN (SELECT id FROM BlogPosts WHERE author_id = ?)";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "ii", $comment_id, $_SESSION["id"]);

    if (mysqli_stmt_execute($delete_stmt)) {
        $message = '<div class="alert alert-success text-center">Comment deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger text-center">Error deleting comment.</div>';
    }

    mysqli_stmt_close($delete_stmt);
    header("location: comments.php"); // Redirect after deletion
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Manage Comments</h2>
    
    <?php echo $message; // Display message ?>

    <div class="row">
        <?php
        // Display all posts by the logged-in author
        while ($post = mysqli_fetch_assoc($result)) {
            echo '<div class="col-md-12">';
            echo '<h3>' . htmlspecialchars($post['title']) . '</h3>';
            
            // Fetch comments for each post
            $comments_sql = "SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC";
            $comments_stmt = mysqli_prepare($conn, $comments_sql);
            mysqli_stmt_bind_param($comments_stmt, "i", $post['id']);
            mysqli_stmt_execute($comments_stmt);
            $comments_result = mysqli_stmt_get_result($comments_stmt);

            if (mysqli_num_rows($comments_result) > 0) {
                echo '<table class="table table-bordered mt-3">
                        <thead class="thead-dark">
                            <tr>
                                <th>Author</th>
                                <th>Comment</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                while ($comment = mysqli_fetch_assoc($comments_result)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($comment['author_name']) . '</td>';
                    echo '<td>' . nl2br(htmlspecialchars($comment['comment'])) . '</td>';
                    echo '<td>' . $comment['created_at'] . '</td>';
                    echo '<td>
                            <a href="comments.php?delete_comment_id=' . $comment['id'] . '" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm(\'Are you sure you want to delete this comment?\');">Delete</a>
                          </td>';
                    echo '</tr>';
                }
                echo '</tbody>
                    </table>';
            } else {
                echo '<p>No comments found for this post.</p>';
            }

            echo '</div>';
        }
        ?>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
