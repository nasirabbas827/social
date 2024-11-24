<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$message = ""; // Variable to hold messages

// Fetch the post to edit
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
    $sql = "SELECT * FROM BlogPosts WHERE id = ? AND author_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $title = $row['title'];
        $content = $row['content'];
        $category_id = $row['category_id'];
        $status = $row['status'];
        $featured_image = $row['featured_image']; // Get current image
    } else {
        echo "Post not found!";
        exit;
    }
    mysqli_stmt_close($stmt);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $category_id = $_POST["category_id"];
    $status = $_POST["status"];
    $featured_image = $row['featured_image']; // Default to the current image

    // Handle file upload
    if (!empty($_FILES['featured_image']['name'])) {
        $image_tmp = $_FILES["featured_image"]["tmp_name"];
        $image_name = time() . '_' . $_FILES["featured_image"]["name"];
        $image_path = "./images/" . $image_name;

        if (move_uploaded_file($image_tmp, $image_path)) {
            $featured_image = $image_name; // Update to new image name

            // Optionally delete old image file
            if (!empty($row['featured_image']) && file_exists("./images/" . $row['featured_image'])) {
                unlink("./images/" . $row['featured_image']);
            }
        } else {
            $message = '<div class="alert alert-danger text-center">Error uploading image.</div>';
        }
    }

    // Update the post in the database
    $update_query = "UPDATE BlogPosts SET title = ?, content = ?, category_id = ?, status = ?, featured_image = ?, updated_at = NOW() WHERE id = ? AND author_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssssssi", $title, $content, $category_id, $status, $featured_image, $post_id, $_SESSION["id"]);

    if (mysqli_stmt_execute($update_stmt)) {
        $message = '<div class="alert alert-success text-center">Post updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger text-center">Error updating post: ' . mysqli_error($conn) . '</div>';
    }

    mysqli_stmt_close($update_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="./css/style.css">

    <!-- Quill Editor CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2>Edit Blog Post</h2>
    <?php echo $message; // Display message ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <?php 
                $category_query = "SELECT * FROM categories";
                $category_result = mysqli_query($conn, $category_query);
                while ($category = mysqli_fetch_assoc($category_result)) {
                    $selected = ($category['id'] == $category_id) ? 'selected' : '';
                    echo "<option value='{$category['id']}' {$selected}>{$category['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Content:</label>
            <div id="editor" style="height: 300px;"><?php echo htmlspecialchars($content); ?></div>
            <input type="hidden" id="content" name="content">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="published" <?php echo ($status == 'published') ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo ($status == 'draft') ? 'selected' : ''; ?>>Draft</option>
            </select>
        </div>

        <div class="form-group">
            <label for="featured_image">Featured Image</label>
            <input type="file" class="form-control-file" id="featured_image" name="featured_image" accept="image/*">
            <?php if (!empty($featured_image)): ?>
                <p>Current Image:</p>
                <img src="./images/<?php echo htmlspecialchars($featured_image); ?>" alt="Featured Image" style="max-width: 200px;">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Update Post</button>
        <a class="btn btn-dark" href="view_posts.php">Back to Posts</a>
    </form>
</div>

<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                ['link', 'image'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }]
            ]
        }
    });
    var content = '<?php echo addslashes($content); ?>';
    quill.root.innerHTML = content;
    var form = document.querySelector('form');
    form.onsubmit = function() {
        var content = document.querySelector('input[name=content]');
        content.value = quill.root.innerHTML;
    };
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
