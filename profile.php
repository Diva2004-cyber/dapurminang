<?php
    include "connect.php";
    include 'Includes/functions/functions.php';
    include "Includes/templates/header.php";
    include "Includes/templates/navbar.php";

    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Get user information
    $user_id = $_SESSION['user_id'];
    $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Process form submission
    $success_message = "";
    $error_message = "";
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $alamat = $_POST['alamat'] ?? '';
        $kota = $_POST['kota'] ?? '';
        $kode_pos = $_POST['kode_pos'] ?? '';
        
        try {
            $stmt = $con->prepare("UPDATE users SET 
                email = ?, 
                full_name = ?,
                phone_number = ?,
                alamat = ?, 
                kota = ?, 
                kode_pos = ? 
                WHERE user_id = ?");
                
            $stmt->execute([$email, $full_name, $phone_number, $alamat, $kota, $kode_pos, $user_id]);
            
            // Refresh user data
            $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $success_message = "Profil berhasil diperbarui!";
        } catch (PDOException $e) {
            $error_message = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
    <?php if($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>Profil Pengguna</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="profile-avatar">
                                <?php if (!empty($user['profile_photo']) && file_exists($user['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile Photo" class="profile-photo">
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-7x text-primary"></i>
                                <?php endif; ?>
                                <div class="profile-photo-overlay">
                                    <label for="profile_photo_input" class="btn btn-sm btn-light">
                                        <i class="fas fa-camera"></i> Ganti Foto
                                    </label>
                                </div>
                            </div>
                            <input type="file" id="profile_photo_input" accept="image/*" style="display: none;">
                            <h3 class="mt-3"><?php echo htmlspecialchars($user['username']); ?></h3>
                            <div class="mt-3">
                                <a href="my_orders.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-shopping-bag"></i> Pesanan Saya
                                </a>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                                </div>
                                
                                <h5 class="mt-4 mb-3 text-primary">Alamat Pengiriman</h5>
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary" id="getLocation">
                                        <i class="fas fa-map-marker-alt"></i> Gunakan Lokasi Saat Ini
                                    </button>
                                </div>
                                
                                <div id="map" style="height: 300px; width: 100%; display: none;" class="mb-3"></div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="kota" class="form-label">Kota</label>
                                            <input type="text" class="form-control" id="kota" name="kota" value="<?php echo htmlspecialchars($user['kota'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="kode_pos" class="form-label">Kode Pos</label>
                                            <input type="text" class="form-control" id="kode_pos" name="kode_pos" value="<?php echo htmlspecialchars($user['kode_pos'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="logout.php" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-avatar {
        width: 150px;
        height: 150px;
        margin: 0 auto;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #0d6efd;
        position: relative;
        overflow: hidden;
    }
    
    .profile-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .profile-photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .profile-avatar:hover .profile-photo-overlay {
        opacity: 1;
    }
    
    .profile-photo-overlay label {
        cursor: pointer;
    }
    
    .profile-photo-overlay label:hover {
        background-color: rgba(255, 255, 255, 0.9);
    }
    
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        border-bottom: none;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
</style>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
<script>
let map;
let marker;

// Initialize map (hidden by default)
function initMap(lat = -0.9236, lng = 100.4544) { // Default to Padang, Sumatra Barat
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat, lng },
        zoom: 15,
    });
    
    marker = new google.maps.Marker({
        position: { lat, lng },
        map: map,
        draggable: true,
    });
    
    // Update address when marker is dragged
    google.maps.event.addListener(marker, 'dragend', function() {
        const position = marker.getPosition();
        reverseGeocode(position.lat(), position.lng());
    });
}

// Get current location
document.getElementById("getLocation").addEventListener("click", function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Show map
            document.getElementById("map").style.display = "block";
            
            // Initialize map if not already initialized
            if (!map) {
                initMap(lat, lng);
            } else {
                map.setCenter({ lat, lng });
                marker.setPosition({ lat, lng });
            }
            
            // Get address from coordinates
            reverseGeocode(lat, lng);
        }, function(error) {
            alert("Tidak dapat mendapatkan lokasi Anda: " + error.message);
        });
    } else {
        alert("Geolocation tidak didukung oleh browser Anda.");
    }
});

// Reverse geocode (convert coordinates to address)
function reverseGeocode(lat, lng) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ location: { lat, lng } }, function(results, status) {
        if (status === "OK") {
            if (results[0]) {
                document.getElementById("alamat").value = results[0].formatted_address;
                
                // Extract city and postal code
                for (const component of results[0].address_components) {
                    if (component.types.includes("administrative_area_level_2")) {
                        document.getElementById("kota").value = component.long_name;
                    }
                    if (component.types.includes("postal_code")) {
                        document.getElementById("kode_pos").value = component.long_name;
                    }
                }
            } else {
                alert("Tidak ada hasil ditemukan");
            }
        } else {
            alert("Gagal mendapatkan alamat: " + status);
        }
    });
}

// Initialize map when page loads
document.addEventListener("DOMContentLoaded", function() {
    // Initialize map with default coordinates
    initMap();
});

// Handle profile photo upload
document.getElementById('profile_photo_input').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const formData = new FormData();
        formData.append('profile_photo', this.files[0]);
        
        fetch('process_profile_photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));
                
                // Update profile photo
                const profilePhoto = document.querySelector('.profile-photo');
                if (profilePhoto) {
                    profilePhoto.src = data.photo_path + '?' + new Date().getTime();
                } else {
                    const avatarDiv = document.querySelector('.profile-avatar');
                    avatarDiv.innerHTML = `
                        <img src="${data.photo_path}?${new Date().getTime()}" alt="Profile Photo" class="profile-photo">
                        <div class="profile-photo-overlay">
                            <label for="profile_photo_input" class="btn btn-sm btn-light">
                                <i class="fas fa-camera"></i> Ganti Foto
                            </label>
                        </div>
                    `;
                }
            } else {
                // Show error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                Terjadi kesalahan saat mengupload foto
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));
        });
    }
});
</script>

<?php include "Includes/templates/footer.php"; ?>
