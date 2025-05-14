<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../features/likes/likes_functions.php';

class LikesTest extends TestCase
{
    private $conn;
    private $userId = 999;
    private $articleId = 1001;

    protected function setUp(): void
    {
        $this->conn = new mysqli("localhost", "root", "", "db_narrative");
        $this->conn->query("INSERT IGNORE INTO users (user_id, email, password) VALUES ($this->userId, 'test@example.com', 'pass')");
        $this->conn->query("INSERT IGNORE INTO tbl_blogs (Id, title, content, user_id) VALUES ($this->articleId, 'Test Article', 'Test Content', $this->userId)");
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM article_likes WHERE user_id = $this->userId");
        $this->conn->query("DELETE FROM tbl_blogs WHERE Id = $this->articleId");
        $this->conn->query("DELETE FROM users WHERE user_id = $this->userId");
        $this->conn->close();
    }

    public function testAddLike()
    {
        $response = addLike($this->conn, $this->articleId, $this->userId);
        $this->assertTrue($response['success']);
        $this->assertGreaterThan(0, $response['likes']);
    }

    public function testRemoveLike()
    {
        addLike($this->conn, $this->articleId, $this->userId); // Add first
        $response = removeLike($this->conn, $this->articleId, $this->userId);
        $this->assertTrue($response['success']);
        $this->assertEquals(0, $response['likes']);
    }
}
