<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$message = ""; 
// Add new category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $categoryName = $_POST["category_name"];
    if (!empty($categoryName)) {
        $sql = "INSERT INTO categories (name, created_at, updated_at) VALUES (?, NOW(), NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $categoryName);

        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success text-center">Category added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger text-center">Error adding category: ' . mysqli_error($conn) . '</div>';
        }

        mysqli_stmt_close($stmt);
    } else {
        $message = '<div class="alert alert-danger text-center">Please provide a category name.</div>';
    }
}

// Delete category
if (isset($_GET['delete_id'])) {
    $categoryId = $_GET['delete_id'];
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);

    if (mysqli_stmt_execute($stmt)) {
        $message = '<div class="alert alert-success text-center">Category deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger text-center">Error deleting category: ' . mysqli_error($conn) . '</div>';
    }

    mysqli_stmt_close($stmt);
}

// Fetch all categories
$sql = "SELECT id, name, created_at, updated_at FROM categories ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="text-center">
        <h2>Manage Categories</h2>
        <?php echo $message; // Display message ?>
    </div>

    <!-- Add Category Form -->
    <div class="profile-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="category_name">Category Name:</label>
                <input type="text" class="form-control" name="category_name" required>
            </div>
            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="mt-5">
        <h4>Categories</h4>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Category Name</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['name'] . '</td>';
                        echo '<td>' . $row['created_at'] . '</td>';
                        echo '<td>' . $row['updated_at'] . '</td>';
                        echo '<td>
                                <a href="edit_category.php?id=' . $row['id'] . '" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete_id=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this category?\')">Delete</a>
                              </td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5" class="text-center">No categories found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>
