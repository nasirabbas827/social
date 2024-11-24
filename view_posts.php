<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Fetch all posts created by the logged-in user with category name
$author_id = $_SESSION["id"];
$sql = "SELECT BlogPosts.id, BlogPosts.title, BlogPosts.status, BlogPosts.category_id, categories.name AS category_name 
        FROM BlogPosts 
        JOIN categories ON BlogPosts.category_id = categories.id
        WHERE author_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $author_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    // Handle delete action
    $delete_id = $_POST["delete_id"];
    $delete_sql = "DELETE FROM BlogPosts WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        echo '<script>alert("Post deleted successfully!"); window.location.href = "view_posts.php";</script>';
    } else {
        echo '<script>alert("Error deleting post!");</script>';
    }
    mysqli_stmt_close($delete_stmt);
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Blog Posts</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Your Blog Posts</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['category_name']; ?></td> <!-- Show the category name here -->
                <td><?php echo $row['status']; ?></td>
                <td>
                    <a href="edit_post.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
