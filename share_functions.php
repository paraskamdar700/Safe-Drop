<?php
// share_functions.php
require_once 'config.php';

function createShare($user_id, $type, $data, $expiry_type, $expiry_value) {
    $pdo = getDBConnection();
    
    $unique_id = generateUniqueId();
    
    if ($type === 'file') {
        $stmt = $pdo->prepare("INSERT INTO shares (user_id, unique_id, type, file_path, file_name, file_size, expiry_type, expiry_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$user_id, $unique_id, $type, $data['file_path'], $data['file_name'], $data['file_size'], $expiry_type, $expiry_value]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO shares (user_id, unique_id, type, text_content, expiry_type, expiry_value) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$user_id, $unique_id, $type, $data['text_content'], $expiry_type, $expiry_value]);
    }
    
    if ($result) {
        return $unique_id;
    } else {
        return false;
    }
}

function getShare($unique_id) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM shares WHERE unique_id = ?");
    $stmt->execute([$unique_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function incrementViewCount($share_id) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("UPDATE shares SET views_count = views_count + 1 WHERE id = ?");
    return $stmt->execute([$share_id]);
}

function deleteShare($share_id) {
    $pdo = getDBConnection();
    
    // Get file path if it's a file share
    $stmt = $pdo->prepare("SELECT file_path FROM shares WHERE id = ?");
    $stmt->execute([$share_id]);
    $share = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete physical file if exists
    if ($share && $share['file_path'] && file_exists($share['file_path'])) {
        unlink($share['file_path']);
    }
    
    // Delete database record
    $stmt = $pdo->prepare("DELETE FROM shares WHERE id = ?");
    return $stmt->execute([$share_id]);
}

function getUserShares($user_id) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM shares WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isShareExpired($share) {
    if (!$share) return true;
    
    if ($share['expiry_type'] === 'views') {
        return $share['views_count'] >= $share['expiry_value'];
    } else { // time-based expiry
        $created_time = strtotime($share['created_at']);
        $current_time = time();
        $hours_passed = ($current_time - $created_time) / 3600;
        return $hours_passed >= $share['expiry_value'];
    }
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>