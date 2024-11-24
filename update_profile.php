<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$message = ""; // Variable to hold messages

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch user details from the database
$sql = "SELECT username, email, bio, profile_picture FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $username, $email, $bio, $profile_picture);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Update user profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST["username"];
    $newEmail = $_POST["email"];
    $newBio = $_POST["bio"];
    $newProfilePicture = $profile_picture; // Default to the current profile picture

    // Handle file upload for the profile picture
    if (!empty($_FILES["profile_picture"]["name"])) {
        $target_dir = "./uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $newProfilePicture = basename($_FILES["profile_picture"]["name"]); // Save only the filename
            } else {
                $message = '<div class="alert alert-danger text-center">Error uploading profile picture.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger text-center">Invalid file type for profile picture.</div>';
        }
    }

    // Update user details in the database
    $update_query = "UPDATE users SET username = ?, email = ?, bio = ?, profile_picture = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssssi", $newUsername, $newEmail, $newBio, $newProfilePicture, $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $message = '<div class="alert alert-success text-center">Profile updated successfully!</div>';
        // Update session data if needed
        $_SESSION["username"] = $newUsername;
    } else {
        $message = '<div class="alert alert-danger text-center">Error updating profile: ' . mysqli_error($conn) . '</div>';
    }

    mysqli_stmt_close($update_stmt);
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .profile-picture-preview {
            display: block;
            margin: auto;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .btn-primary {
            width: 100%;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="profile-container">
        <h2 class="text-center">Update Profile</h2>

        <!-- Display Message -->
        <?php echo $message; ?>

        <!-- Profile Picture Preview -->
        <img src="./uploads/<?php echo $profile_picture ? $profile_picture : 'default.png'; ?>" alt="Profile Picture" class="profile-picture-preview">

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" value="<?php echo $username; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea class="form-control" name="bio" rows="3"><?php echo $bio; ?></textarea>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" class="form-control" name="profile_picture">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
