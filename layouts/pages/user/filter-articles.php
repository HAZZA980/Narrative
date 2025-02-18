<?php
include $_SERVER['DOCUMENT_ROOT'].'phpProjects/Narrative/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Get the selected tags from the request
    $tags = $data['tags'] ?? [];

    // Build the query
    if (!empty($tags)) {
        // Sanitize the tags and build the query
        $tagsPlaceholders = implode(',', array_fill(0, count($tags), '?'));
        $query = "SELECT * FROM tbl_blogs WHERE Tags IN ($tagsPlaceholders)";
        $stmt = $conn->prepare($query);

        // Bind the selected tags dynamically
        $stmt->bind_param(str_repeat('s', count($tags)), ...$tags);

        $stmt->execute();
        $result = $stmt->get_result();

        // Prepare the HTML for the filtered articles
        $articlesHTML = '';
        while ($row = $result->fetch_assoc()) {
            $articlesHTML .= '
            <div class="flex-item">
                <div class="article-author-and-topic">
                    <span class="aa" id="author-name">' . htmlspecialchars($row['Author']) . '</span>
                    <span class="aa" id="writing-about"> writing about </span>
                    <span class="aa" id="blog-tags">' . htmlspecialchars($row['Tags']) . '</span>
                </div>
                <a href="../layouts/pages/articles/article.php?id=' . $row['id'] . '" class="article-main-link">
                    <div class="blog-body">
                        <div class="blog-details">
                            <h2 id="blog-title">' . htmlspecialchars($row['title']) . '</h2>
                            <p id="blog-content">' . htmlspecialchars($row['summary']) . '...</p>
                        </div>
                    </div>
                </a>
            </div>';
        }

        echo json_encode(['success' => true, 'articlesHTML' => $articlesHTML]);
    } else {
        // If no tags are selected, return all articles
        $query = "SELECT * FROM tbl_blogs";
        $result = $conn->query($query);

        $articlesHTML = '';
        while ($row = $result->fetch_assoc()) {
            $articlesHTML .= '
            <div class="flex-item">
                <div class="article-author-and-topic">
                    <span class="aa" id="author-name">' . htmlspecialchars($row['Author']) . '</span>
                    <span class="aa" id="writing-about"> writing about </span>
                    <span class="aa" id="blog-tags">' . htmlspecialchars($row['Tags']) . '</span>
                </div>
                <a href="../layouts/pages/articles/article.php?id=' . $row['id'] . '" class="article-main-link">
                    <div class="blog-body">
                        <div class="blog-details">
                            <h2 id="blog-title">' . htmlspecialchars($row['title']) . '</h2>
                            <p id="blog-content">' . htmlspecialchars($row['summary']) . '...</p>
                        </div>
                    </div>
                </a>
            </div>';
        }

        echo json_encode(['success' => true, 'articlesHTML' => $articlesHTML]);
    }
}
