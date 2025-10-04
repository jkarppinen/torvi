<?php
/**
 * File Upload Validation Functions
 */

// Configuration
define('MAX_FILE_SIZE', 30 * 1024 * 1024); // 30MB
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'image/webp'
]);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

/**
 * Validate uploaded file
 * @param array $file The $_FILES array element
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_uploaded_file($file) {
    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }

    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['valid' => true, 'error' => null]; // No file is optional
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['valid' => false, 'error' => 'File exceeds size limit'];
        default:
            return ['valid' => false, 'error' => 'Unknown upload error'];
    }

    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB limit'];
    }

    // Validate MIME type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);

    if (!in_array($mime_type, ALLOWED_MIME_TYPES, true)) {
        return ['valid' => false, 'error' => 'Invalid file type. Only images (JPEG, PNG, GIF, WebP) are allowed'];
    }

    // Validate file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_EXTENSIONS, true)) {
        return ['valid' => false, 'error' => 'Invalid file extension'];
    }

    // Additional check: verify it's actually an image
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return ['valid' => false, 'error' => 'File is not a valid image'];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Get sanitized filename
 * @param string $filename Original filename
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove any path information
    $filename = basename($filename);

    // Get extension
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Generate safe filename with timestamp
    $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($filename, PATHINFO_FILENAME));
    $safe_name = substr($safe_name, 0, 50); // Limit length

    return $safe_name . '_' . time() . '.' . $extension;
}
?>
