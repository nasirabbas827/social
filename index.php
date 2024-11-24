<?php
include('config.php');

session_start();

// Initialize the search query
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    // Sanitize the user input to prevent SQL injection
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    
    // Build the search query to match any word in the title or category name
    $search_query = "AND (posts.title LIKE '%$search_term%' OR categories.name LIKE '%$search_term%')";
}

// Fetch published posts with category and author details
$sql = "SELECT posts.id, posts.title, posts.created_at, posts.content, posts.featured_image, posts.author_id, posts.category_id, categories.name AS category_name, users.username AS author_name 
        FROM BlogPosts AS posts
        JOIN categories ON posts.category_id = categories.id
        JOIN users ON posts.author_id = users.id
        WHERE posts.status = 'published' $search_query
        ORDER BY posts.created_at DESC";
$result = mysqli_query($conn, $sql);

// Fetch recent featured images for the carousel
$carousel_sql = "SELECT id, title, featured_image FROM BlogPosts WHERE featured_image != '' AND status = 'published' ORDER BY created_at DESC LIMIT 5";
$carousel_result = mysqli_query($conn, $carousel_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Personal Blog Website</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .jumbotron {
            height: 500px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./images/hotel.jpg');
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .jumbotron h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .jumbotron p {
            font-size: 1.5rem;
        }
        .carousel-item img {
            height: 300px;
            object-fit: cover;
        }

        .carousel-item {
            text-align: center;
        }

        .carousel-caption {
            bottom: 30px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        img {
            height: 200px;
        }
    </style>
</head>
<body>

<?php
include('navbar.php');
?>

<div class="jumbotron text-center">
    <h1>Welcome to Personal Blog Website</h1>
    <p>Explore fascinating stories, tips, and insights across various topics.</p>
    <a href="login.php" class="btn btn-primary btn-lg">Login to Explore More</a>
</div>

<div class="container mt-5">

    <!-- Carousel for recent posts -->
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php 
            $active = "active"; 
            $index = 0; 
            while ($carousel_row = mysqli_fetch_assoc($carousel_result)) { 
                ?>
                <li data-target="#carouselExampleIndicators" data-slide-to="<?php echo $index; ?>" class="<?php echo $active; ?>"></li>
                <?php 
                $active = "";  
                $index++; 
            } ?>
        </ol>
        <h2>Featured Posts</h2>

        <div class="carousel-inner">
            <?php
            // Reset result pointer to the beginning to display carousel items
            mysqli_data_seek($carousel_result, 0);
            $active = "active"; // First item should be active
            while ($carousel_row = mysqli_fetch_assoc($carousel_result)) {
            ?>
                <div class="carousel-item <?php echo $active; ?>">
                    <?php if ($carousel_row['featured_image']) { ?>
                        <img src="images/<?php echo $carousel_row['featured_image']; ?>" class="d-block w-100" alt="Featured Image">
                    <?php } else { ?>
                        <img src="https://via.placeholder.com/800x300" class="d-block w-100" alt="Featured Image">
                    <?php } ?>
                    <div class="carousel-caption">
                        <h5><?php echo htmlspecialchars($carousel_row['title']); ?></h5>
                    </div>
                </div>
            <?php 
                $active = ""; // Remove "active" class from all but the first item
            } ?>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <!-- Search Bar Section -->
    <h2 class="text-center mt-5">Search From Posts</h2>

    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by title or category" value="<?php echo isset($search_term) ? htmlspecialchars($search_term) : ''; ?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </div>
    </form>

    <!-- Display Posts -->
    <h2 class="text-center">Recent Posts</h2>
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0) { 
            while ($row = mysqli_fetch_assoc($result)) {
                // Truncate content for preview
                $content_preview = strip_tags($row['content']);
                $content_preview = strlen($content_preview) > 150 ? substr($content_preview, 0, 150) . '...' : $content_preview;
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($row['featured_image']) { ?>
                            <img src="images/<?php echo $row['featured_image']; ?>" class="card-img-top" alt="Featured Image">
                        <?php } else { ?>
                            <img src="https://via.placeholder.com/300x200" class="card-img-top" alt="Featured Image">
                        <?php } ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <p class="card-text"><?php echo $content_preview; ?></p>
                            <p class="text-muted">By <strong><?php echo htmlspecialchars($row['author_name']); ?></strong> on <?php echo date('F j, Y', strtotime($row['created_at'])); ?> | Category: <?php echo htmlspecialchars($row['category_name']); ?></p>
                            <a href="view_post.php?id=<?php echo $row['id']; ?>" class="btn btn-primary mt-auto">Read More</a>
                        </div>
                    </div>
                </div>
            <?php } 
        } else { ?>
            <div class="col-12 text-center">
                <p>No posts found matching your search criteria.</p>
            </div>
        <?php } ?>
    </div>
</div>

<?php
include 'footer.php';
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
