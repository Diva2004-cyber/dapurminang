<?php
session_start();
$pageTitle = 'Kelola Voucher';
include 'connect.php';
include 'Includes/functions/functions.php';
include 'Includes/templates/header.php';
include 'Includes/templates/navbar.php';

$success_msg = '';
$error_msg = '';

// Tambah voucher
if(isset($_POST['add_voucher'])) {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $value = intval($_POST['value']);
    $min_order = intval($_POST['min_order']);
    $quota = $_POST['quota'] !== '' ? intval($_POST['quota']) : null;
    $valid_from = $_POST['valid_from'] ?: null;
    $valid_until = $_POST['valid_until'] ?: null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $stmt = $con->prepare("INSERT INTO vouchers (code, type, value, min_order, quota, valid_from, valid_until, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$code, $type, $value, $min_order, $quota, $valid_from, $valid_until, $is_active]);
        $success_msg = 'Voucher berhasil ditambahkan!';
    } catch(Exception $e) {
        $error_msg = 'Gagal menambah voucher: '.$e->getMessage();
    }
}
// Edit voucher
if(isset($_POST['edit_voucher'])) {
    $id = intval($_POST['id']);
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type'];
    $value = intval($_POST['value']);
    $min_order = intval($_POST['min_order']);
    $quota = $_POST['quota'] !== '' ? intval($_POST['quota']) : null;
    $valid_from = $_POST['valid_from'] ?: null;
    $valid_until = $_POST['valid_until'] ?: null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $stmt = $con->prepare("UPDATE vouchers SET code=?, type=?, value=?, min_order=?, quota=?, valid_from=?, valid_until=?, is_active=? WHERE id=?");
    try {
        $stmt->execute([$code, $type, $value, $min_order, $quota, $valid_from, $valid_until, $is_active, $id]);
        $success_msg = 'Voucher berhasil diupdate!';
    } catch(Exception $e) {
        $error_msg = 'Gagal update voucher: '.$e->getMessage();
    }
}
// Hapus voucher
if(isset($_POST['delete_voucher'])) {
    $id = intval($_POST['id']);
    $stmt = $con->prepare("DELETE FROM vouchers WHERE id=?");
    $stmt->execute([$id]);
    $success_msg = 'Voucher berhasil dihapus!';
}
// Ambil semua voucher
$stmt = $con->prepare("SELECT * FROM vouchers ORDER BY created_at DESC");
$stmt->execute();
$vouchers = $stmt->fetchAll();

// Ambil data riwayat penggunaan voucher
$filter_code = isset($_GET['filter_code']) ? trim($_GET['filter_code']) : '';
$filter_user = isset($_GET['filter_user']) ? trim($_GET['filter_user']) : '';
$where = [];
$params = [];
if($filter_code) {
    $where[] = 'v.code LIKE ?';
    $params[] = "%$filter_code%";
}
if($filter_user) {
    $where[] = 'u.full_name LIKE ?';
    $params[] = "%$filter_user%";
}
$where_sql = $where ? ('WHERE '.implode(' AND ', $where)) : '';
$sql = "SELECT vu.*, v.code, v.type, v.value, u.full_name, u.email FROM voucher_usages vu
        JOIN vouchers v ON vu.voucher_id = v.id
        JOIN users u ON vu.user_id = u.user_id
        $where_sql
        ORDER BY vu.used_at DESC";
$stmt = $con->prepare($sql);
$stmt->execute($params);
$voucher_usages = $stmt->fetchAll();
// Statistik penggunaan per voucher
$stat_sql = "SELECT code, COUNT(*) as total FROM voucher_usages vu JOIN vouchers v ON vu.voucher_id = v.id GROUP BY code ORDER BY total DESC";
$stat = $con->query($stat_sql)->fetchAll();

