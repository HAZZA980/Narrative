<?php

class BookmarkService {
    private $conn;
    private $userId;

    public function __construct(mysqli $conn, int $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function bookmark(int $articleId, string $action): array {
        if (!$articleId || !$action) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }

        if ($action === 'add') {
            $check = $this->conn->prepare("SELECT 1 FROM user_bookmarks WHERE article_id = ? AND user_id = ?");
            $check->bind_param("ii", $articleId, $this->userId);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows === 0) {
                $insert = $this->conn->prepare("INSERT INTO user_bookmarks (article_id, user_id) VALUES (?, ?)");
                $insert->bind_param("ii", $articleId, $this->userId);
                $insert->execute();
            }
        } elseif ($action === 'remove') {
            $delete = $this->conn->prepare("DELETE FROM user_bookmarks WHERE article_id = ? AND user_id = ?");
            $delete->bind_param("ii", $articleId, $this->userId);
            $delete->execute();
        }

        // Check final state
        $check = $this->conn->prepare("SELECT 1 FROM user_bookmarks WHERE article_id = ? AND user_id = ?");
        $check->bind_param("ii", $articleId, $this->userId);
        $check->execute();
        $result = $check->get_result();

        return ['success' => true, 'bookmarked' => $result->num_rows > 0];
    }
}
