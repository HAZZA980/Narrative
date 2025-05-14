<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../features/register/register_functions.php';

class RegisterTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli("localhost", "root", "", "narrative_testdb");
        if ($this->conn->connect_error) {
            die("DB Connection failed: " . $this->conn->connect_error);
        }

        // Clean any previous test runs
        $this->conn->query("DELETE FROM users WHERE email = 'testregister@example.com'");
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE email = 'testregister@example.com'");
        $this->conn->close();
    }

    public function testSuccessfulRegistration()
    {
        $result = registerUser(
            $this->conn,
            'NewUser',
            'testregister@example.com',
            'Password1',
            'Password1'
        );

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('testregister@example.com', $result['email']);
    }

    public function testEmptyFields()
    {
        $result = registerUser($this->conn, '', '', '', '');
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Please fill in all fields.', $result['message']);
    }

    public function testPasswordMismatch()
    {
        $result = registerUser($this->conn, 'User', 'email@example.com', 'Password1', 'Mismatch');
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Passwords do not match.', $result['message']);
    }

    public function testWeakPassword()
    {
        $result = registerUser($this->conn, 'User', 'email@example.com', 'weak', 'weak');
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Password must be', $result['message']);
    }

    public function testInvalidEmail()
    {
        $result = registerUser($this->conn, 'User', 'invalid-email', 'Password1', 'Password1');
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Please enter a valid email address.', $result['message']);
    }

    public function testDuplicateEmail()
    {
        // Insert once
        registerUser($this->conn, 'User', 'testregister@example.com', 'Password1', 'Password1');
        // Try again
        $result = registerUser($this->conn, 'User', 'testregister@example.com', 'Password1', 'Password1');
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Email already registered.', $result['message']);
    }
}
