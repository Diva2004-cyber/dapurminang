<?php
    include("../connect.php");

    if (isset($_POST['do'])) {
        $do = $_POST['do'];

        // Jika menambahkan gambar
        if ($do == "Add") {
            $image_name = $_POST['image_name'];
            $image = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];

            // Mendapatkan ekstensi file gambar
            $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

            // Mengecek apakah file adalah gambar dengan ekstensi yang valid
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (!in_array($image_ext, $allowed_ext)) {
                echo "<div class='alert alert-danger'>Invalid file type. Only JPG, JPEG, and PNG are allowed.</div>";
                exit();
            }

            // Membuat nama file gambar yang unik
            $new_image_name = uniqid() . "." . $image_ext;
            $target_path = "../Uploads/images/" . $new_image_name;

            // Pindahkan gambar ke direktori tujuan
            if (move_uploaded_file($image_tmp, $target_path)) {
                // Masukkan ke database
                $stmt = $con->prepare("INSERT INTO image_gallery (image_name, image) VALUES (?, ?)");
                $stmt->execute([$image_name, $new_image_name]);

                echo "<div class='alert alert-success'>Image added successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Failed to upload image!</div>";
            }
        }

        // Jika menghapus gambar
        if ($do == "Delete") {
            $image_id = $_POST['image_id'];

            // Ambil nama gambar dari database
            $stmt = $con->prepare("SELECT image FROM image_gallery WHERE image_id = ?");
            $stmt->execute([$image_id]);
            $image = $stmt->fetchColumn();

            if ($image) {
                // Hapus gambar dari server
                $image_path = "../Uploads/images/" . $image;
                if (file_exists($image_path)) {
                    unlink($image_path); // Menghapus file gambar
                }

                // Hapus entri gambar dari database
                $stmt = $con->prepare("DELETE FROM image_gallery WHERE image_id = ?");
                $stmt->execute([$image_id]);

                echo "success"; // Mengembalikan pesan sukses
            } else {
                echo "<div class='alert alert-danger'>Image not found in the database!</div>";
            }
        }
    }
?>
