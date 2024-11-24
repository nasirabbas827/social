<?php
include('config.php');

// define variables and initialize with empty values
$username = $password = $email = $phone  = $success_msg = "";
$username_err = $password_err = $email_err = $phone_err  = "";

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // check if username already exists in database
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        $param_username = trim($_POST["username"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $username_err = "This username is already taken.";
        } else {
            $username = trim($_POST["username"]);
        }
    }

    // validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email address.";
    } else {
        $email = trim($_POST["email"]);
        // check if email already exists in database
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $email_err = "This email address is already taken.";
        }
    }

    // validate phone number
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter a phone number.";
    } else {
        $phone = trim($_POST["phone"]);
        // check if phone number already exists in database
        $sql = "SELECT id FROM users WHERE phone = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_phone);
        $param_phone = $phone;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
            $phone_err = "This phone number is already taken.";
        }
    }


    // if no errors, insert user into database
    if (empty($username_err) && empty($password_err) && empty($email_err) && empty($phone_err) ) {
        $sql = "INSERT INTO users (username, password, email, phone) VALUES ( ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $param_username, $param_password, $param_email, $param_phone);
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_email = $email;
        $param_phone = $phone;
        mysqli_stmt_execute($stmt);
        $success_msg = "User registered successfully.";
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success text-center" role="alert">
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <h2 class="text-center">User Registration</h2>
    <p class="text-center">Please fill in your details to register.</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="col-md-6 form-group">
                <label>Phone Number</label>
                <input type="number" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                <span class="invalid-feedback"><?php echo $phone_err; ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="col-md-6 form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
        </div>

        <div class="form-group text-center">
            <input type="submit" class="btn btn-primary" value="Register">
        </div>
    </form>

    <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
</div>
<?php
include 'footer.php';
?>
<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
