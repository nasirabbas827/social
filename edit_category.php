<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$message = ""; // Variable to hold messages

// Check if the category ID is set in the URL
if (isset($_GET['id'])) {
    $categoryId = $_GET['id'];

    // Fetch the category details from the database
    $sql = "SELECT id, name FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $name);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // If the category doesn't exist, redirect
    if (empty($id)) {
        header("location: categories.php");
        exit;
    }

    // Update category name
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newName = $_POST["category_name"];

        if (!empty($newName)) {
            $sql = "UPDATE categories SET name = ?, updated_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $newName, $categoryId);

            if (mysqli_stmt_execute($stmt)) {
                $message = '<div class="alert alert-success text-center">Category updated successfully!</div>';
                header("Location: manage_categories.php");
exit;
            } else {
                $message = '<div class="alert alert-danger text-center">Error updating category: ' . mysqli_error($conn) . '</div>';
            }

            mysqli_stmt_close($stmt);
        } else {
            $message = '<div class="alert alert-danger text-center">Category name cannot be empty.</div>';
        }
    }
} else {
    header("location: categories.php");
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2 class="text-center">Edit Category</h2>

    <?php echo $message; // Display message ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $categoryId; ?>">
        <div class="form-group">
            <label for="category_name">Category Name:</label>
            <input type="text" class="form-control" name="category_name" value="<?php echo $name; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Category</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
