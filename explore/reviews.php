<?php
//BASE_PATH won't work because it's in the config file that we're trying to import.
include $_SERVER['DOCUMENT_ROOT'] . '/phpProjects/Narrative/config/config.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../public/css/articleLayouts/layoutOne_7_12_Item.css">
    <title>Live & Learn | Narrative</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .tabs {
            display: flex;
            justify-content: center;
            gap: 30px; /* Adjust spacing between tabs */
            margin: 2em;
        }

        .tab {
            font-size: 16px;
            font-weight: 500;
            padding: 10px 0;
            text-transform: uppercase;
            cursor: pointer;
            transition: color 0.3s ease, border-bottom 0.3s ease;
            color: #555; /* Neutral text color */
        }

        .tab:hover {
            color: #000; /* Slightly darker text on hover */
        }

        .tab.active {
            color: #000; /* Dark color for active tab */
            border-bottom: 2px solid #000; /* Border bottom to indicate active tab */
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>

<main class="main-container">
    <div class="tabs">
        <div class="tab active" data-tab="play-reviews">Play Reviews</div>
        <div class="tab" data-tab="book-reviews">Book Reviews</div>
    </div>

    <div class="tab-content active main-content" id="play-reviews">

        <div id="playReviewsCarousel" class="carousel slide" data-bs-ride="carousel">
            <!-- Indicators -->
            <div class="carousel-indicators">
                <?php
                $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Category, Tags, Image, user_id 
                FROM tbl_blogs 
                WHERE Category = 'Reviews' 
                ORDER BY datePublished DESC";

                $result = $conn->query($sql);
                $indicatorIndex = 0;
                while ($result->fetch_assoc()) {
                    echo '<button type="button" data-bs-target="#playReviewsCarousel" data-bs-slide-to="' . $indicatorIndex . '"' . ($indicatorIndex === 0 ? ' class="active"' : '') . ' aria-current="true" aria-label="Slide ' . ($indicatorIndex + 1) . '"></button>';
                    $indicatorIndex++;
                }
                ?>
            </div>

            <!-- Carousel Items -->
            <div class="carousel-inner">
                <?php
                $result->data_seek(0); // Reset the result pointer for fetching data again
                $isActive = true;
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="carousel-item <?php echo $isActive ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
                            <img src="<?php echo BASE_URL . 'public/images/users/' . htmlspecialchars($row['user_id']) . '/' . htmlspecialchars($row['Image']); ?>"
                                 class="d-block w-100" alt="Play Image">
                        </a>
                        <div class="carousel-caption d-none d-md-block" style="background: rgba(0, 0, 0, 0.5); padding: 15px; border-radius: 10px; margin-top: 10px;">
                            <h5 class='carousel-heading-title' style="font-weight: 600; font-size: 30px; color: white; margin: 0;">
                                <a href="<?php echo BASE_URL ?>users/article.php?id=<?php echo $row['id']; ?>"
                                   style="text-decoration: none; color: white;"><?php echo htmlspecialchars($row['title']); ?></a>
                            </h5>
                            <p style="color: white; margin: 5px 0;"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                            <p style="margin: 0; color: white;"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small></p>
                        </div>

                    </div>

                    <?php
                    $isActive = false;
                }
                ?>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#playReviewsCarousel"
                    data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#playReviewsCarousel"
                    data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>


        <h4 class="main-content-title">The Latest in Shakespeare Plays</h4>

        <div class="grid-container">
            <?php
            // Query to get latest blogs excluding featured blogs
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
            FROM tbl_blogs 
            WHERE (Tags = 'Shakespeare Plays') 
              AND featured = 0 
              AND Private = 0 
            ORDER BY datePublished DESC";
            $result = $conn->query($sql);

            $i = 1; // Counter to track grid items
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="grid-item p<?php echo $i; ?>">
                    <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>">
                        <div class="image-container">
                            <img src="<?php echo BASE_URL . 'public/images/users/' . htmlspecialchars($row['user_id']) . '/' . htmlspecialchars($row['Image']); ?>"
                                 alt="Article Image">
                        </div>
                        <div class="blog-details">
                            <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?>

                            </h2>
                            <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                    <div class="blog-details-2">
                        <p id="blog-tags">
                            <a href="<?php echo BASE_URL; ?>explore/lifestyle.php">
                                <?php echo htmlspecialchars($row['Tags']); ?>
                            </a>
                        </p>
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                        </p>
                    </div>
                </div>
                <?php
                $i++; // Increment the counter for the next blog
            }
            ?>
        </div>


    </div>

    <div class="tab-content main-content" id="book-reviews">
        <h1 class="main-content-title">Book Reviews</h1>
        <div class="grid-container">
            <?php
            $sql = "SELECT id, title, LEFT(content, 270) AS summary, datePublished, Tags, Image, user_id 
                    FROM tbl_blogs 
                    WHERE Tags = 'Book Reviews' 
                      AND featured = 0 
                      AND Private = 0 
                    ORDER BY datePublished DESC 
                    LIMIT 12";
            $result = $conn->query($sql);

            $i = 1;
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="grid-item p<?php echo $i; ?>">
                    <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
                        <div class="image-container">
                            <img src="<?php echo BASE_URL . 'public/images/users/' . htmlspecialchars($row['user_id']) . '/' . htmlspecialchars($row['Image']); ?>"
                                 alt="Article Image">
                        </div>
                        <div class="blog-details">
                            <h2 id="blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                            <p id="blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                    <div class="blog-details-2">
                        <p id="blog-tags">
                            <a href="<?php echo BASE_URL; ?>explore/lifestyle.php">
                                <?php echo htmlspecialchars($row['Tags']); ?>
                            </a>
                        </p>
                        <p id="blog-date"><small><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></small>
                        </p>
                    </div>
                </div>
                <?php
                $i++;
            }
            ?>
        </div>
    </div>
</main>

<script>
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
</script>
</body>
</html>