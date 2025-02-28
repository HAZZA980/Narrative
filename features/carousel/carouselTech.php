<style>
    .carousel-container {
        position: relative;
        width: 75%;
        overflow: hidden;
    }

    .carousel-grid {
        display: flex;
        gap: 15px;
        overflow-x: auto;
        scroll-behavior: smooth;
        scrollbar-width: none; /* Hide scrollbar for Firefox */
    }

    .carousel-grid::-webkit-scrollbar {
        display: none; /* Hide scrollbar for Chrome/Safari */
    }

    .carousel-image-container {
        height: 100px; /* Set a fixed height */
        width: 100%; /* Ensure it takes full width */
        overflow: hidden; /* Hide overflow */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .carousel-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Ensures images fill container without stretching */
        border-radius: 8px 8px; /* Keeps border-radius */
    }


    .carousel-grid-item {
        flex: 0 0 auto;
        border-radius: 10px;
        background-color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
        height: 15rem;
        width: 12.3rem;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: stretch; /* Ensure child elements stretch */
        overflow: hidden;
    }

    /* Blog details below the image */
    .carousel-blog-details {
        flex-grow: 1; /* Allows content to expand */
        padding: 10px;
        background: white;
        text-align: center;
    }

    .carousel-blog-title {
        font-size: 11px;
        font-weight: bold;
        color: #333;
        max-font-size: var(12px);
    }

    .carousel-blog-content {
        font-size: 14px;
        color: #777;
    }


    .carousel-button-left {
        left: 0.3rem;
    }

    .carousel-button-right {
        right: 1rem;
        color: white;
        z-index: 1000;
    }

    .carousel-button:hover {
        background: rgba(0, 0, 0, 0.8);
    }

</style>
<div class="carousel-container">
    <!-- Left Scroll Button -->
    <button class="carousel-button carousel-button-left" onclick="scrollCarousel(-1)">&#10094;</button>

    <div class="carousel-grid">
        <?php
        // Fetch articles for the carousel
        $sql = "SELECT id, title, category, datePublished, Tags, Image, user_id 
                FROM tbl_blogs 
                WHERE Tags LIKE '%Big Tech%' or Tags like '%Security%' 
                AND featured = 0 
                AND Private = 0 
                ORDER BY datePublished DESC";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="carousel-grid-item">
                <a href="<?php echo BASE_URL ?>user/article.php?id=<?php echo $row['id']; ?>">
                    <div class="carousel-image-container">
                        <img src="<?php echo isset($row['Image']) && !empty($row['Image']) && $row['Image'] !== 'narrative-logo-big.png'
                            ? BASE_URL . 'public/images/users/' . $row['user_id'] . '/' . $row['Image']
                            : BASE_URL . 'narrative-logo-big.png'; ?>" alt="Blog Image">
                    </div>
                    <div class="carousel-blog-details">
                        <h2 class="carousel-blog-title"><?php echo htmlspecialchars($row['title']); ?></h2>
                        <p class="carousel-blog-content"><?php echo date('F j, Y', strtotime($row['datePublished'])); ?></p>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>

    <!-- Right Scroll Button -->
    <button class="carousel-button carousel-button-right" onclick="scrollCarousel(1)">&#10095;</button>
</div>
