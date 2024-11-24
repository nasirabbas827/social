<?php
include('config.php');
session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$message = ""; // Variable to hold messages

// Fetch categories for the dropdown
$sql = "SELECT id, name FROM categories";
$result = mysqli_query($conn, $sql);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $category_id = $_POST["category_id"];
    $status = $_POST["status"];
    $author_id = $_SESSION["id"];
    $image_name = ''; // Initialize image name

    // Handle the featured image upload
    if (isset($_FILES["featured_image"]) && $_FILES["featured_image"]["error"] == 0) {
        $image_tmp = $_FILES["featured_image"]["tmp_name"];
        $image_name = time() . '_' . $_FILES["featured_image"]["name"];
        $image_path = "./images/" . $image_name;

        // Move the uploaded image to the images folder
        if (move_uploaded_file($image_tmp, $image_path)) {
            // Image uploaded successfully
            $message =  '<div class="alert alert-success text-center">Featured image uploaded successfully!</div>';
        } else {
            echo '<div class="alert alert-danger text-center">Error uploading the image.</div>';
        }
    }

    // Handle base64 images in the content and save them to posts_pictures folder
    preg_match_all('/<img src="data:image\/(.*?);base64,(.*?)"/', $content, $matches);
    if (!empty($matches[2])) {
        foreach ($matches[2] as $key => $base64Image) {
            $imageData = base64_decode($base64Image);
            $imageName = time() . '_' . $key . '.png'; // Generate unique image name
            file_put_contents('./posts_pictures/' . $imageName, $imageData);
            // Replace base64 image with the URL of the uploaded image
            $content = str_replace($matches[0][$key], '<img src="../posts_pictures/' . $imageName . '">', $content);
        }
    }

    // Insert the new post into the database
    $insert_query = "INSERT INTO BlogPosts (title, content, author_id, category_id, featured_image, status, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = mysqli_prepare($conn, $insert_query);

    // Correct bind_param with 6 variables (title, content, author_id, category_id, featured_image, status)
    mysqli_stmt_bind_param($stmt, "ssisss", $title, $content, $author_id, $category_id, $image_name, $status);

    if (mysqli_stmt_execute($stmt)) {
        $message =  '<div class="alert alert-success text-center">Post created successfully!</div>';
    } else {
        $message =  '<div class="alert alert-danger text-center">Error creating post: ' . mysqli_error($conn) . '</div>';
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog Post</title>

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
    <h2>Create a Blog Post</h2>
    <?php echo $message; // Display message ?>


    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Content:</label>
            <!-- Quill Editor -->
            <div id="editor" style="height: 300px;"></div>
            <input type="hidden" id="content" name="content" style="height: 300px;">
        </div>

        <div class="form-group">
            <label for="featured_image">Featured Image</label>
            <input type="file" class="form-control" id="featured_image" name="featured_image">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create Post</button>
        <a class="btn btn-dark" href="view_posts.php">View Posts</a>
    </form>
</div>

<!-- Quill Editor JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
    // Initialize Quill editor
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

    // Update the hidden input field with Quill content
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
