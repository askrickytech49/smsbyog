<?php
/**
 * Karl Peace Legacy Foundation — Check Auth
 * GET /api/check-auth.php
 * Returns current session user or 401.
 */
require_once __DIR__ . '/config.php';

$user = getAuthenticatedUser();

if (!$user) {
    jsonResponse(['success' => false, 'authenticated' => false], 401);
}

jsonResponse([
    'success'       => true,
    'authenticated' => true,
    'user' => [
        'uid'          => $user['uid'],
        'email'        => $user['email'],
        'displayName'  => $user['display_name'],
        'role'         => $user['role'],
        'isSuperAdmin' => (bool) $user['is_super_admin'],
    ],
]);
