<div class="carousel-container">
    <button class="carousel-button left" onclick="scrollCarousel(-1)">&#10094;</button>
    <div class="carousel">
        <div class="carousel-grid">
            <?php
            $sql = "SELECT id, title, category, datePublished, Tags, Image, user_id 
                FROM tbl_blogs 
                WHERE Category = 'Travel' AND title LIKE '%Workaway%' 
                AND featured = 0 
                AND Private = 0 
                ORDER BY datePublished DESC";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="carousel-grid-item">
                    <a href="<?php echo BASE_URL?>user/article.php?id=<?php echo $row['id']; ?>">
                        <div class="image-container">
                            <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                                ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                                : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                        </div>
                        <div class="carousel-blog-details">
                            <h2 id="carousel-blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                            <p id="carousel-blog-content"><?php echo htmlspecialchars($row['summary']); ?>...</p>
                        </div>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <button class="carousel-button right" onclick="scrollCarousel(1)">&#10095;</button>
</div>