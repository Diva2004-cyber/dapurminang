<?php
include "connect.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_photo'];
    
    // Validasi file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.';
    } elseif ($file['size'] > $max_size) {
        $response['message'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
    } else {
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
        $upload_path = 'uploads/profile_photos/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            try {
                // Get old photo path if exists
                $stmt = $con->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $old_photo = $stmt->fetchColumn();
                
                // Update database
                $stmt = $con->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
                $stmt->execute([$upload_path, $user_id]);
                
                // Delete old photo if exists
                if ($old_photo && file_exists($old_photo)) {
                    unlink($old_photo);
                }
                
                $response['success'] = true;
                $response['message'] = 'Foto profil berhasil diperbarui';
                $response['photo_path'] = $upload_path;
            } catch (PDOException $e) {
                $response['message'] = 'Gagal menyimpan data: ' . $e->getMessage();
                // Delete uploaded file if database update fails
                unlink($upload_path);
            }
        } else {
            $response['message'] = 'Gagal mengupload file';
        }
    }
} else {
    $response['message'] = 'Tidak ada file yang diupload';
}

header('Content-Type: application/json');
echo json_encode($response);
?> 