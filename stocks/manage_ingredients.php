<?php
require_once '../connect.php';
require_once '../auth_check.php';

// Hanya admin yang bisa akses
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit;
}

// Ambil data bahan baku
$sql = "SELECT * FROM ingredients ORDER BY name";
$result = $conn->query($sql);
$ingredients = $result->fetch_all(MYSQLI_ASSOC);

// Update stok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $ingredient_id = $_POST['ingredient_id'];
    $quantity = $_POST['quantity'];
    
    $sql = "UPDATE ingredients SET stock_quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $ingredient_id);
    
    if ($stmt->execute()) {
        $message = "Stok berhasil diperbarui";
    } else {
        $error = "Gagal memperbarui stok";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Stok Bahan Baku</title>
    <link rel="stylesheet" href="../Design/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manajemen Stok Bahan Baku</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th>Stok</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ingredient): ?>
                    <tr class="<?php echo $ingredient['stock_quantity'] < 10 ? 'danger' : ''; ?>">
                        <td><?php echo htmlspecialchars($ingredient['name']); ?></td>
                        <td><?php echo $ingredient['stock_quantity']; ?></td>
                        <td><?php echo htmlspecialchars($ingredient['unit']); ?></td>
                        <td>
                            <?php if ($ingredient['stock_quantity'] < 10): ?>
                                <span class="label label-danger">Stok Kritis</span>
                            <?php else: ?>
                                <span class="label label-success">Aman</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="ingredient_id" value="<?php echo $ingredient['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $ingredient['stock_quantity']; ?>" min="0">
                                <button type="submit" name="update_stock" class="btn btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 