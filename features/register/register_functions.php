<?php

function isValidPassword($password): bool {
    return preg_match('/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $password);
}

function registerUser(mysqli $conn, string $username, string $email, string $password, string $confirm_password): array {
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        return ['status' => 'error', 'message' => 'Please fill in all fields.'];
    }

    if ($password !== $confirm_password) {
        return ['status' => 'error', 'message' => 'Passwords do not match.'];
    }

    if (!isValidPassword($password)) {
        return ['status' => 'error', 'message' => 'Password must be at least 8 characters long, include 1 number, and 1 uppercase letter.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['status' => 'error', 'message' => 'Please enter a valid email address.'];
    }

    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        return ['status' => 'error', 'message' => 'Email already registered.'];
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        return ['status' => 'success', 'email' => $email];
    } else {
        return ['status' => 'error', 'message' => 'Error: ' . $stmt->error];
    }
}
