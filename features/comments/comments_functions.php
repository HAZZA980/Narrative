<?php

function addComment(mysqli $conn, int $article_id, int $user_id, string $comment): array
{
    if (empty($article_id) || empty(trim($comment))) {
        return ['status' => 'error', 'message' => 'Invalid article or comment content.'];
    }

    $comment = $conn->real_escape_string($comment);

    $query = "INSERT INTO article_comments (article_id, user_id, comment, commented_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("iis", $article_id, $user_id, $comment);

    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Comment added successfully.'];
    } else {
        return ['status' => 'error', 'message' => 'Failed to add comment: ' . $stmt->error];
    }
}
