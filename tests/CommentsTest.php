<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../features/comments/comments_functions.php';

class CommentsTest extends TestCase
{
    private $conn;
    private $userId;

    protected function setUp(): void
    {
        $this->conn = new mysqli("localhost", "root", "", "narrative_testdb");

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        $this->userId = 888;

        // Create test user
        $this->conn->query("INSERT IGNORE INTO users (user_id, email, password) VALUES ($this->userId, 'commenttester@example.com', '" . password_hash('test123', PASSWORD_DEFAULT) . "')");

        // Create a test article
        $this->conn->query("INSERT IGNORE INTO tbl_blogs (Id, title, content, user_id) VALUES (2001, 'Test Article for Comment', 'Some content.', $this->userId)");
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM article_comments WHERE article_id = 2001");
        $this->conn->query("DELETE FROM tbl_blogs WHERE Id = 2001");
        $this->conn->query("DELETE FROM users WHERE user_id = $this->userId");
        $this->conn->close();
    }

    public function testAddValidComment()
    {
        $response = addComment($this->conn, 2001, $this->userId, "This is a test comment.");
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Comment added successfully.', $response['message']);
    }

    public function testAddEmptyComment()
    {
        $response = addComment($this->conn, 2001, $this->userId, "");
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid article or comment content.', $response['message']);
    }

    public function testAddCommentToInvalidArticle()
    {
        $response = addComment($this->conn, 0, $this->userId, "Valid comment but invalid article.");
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid article or comment content.', $response['message']);
    }
}
