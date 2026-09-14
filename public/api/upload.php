<?php
/**
 * Karl Peace Legacy Foundation — File Upload API
 * -----------------------------------------------
 * POST /api/upload.php
 *   Multipart: field name "file", optional fields: entity_type, entity_id
 *   JSON body: { data: "base64...", name: "filename.jpg", entity_type, entity_id }
 *
 * Returns: { success, url, filename, width, height, size }
 *
 * Public uploads (application documents) are allowed without auth but
 * stored in a dated subfolder. Admin uploads require auth.
 */
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$actor      = getAuthenticatedUser(); // null = public upload
$entityType = sanitizeString($_POST['entity_type'] ?? ($_GET['entity_type'] ?? ''), 64);
$entityId   = sanitizeString($_POST['entity_id']   ?? ($_GET['entity_id']   ?? ''), 64);

// Public uploads only allowed for scholarship application documents
$publicAllowed = in_array($entityType, [
    'application_transcript', 'application_id', 'application_admission',
    'application_passport', 'application_recommendation',
], true);

if (!$actor && !$publicAllowed) {
    jsonResponse(['success' => false, 'error' => 'Authentication required for this upload type.'], 401);
}

// Rate limit public uploads
if (!$actor) {
    checkRateLimit('upload_pub_' . ($_SERVER['REMOTE_ADDR'] ?? 'x'), 10, 300);
}

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
    'application/pdf' => 'pdf',
];

// ── Detect: multipart file or base64 JSON ────────────────────
$isBase64  = false;
$tmpPath   = null;
$origName  = 'upload';
$mimeType  = '';
$fileSize  = 0;

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (str_contains($contentType, 'multipart/form-data')) {
    // Multipart upload
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errMap = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form limit.',
            UPLOAD_ERR_PARTIAL    => 'File partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'No temp directory.',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk.',
        ];
        $code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
        jsonResponse(['success' => false, 'error' => $errMap[$code] ?? 'Upload error.'], 400);
    }

    $tmpPath  = $_FILES['file']['tmp_name'];
    $origName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
    $fileSize = $_FILES['file']['size'];
    $mimeType = mime_content_type($tmpPath);
} else {
    // JSON / base64 upload
    $body = getRequestBody();
    if (empty($body['data'])) {
        jsonResponse(['success' => false, 'error' => 'No file data provided.'], 400);
    }

    $base64Data = $body['data'];
    // Strip data URI prefix if present (data:image/jpeg;base64,...)
    if (str_contains($base64Data, ',')) {
        [$prefix, $base64Data] = explode(',', $base64Data, 2);
        if (preg_match('/data:([^;]+);base64/', $prefix, $m)) {
            $mimeType = $m[1];
        }
    }

    $decoded = base64_decode($base64Data, true);
    if ($decoded === false) {
        jsonResponse(['success' => false, 'error' => 'Invalid base64 data.'], 400);
    }

    $origName = pathinfo(sanitizeString($body['name'] ?? 'upload', 100), PATHINFO_FILENAME);
    $fileSize = strlen($decoded);
    $tmpPath  = tempnam(sys_get_temp_dir(), 'kplf_');
    file_put_contents($tmpPath, $decoded);
    $mimeType = $mimeType ?: mime_content_type($tmpPath);
    $isBase64 = true;
}

// ── Validate ─────────────────────────────────────────────────
if (!isset($allowedMimes[$mimeType])) {
    if ($isBase64 && $tmpPath) @unlink($tmpPath);
    jsonResponse(['success' => false, 'error' => 'File type not allowed. Accepted: JPG, PNG, WebP, GIF, PDF.'], 415);
}

if ($fileSize > MAX_UPLOAD_BYTES) {
    if ($isBase64 && $tmpPath) @unlink($tmpPath);
    jsonResponse(['success' => false, 'error' => 'File too large. Maximum size is 5MB.'], 413);
}

// ── Build destination path ────────────────────────────────────
$ext      = $allowedMimes[$mimeType];
$slug     = preg_replace('/[^a-z0-9_-]/', '_', strtolower(mb_substr($origName, 0, 60)));
$filename = $slug . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

// Subfolder by entity type or date
$subdir   = $entityType ?: date('Y/m');
$destDir  = UPLOAD_DIR . $subdir . '/';
if (!is_dir($destDir)) {
    @mkdir($destDir, 0755, true);
}
$destPath = $destDir . $filename;

// ── Move file ─────────────────────────────────────────────────
if ($isBase64) {
    $moved = rename($tmpPath, $destPath);
} else {
    $moved = move_uploaded_file($tmpPath, $destPath);
}

if (!$moved) {
    jsonResponse(['success' => false, 'error' => 'Could not save uploaded file.'], 500);
}

// ── Get image dimensions ──────────────────────────────────────
$width  = null;
$height = null;
if (str_starts_with($mimeType, 'image/')) {
    $size   = @getimagesize($destPath);
    $width  = $size[0] ?? null;
    $height = $size[1] ?? null;
}

// ── Record in media_uploads table ─────────────────────────────
$publicUrl = UPLOAD_URL_BASE . $subdir . '/' . $filename;
try {
    $db = getDB();
    $db->prepare(
        'INSERT INTO media_uploads
         (original_filename, stored_filename, file_path, public_url,
          file_size, mime_type, width, height, entity_type, entity_id, uploaded_by)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        $origName . '.' . $ext,
        $filename,
        $destPath,
        $publicUrl,
        $fileSize,
        $mimeType,
        $width,
        $height,
        $entityType ?: null,
        $entityId   ?: null,
        $actor['email'] ?? 'public',
    ]);
} catch (Throwable) {
    // Media record failure is non-fatal — file was already saved
}

jsonResponse([
    'success'  => true,
    'url'      => $publicUrl,
    'filename' => $filename,
    'mimeType' => $mimeType,
    'size'     => $fileSize,
    'width'    => $width,
    'height'   => $height,
]);
