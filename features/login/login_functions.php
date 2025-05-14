<?php
function loginUser(mysqli $conn, string $email, string $password): array
{
    if (empty($email) || empty($password)) {
        return ['status' => 'error', 'message' => 'Please fill in all fields.'];
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        return ['status' => 'error', 'message' => 'Database error: ' . $conn->error];
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['status' => 'error', 'message' => 'Invalid email or password.'];
    }

    // Check for user preferences
    $stmt_pref = $conn->prepare("SELECT COUNT(*) AS tag_count FROM user_preferences WHERE user_id = ?");
    $stmt_pref->bind_param("i", $user['user_id']);
    $stmt_pref->execute();
    $result_pref = $stmt_pref->get_result();
    $tag_data = $result_pref->fetch_assoc();

    return [
        'status' => 'success',
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'has_preferences' => ($tag_data['tag_count'] > 0)
    ];
}
