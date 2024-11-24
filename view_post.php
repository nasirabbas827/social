<?php
include('config.php');
session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch the username from the users table
$user_sql = "SELECT username FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_sql);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
    $_SESSION["username"] = $user['username']; // Store in session
} else {
    echo "Error: User not found.";
    exit;
}

$post_id = $_GET['id'];

// Fetch post details and like count
$post_sql = "SELECT posts.id, posts.title, posts.content, posts.created_at, posts.featured_image, posts.author_id, 
             categories.name AS category_name, users.username AS author_name, users.bio AS author_bio, 
             users.profile_picture AS author_profile_pic, 
             (SELECT COUNT(*) FROM Likes WHERE PostID = posts.id) AS like_count
             FROM BlogPosts AS posts
             JOIN categories ON posts.category_id = categories.id
             JOIN users ON posts.author_id = users.id
             WHERE posts.id = $post_id AND posts.status = 'published'";
$post_result = mysqli_query($conn, $post_sql);
$post = mysqli_fetch_assoc($post_result);

$like_count = $post['like_count'];

// Fetch comments
$comments_sql = "SELECT comment, author_name, created_at FROM comments WHERE post_id = $post_id ORDER BY created_at DESC";
$comments_result = mysqli_query($conn, $comments_sql);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle comment submission
    if (isset($_POST['comment']) && isset($_SESSION["id"])) {
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        $author_name = $_SESSION["username"];

        $insert_comment_sql = "INSERT INTO comments (post_id, comment, author_name, created_at) VALUES ('$post_id', '$comment', '$author_name', NOW())";
        if (mysqli_query($conn, $insert_comment_sql)) {
            header("Location: view_post.php?id=$post_id");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>Error adding comment. Please try again.</div>";
        }
    }
}

function clean_html($content)
{
    return strip_tags($content);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .author-profile {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .author-profile img {
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin-right: 15px;
        }
        .img-fluid {
            height: 200px;
        }
        .comment-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <?php echo $message; ?>

    <div class="author-profile card mb-4" style="background-color: #f8f9fa; border-radius: 10px; padding: 15px;">
        <div class="row no-gutters">
            <div class="col-md-3">
                <?php if ($post['author_profile_pic']) { ?>
                    <img src="uploads/<?php echo $post['author_profile_pic']; ?>" alt="Author Profile Picture" class="img-fluid rounded-circle">
                <?php } else { ?>
                    <img src="https://via.placeholder.com/80" alt="Author Profile Picture" class="img-fluid rounded-circle">
                <?php } ?>
            </div>
            <div class="col-md-9">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($post['author_name']); ?></h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($post['author_bio'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <p class="text-muted">By <strong><?php echo htmlspecialchars($post['author_name']); ?></strong> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></p>

    <?php if ($post['featured_image']) { ?>
        <img src="images/<?php echo $post['featured_image']; ?>" class="img-fluid mb-4" alt="Featured Image">
    <?php } else { ?>
        <img src="https://via.placeholder.com/800x400" class="img-fluid mb-4" alt="Featured Image">
    <?php } ?>

    <div class="card">
        <div class="card-body">
            <?php echo htmlspecialchars_decode($post['content']); ?>
        </div>
    </div>

    <div class="post-actions mt-4">
        <button id="like-btn" class="btn btn-outline-primary">
            <i class="fas fa-thumbs-up"></i> Like <span id="like-count"><?php echo $like_count; ?></span>
        </button>
        <button id="share-btn" class="btn btn-outline-success">
            <i class="fas fa-share"></i> Share
        </button>
    </div>

    <div class="comments-section mt-4">
        <h4>Comments</h4>
        <div class="card">
            <div class="card-body">
                <?php if (mysqli_num_rows($comments_result) > 0) { ?>
                    <?php while ($comment = mysqli_fetch_assoc($comments_result)) { ?>
                        <div class="comment-card">
                            <h5><?php echo htmlspecialchars($comment['author_name']); ?></h5>
                            <p class="text-muted"><?php echo date('F j, Y', strtotime($comment['created_at'])); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="leave-comment mt-4">
        <h5>Leave a Comment</h5>
        <?php if (isset($_SESSION["id"])) { ?>
            <form action="view_post.php?id=<?php echo $post['id']; ?>" method="POST">
                <div class="form-group">
                    <textarea name="comment" class="form-control" rows="4" placeholder="Your comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Comment</button>
            </form>
        <?php } else { ?>
            <p>You need to <a href="login.php">log in</a> to leave a comment.</p>
        <?php } ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script>
    document.getElementById('like-btn').addEventListener('click', function () {
        const postId = <?php echo $post_id; ?>;
        fetch('like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('like-count').textContent = data.like_count;
            } else {
                alert(data.message);
            }
        });
    });

    document.getElementById('share-btn').addEventListener('click', function () {
        const postId = <?php echo $post_id; ?>;
        fetch('share.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            }
        });
    });
</script>
</body>
</html>
