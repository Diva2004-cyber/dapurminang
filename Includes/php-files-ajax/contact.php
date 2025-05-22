<?php
    // Fungsi untuk membersihkan input
    function test_input($data) {
        // Menghapus spasi yang tidak perlu
        $data = trim($data);
        
        // Menghapus backslashes jika ada
        $data = stripslashes($data);
        
        // Mengonversi karakter khusus menjadi entitas HTML
        $data = htmlspecialchars($data);
        
        return $data;
    }

    // Mengambil data dari form dan menampilkan pesan dummy
    if (isset($_POST['contact_name']) && isset($_POST['contact_email']) && isset($_POST['contact_subject']) && isset($_POST['contact_message'])) {
        $contact_name = test_input($_POST['contact_name']);
        $contact_email = test_input($_POST['contact_email']);
        $contact_subject = test_input($_POST['contact_subject']);
        $contact_message = test_input($_POST['contact_message']);

        // Dummy response untuk menampilkan pesan berhasil
        $message_sent = true; // Menandakan bahwa pesan berhasil dikirim
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Restaurant Padang</title>
    <link rel="stylesheet" href="path_to_your_css.css"> <!-- Ganti dengan path CSS Anda -->
</head>
<body>


        <!-- Pesan Dummy (Hanya tampil jika pesan berhasil dikirim) -->
        <?php if (isset($message_sent) && $message_sent === true): ?>
            <div class="alert alert-success">
                Pesan Anda berhasil dikirim! Terima kasih telah menghubungi kami, kami akan segera membalas emailmu.
            </div>
        <?php endif; ?>
    </section>

    <!-- Tambahkan script JS atau lainnya di sini jika diperlukan -->

</body>
</html>
