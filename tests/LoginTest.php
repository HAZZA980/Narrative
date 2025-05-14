<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../features/login/login_functions.php';

class LoginTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli("localhost", "root", "", "narrative_testdb");
        if ($this->conn->connect_error) {
            die("DB Connection failed: " . $this->conn->connect_error);
        }

        // Create a test user
        $password = password_hash("Test@123", PASSWORD_DEFAULT);
        $this->conn->query("INSERT IGNORE INTO users (user_id, username, email, password) VALUES (999, 'testuser', 'testuser@example.com', '$password')");
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE user_id = 999");
        $this->conn->close();
    }

    public function testSuccessfulLogin()
    {
        $result = loginUser($this->conn, "testuser@example.com", "Test@123");
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('testuser', $result['username']);
    }

    public function testLoginWithWrongPassword()
    {
        $result = loginUser($this->conn, "testuser@example.com", "wrongpassword");
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email or password.', $result['message']);
    }

    public function testLoginWithEmptyFields()
    {
        $result = loginUser($this->conn, "", "");
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Please fill in all fields.', $result['message']);
    }

    public function testLoginWithUnknownEmail()
    {
        $result = loginUser($this->conn, "doesnotexist@example.com", "somepassword");
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email or password.', $result['message']);
    }
}
