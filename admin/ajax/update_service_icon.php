<?php
include '../../include/config.php';

header('Content-Type: application/json');

if (!isset($_POST['service_code'])) {
    echo json_encode(['success' => false, 'message' => 'Missing service code']);
    exit;
}

$service_code = mysqli_real_escape_string($conn, $_POST['service_code']);
$action = $_POST['action'] ?? 'upload';

// ACTION: Remove custom icon
if ($action === 'remove') {
    // Get current icon path to delete file
    $q = mysqli_query($conn, "SELECT icon_path FROM service_custom_icons WHERE service_code='$service_code'");
    if ($row = mysqli_fetch_assoc($q)) {
        $file = __DIR__ . '/../../' . $row['icon_path'];
        if (file_exists($file)) unlink($file);
    }
    mysqli_query($conn, "DELETE FROM service_custom_icons WHERE service_code='$service_code'");
    echo json_encode(['success' => true, 'message' => 'Custom icon removed']);
    exit;
}

// ACTION: Set icon via URL
if (!empty($_POST['icon_url'])) {
    $icon_url = trim($_POST['icon_url']);
    // Save URL directly to DB
    $savePath_esc = mysqli_real_escape_string($conn, $icon_url);
    mysqli_query($conn, "INSERT INTO service_custom_icons (service_code, icon_path) VALUES ('$service_code', '$savePath_esc') 
                         ON DUPLICATE KEY UPDATE icon_path='$savePath_esc'");
    
    echo json_encode(['success' => true, 'message' => 'Icon URL saved', 'icon_url' => $icon_url]);
    exit;
}

// ACTION: Upload file
if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/webp', 'image/gif'];
    $fileType = $_FILES['icon_file']['type'];
    
    if (!in_array($fileType, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Use PNG, JPG, SVG, or WebP']);
        exit;
    }
    
    if ($_FILES['icon_file']['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large. Max 2MB']);
        exit;
    }
    
    $ext = pathinfo($_FILES['icon_file']['name'], PATHINFO_EXTENSION);
    $filename = preg_replace('/[^a-z0-9_-]/', '_', strtolower($service_code)) . '.' . strtolower($ext);
    $savePath = 'service_images/' . $filename;
    $fullPath = __DIR__ . '/../../' . $savePath;
    
    if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $fullPath)) {
        $savePath_esc = mysqli_real_escape_string($conn, $savePath);
        mysqli_query($conn, "INSERT INTO service_custom_icons (service_code, icon_path) VALUES ('$service_code', '$savePath_esc') 
                             ON DUPLICATE KEY UPDATE icon_path='$savePath_esc'");
        echo json_encode(['success' => true, 'message' => 'Icon uploaded', 'icon_url' => $savePath . '?t=' . time()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'No file or URL provided']);
