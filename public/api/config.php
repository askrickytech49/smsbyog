<?php
/**
 * Karl Peace Legacy Foundation — API Configuration & Database
 * -----------------------------------------------------------
 * MySQL PDO connection, CORS, auth helpers, audit logging.
 * All other API files require_once this file.
 */

// ── CORS ─────────────────────────────────────────────────────
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:3000',
    'http://localhost',
    'http://localhost:8080',
    'http://127.0.0.1:5173',
    'http://127.0.0.1',
    'http://127.0.0.1:8080',
    'http://karl-peace.localhost',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: http://localhost:5173");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ── DATABASE CREDENTIALS ─────────────────────────────────────
// Edit these to match your XAMPP / production MySQL setup
define('DB_HOST',     'localhost');
define('DB_PORT',     '3306');
define('DB_NAME',     'karl_peace_foundation');
define('DB_USER',     'root');          // XAMPP default
define('DB_PASS',     '');             // XAMPP default (empty)
define('DB_CHARSET',  'utf8mb4');

// ── SECURITY ─────────────────────────────────────────────────
define('JWT_SECRET',       'kplf_secret_key_change_in_production_2026');
define('SESSION_LIFETIME', 60 * 60 * 8); // 8 hours in seconds
define('UPLOAD_DIR',       __DIR__ . '/../uploads/');
define('UPLOAD_URL_BASE',  '/uploads/');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5 MB

// ── PDO SINGLETON ─────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            jsonResponse([
                'success' => false,
                'error'   => 'Database connection failed. Check config.php credentials.',
                'detail'  => $e->getMessage(),
            ], 503);
        }
    }
    return $pdo;
}

// ── JSON HELPERS ──────────────────────────────────────────────
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function getRequestBody(): array {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST ?: [];
}

// ── TOKEN HELPERS ─────────────────────────────────────────────
function generateToken(): string {
    return 'kplf_' . bin2hex(random_bytes(32));
}

function hashToken(string $token): string {
    return hash('sha256', $token);
}

function getBearerToken(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION']
           ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
           ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
        return trim($m[1]);
    }
    // Also accept via query param (for exports/downloads)
    if (!empty($_GET['token'])) {
        return trim($_GET['token']);
    }
    return null;
}

// ── AUTH ──────────────────────────────────────────────────────
/**
 * Returns the authenticated admin user row or null.
 */
function getAuthenticatedUser(): ?array {
    $token = getBearerToken();
    if (!$token) return null;

    $tokenHash = hashToken($token);
    $db = getDB();

    $stmt = $db->prepare(
        'SELECT s.user_id, s.expires_at, s.is_revoked,
                u.id, u.uid, u.email, u.display_name, u.role, u.is_super_admin, u.is_active
         FROM admin_sessions s
         JOIN admin_users u ON u.id = s.user_id
         WHERE s.token_hash = ? LIMIT 1'
    );
    $stmt->execute([$tokenHash]);
    $row = $stmt->fetch();

    if (!$row) return null;
    if ($row['is_revoked']) return null;
    if (!$row['is_active']) return null;
    if (strtotime($row['expires_at']) < time()) return null;

    // Touch last_used_at
    $db->prepare('UPDATE admin_sessions SET last_used_at = NOW() WHERE token_hash = ?')
       ->execute([$tokenHash]);

    return $row;
}

/**
 * Require auth or send 401.
 */
function requireAuth(): array {
    $user = getAuthenticatedUser();
    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'Unauthorized. Please log in.'], 401);
    }
    return $user;
}

/**
 * Require admin or editor role.
 */
function requireAdmin(): array {
    $user = requireAuth();
    if (!in_array($user['role'], ['admin', 'editor'], true)) {
        jsonResponse(['success' => false, 'error' => 'Forbidden. Admin role required.'], 403);
    }
    return $user;
}

/**
 * Require super admin.
 */
function requireSuperAdmin(): array {
    $user = requireAuth();
    if (!$user['is_super_admin']) {
        jsonResponse(['success' => false, 'error' => 'Forbidden. Super-admin role required.'], 403);
    }
    return $user;
}

// ── AUDIT LOG ─────────────────────────────────────────────────
function auditLog(
    string $action,
    ?string $entityType = null,
    ?string $entityId   = null,
    ?array  $oldValue   = null,
    ?array  $newValue   = null,
    ?array  $actor      = null
): void {
    try {
        $db = getDB();
        $db->prepare(
            'INSERT INTO audit_log
             (user_id, user_email, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $actor['id']    ?? null,
            $actor['email'] ?? null,
            $action,
            $entityType,
            $entityId,
            $oldValue ? json_encode($oldValue) : null,
            $newValue ? json_encode($newValue) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    } catch (Throwable) {
        // Audit failures must never break the main response
    }
}

// ── VALIDATION HELPERS ────────────────────────────────────────
function validateEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sanitizeString(string $val, int $maxLen = 255): string {
    return mb_substr(trim(strip_tags($val)), 0, $maxLen);
}

function generateId(string $prefix = ''): string {
    return $prefix . bin2hex(random_bytes(8));
}

// ── RATE LIMITER (simple file-based) ─────────────────────────
function checkRateLimit(string $key, int $maxHits = 10, int $windowSec = 60): void {
    $dir  = sys_get_temp_dir() . '/kplf_rl/';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . md5($key) . '.json';
    $now  = time();
    $data = ['hits' => [], 'count' => 0];

    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        if ($raw) $data = json_decode($raw, true) ?: $data;
    }

    // Prune old hits outside the window
    $data['hits'] = array_filter($data['hits'], fn($t) => ($now - $t) < $windowSec);
    $data['hits'][] = $now;
    $data['count']  = count($data['hits']);

    @file_put_contents($file, json_encode($data));

    if ($data['count'] > $maxHits) {
        jsonResponse([
            'success' => false,
            'error'   => 'Too many requests. Please wait and try again.',
        ], 429);
    }
}
