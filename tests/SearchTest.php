<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../features/search/search_functions.php';

class SearchTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli("localhost", "root", "", "narrative_testdb");
        $this->conn->query("DELETE FROM tbl_blogs WHERE title = 'Test Article'");
        $this->conn->query("INSERT INTO Users (user_id, username, email, password) VALUES (9999, 'testuser', 'test@ex.com', 'pass') ON DUPLICATE KEY UPDATE username = 'testuser'");
        $this->conn->query("INSERT INTO tbl_blogs (id, title, user_id, content, datePublished, Tags, private, Category, Type) VALUES 
    (9999, 'Test Article', 9999, 'This is a test article.', '2024-01-01', 'test', 0, 'Tech', 'general')");

    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM tbl_blogs WHERE id = 9999");
        $this->conn->query("DELETE FROM Users WHERE user_id = 9999");
        $this->conn->close();
    }

    public function testSearchReturnsResults()
    {
        $params = ['txt-search' => 'test'];
        $result = searchArticles($this->conn, $params);

        $this->assertGreaterThan(0, count($result['articles']));
        $this->assertEquals('Gaming Monitor Specs Explained', $result['articles'][0]['title']);
    }

    public function testPaginationLimitsResults()
    {
        $params = ['txt-search' => 'test', 'page' => 1];
        $result = searchArticles($this->conn, $params);

        $this->assertLessThanOrEqual(15, count($result['articles']));
    }

    public function testCategoryFilter()
    {
        $params = ['txt-search' => 'test', 'categories' => 'Tech'];
        $result = searchArticles($this->conn, $params);

        $this->assertNotEmpty($result['articles']);
        $this->assertEquals('Tech', $result['articles'][0]['Category']);
    }

    public function testDateRangeFilter()
    {
        $params = [
            'txt-search' => 'test',
            'startDate' => '2023-01-01',
            'endDate' => '2024-12-31'
        ];
        $result = searchArticles($this->conn, $params);

        $this->assertNotEmpty($result['articles']);
    }
}