$voucher = isset($_SESSION['voucher']) ? $_SESSION['voucher'] : null;
$potongan = 0;
if($voucher) {
    if($voucher['discount_type'] == 'order_total') {
        $potongan = $voucher['type'] == 'fixed' ? $voucher['value'] : floor($total * $voucher['value'] / 100);
    } elseif($voucher['discount_type'] == 'shipping') {
        $potongan = $voucher['type'] == 'fixed' ? min($voucher['value'], $shipping_cost) : floor($shipping_cost * $voucher['value'] / 100);
    } elseif($voucher['discount_type'] == 'item' && $voucher['menu_id']) {
        foreach($_SESSION['cart'] as $item) {
            if($item['menu_id'] == $voucher['menu_id']) {
                $item_total = $item['menu_price'] * $item['quantity'] * 1000;
                $potongan = $voucher['type'] == 'fixed' ? min($voucher['value'], $item_total) : floor($item_total * $voucher['value'] / 100);
                break;
            }
        }
    }
}
?>
<div class="container mt-4">
    <h2>Kelola Voucher</h2>
    <?php if($success_msg): ?><div class="alert alert-success"><?php echo $success_msg; ?></div><?php endif; ?>
    <?php if($error_msg): ?><div class="alert alert-danger"><?php echo $error_msg; ?></div><?php endif; ?>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addVoucherModal">Tambah Voucher</button>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tipe</th>
                    <th>Nilai</th>
                    <th>Min. Belanja</th>
                    <th>Kuota</th>
                    <th>Terpakai</th>
                    <th>Berlaku</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($vouchers as $v): ?>
                <tr>
                    <td><?php echo htmlspecialchars($v['code']); ?></td>
                    <td><?php echo $v['type']=='fixed'?'Nominal':'Persen'; ?></td>
                    <td><?php echo $v['type']=='fixed'?'Rp '.number_format($v['value'],0,',','.'):($v['value'].'%'); ?></td>
                    <td>Rp <?php echo number_format($v['min_order'],0,',','.'); ?></td>
                    <td><?php echo $v['quota']!==null?$v['quota']:'Tak Terbatas'; ?></td>
                    <td><?php echo $v['used']; ?></td>
                    <td><?php echo ($v['valid_from']?date('d/m/Y',strtotime($v['valid_from'])):'-').' s/d '.($v['valid_until']?date('d/m/Y',strtotime($v['valid_until'])):'-'); ?></td>
                    <td><?php echo $v['is_active']?'Aktif':'Nonaktif'; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editVoucherModal<?php echo $v['id']; ?>">Edit</button>
                            <button type="submit" name="delete_voucher" class="btn btn-sm btn-danger" onclick="return confirm('Hapus voucher ini?')">Hapus</button>
                        </form>
                        <!-- Modal Edit Voucher -->
                        <div class="modal fade" id="editVoucherModal<?php echo $v['id']; ?>" tabindex="-1" role="dialog">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <form method="post">
                                <div class="modal-header">
                                  <h5 class="modal-title">Edit Voucher</h5>
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                  <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                                  <div class="form-group">
                                    <label>Kode Voucher</label>
                                    <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($v['code']); ?>" required>
                                  </div>
                                  <div class="form-group">
                                    <label>Tipe</label>
                                    <select name="type" class="form-control">
                                      <option value="fixed" <?php if($v['type']=='fixed') echo 'selected'; ?>>Nominal</option>
                                      <option value="percent" <?php if($v['type']=='percent') echo 'selected'; ?>>Persen</option>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                    <label>Nilai</label>
                                    <input type="number" name="value" class="form-control" value="<?php echo $v['value']; ?>" required>
                                  </div>
                                  <div class="form-group">
                                    <label>Minimal Belanja</label>
                                    <input type="number" name="min_order" class="form-control" value="<?php echo $v['min_order']; ?>">
                                  </div>
                                  <div class="form-group">
                                    <label>Kuota</label>
                                    <input type="number" name="quota" class="form-control" value="<?php echo $v['quota']; ?>">
                                  </div>
                                  <div class="form-group">
                                    <label>Berlaku Dari</label>
                                    <input type="date" name="valid_from" class="form-control" value="<?php echo $v['valid_from']?date('Y-m-d',strtotime($v['valid_from'])):''; ?>">
                                  </div>
                                  <div class="form-group">
                                    <label>Berlaku Sampai</label>
                                    <input type="date" name="valid_until" class="form-control" value="<?php echo $v['valid_until']?date('Y-m-d',strtotime($v['valid_until'])):''; ?>">
                                  </div>
                                  <div class="form-group">
                                    <label>Status</label>
                                    <input type="checkbox" name="is_active" value="1" <?php if($v['is_active']) echo 'checked'; ?>> Aktif
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="submit" name="edit_voucher" class="btn btn-success">Simpan</button>
                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal Tambah Voucher -->
    <div class="modal fade" id="addVoucherModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="post">
            <div class="modal-header">
              <h5 class="modal-title">Tambah Voucher</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label>Kode Voucher</label>
                <input type="text" name="code" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Tipe</label>
                <select name="type" class="form-control">
                  <option value="fixed">Nominal</option>
                  <option value="percent">Persen</option>
                </select>
              </div>
              <div class="form-group">
                <label>Nilai</label>
                <input type="number" name="value" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Minimal Belanja</label>
                <input type="number" name="min_order" class="form-control">
              </div>
              <div class="form-group">
                <label>Kuota</label>
                <input type="number" name="quota" class="form-control">
              </div>
              <div class="form-group">
                <label>Berlaku Dari</label>
                <input type="date" name="valid_from" class="form-control">
              </div>
              <div class="form-group">
                <label>Berlaku Sampai</label>
                <input type="date" name="valid_until" class="form-control">
              </div>
              <div class="form-group">
                <label>Status</label>
                <input type="checkbox" name="is_active" value="1" checked> Aktif
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" name="add_voucher" class="btn btn-primary">Tambah</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="container mt-5">
        <h3>Riwayat Penggunaan Voucher</h3>
        <form method="get" class="form-inline mb-3">
            <input type="text" name="filter_code" class="form-control mr-2" placeholder="Kode Voucher" value="<?php echo htmlspecialchars($filter_code); ?>">
            <input type="text" name="filter_user" class="form-control mr-2" placeholder="Nama User" value="<?php echo htmlspecialchars($filter_user); ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="vouchers.php" class="btn btn-secondary ml-2">Reset</a>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Kode Voucher</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Tanggal Pakai</th>
                        <th>Order ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($voucher_usages as $vu): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vu['code']); ?></td>
                        <td><?php echo htmlspecialchars($vu['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($vu['email']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($vu['used_at'])); ?></td>
                        <td><?php echo $vu['order_id']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <h5 class="mt-4">Statistik Penggunaan Voucher</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead><tr><th>Kode Voucher</th><th>Total Digunakan</th></tr></thead>
                <tbody>
                    <?php foreach($stat as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['code']); ?></td>
                        <td><?php echo $s['total']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'Includes/templates/footer.php'; ?> 