<?php

function addLike(mysqli $conn, int $article_id, int $user_id): array {
    $check_query = "SELECT * FROM article_likes WHERE article_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $article_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $query = "INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
    }

    return getLikeCount($conn, $article_id);
}

function removeLike(mysqli $conn, int $article_id, int $user_id): array {
    $query = "DELETE FROM article_likes WHERE article_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $article_id, $user_id);
    $stmt->execute();

    return getLikeCount($conn, $article_id);
}

function getLikeCount(mysqli $conn, int $article_id): array {
    $query = "SELECT COUNT(*) AS like_count FROM article_likes WHERE article_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $like_count = $result->fetch_assoc()['like_count'];

    return ["success" => true, "likes" => $like_count];
}
