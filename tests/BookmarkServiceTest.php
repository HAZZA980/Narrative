<?php
use PHPUnit\Framework\TestCase;

class BookmarkServiceTest extends TestCase
{
    private $conn;
    private $userId;

    // Set up before each test
    protected function setUp(): void
    {
        // Database connection
        $this->conn = new mysqli("localhost", "root", "", "narrative_testdb");

        // Check for connection errors
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        // Insert a dummy user into the 'users' table for testing
        $this->userId = 999; // Example user ID, you should adjust as needed
        $this->conn->query("INSERT IGNORE INTO users (user_id, email, password) VALUES ($this->userId, 'testuser@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "')");

        // Insert dummy articles into tbl_blogs for testing (Article ID should match foreign key constraint)
        $this->conn->query("INSERT IGNORE INTO tbl_blogs (Id, title, content, user_id) VALUES (1001, 'Test Article 1', 'Content', $this->userId)");
        $this->conn->query("INSERT IGNORE INTO tbl_blogs (Id, title, content, user_id) VALUES (1002, 'Test Article 2', 'Content', $this->userId)");
    }

    // Clean up after each test
    protected function tearDown(): void
    {
        // Delete the inserted dummy articles
        $this->conn->query("DELETE FROM tbl_blogs WHERE Id IN (1001, 1002)");

        // Delete the dummy user
        $this->conn->query("DELETE FROM users WHERE user_id = $this->userId");

        // Close the database connection
        $this->conn->close();
    }

    // Test case for adding a bookmark
    public function testAddBookmark()
    {
        // Simulate adding a bookmark for article ID 1001
        $bookmarkService = new BookmarkService($this->conn);
        $response = $bookmarkService->addBookmark($this->userId, 1001);

        // Assert that the bookmark was successfully added
        $this->assertTrue($response['success']);
        $this->assertTrue($response['bookmarked']);
    }

    // Test case for removing a bookmark
    public function testRemoveBookmark()
    {
        // Simulate adding a bookmark first
        $bookmarkService = new BookmarkService($this->conn);
        $bookmarkService->addBookmark($this->userId, 1001);

        // Now simulate removing the bookmark
        $response = $bookmarkService->removeBookmark($this->userId, 1001);

        // Assert that the bookmark was successfully removed
        $this->assertTrue($response['success']);
        $this->assertFalse($response['bookmarked']);
    }

    // Test case for checking an already bookmarked article
    public function testAlreadyBookmarkedArticle()
    {
        // Add a bookmark for article 1001
        $bookmarkService = new BookmarkService($this->conn);
        $bookmarkService->addBookmark($this->userId, 1001);

        // Check if the article is bookmarked
        $response = $bookmarkService->isBookmarked($this->userId, 1001);

        // Assert that the article is still bookmarked
        $this->assertTrue($response['bookmarked']);
    }
}

class BookmarkService
{
    private $conn;

    // Constructor to initialize database connection
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Add a bookmark
    public function addBookmark($userId, $articleId)
    {
        $query = "SELECT * FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $articleId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insert bookmark if it doesn't exist
            $insertQuery = "INSERT INTO user_bookmarks (article_id, user_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($insertQuery);
            $stmt->bind_param("ii", $articleId, $userId);
            $stmt->execute();
        }

        // Check if the article is now bookmarked
        $checkQuery = "SELECT * FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("ii", $articleId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookmarked = $result->num_rows > 0;
        return [
            'success' => true,
            'bookmarked' => $bookmarked
        ];
    }

    // Remove a bookmark
    public function removeBookmark($userId, $articleId)
    {
        $query = "DELETE FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $articleId, $userId);
        $stmt->execute();

        // Check if the article is still bookmarked after removal
        $checkQuery = "SELECT * FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("ii", $articleId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookmarked = $result->num_rows > 0;
        return [
            'success' => true,
            'bookmarked' => $bookmarked
        ];
    }

    // Check if an article is bookmarked
    public function isBookmarked($userId, $articleId)
    {
        $query = "SELECT * FROM user_bookmarks WHERE article_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $articleId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $bookmarked = $result->num_rows > 0;
        return [
            'bookmarked' => $bookmarked
        ];
    }
}
