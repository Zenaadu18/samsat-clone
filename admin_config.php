<?php
// admin_config.php
session_start();
require 'db.php';

/* ============================================================================
 * 1. INISIALISASI & KEAMANAN
 * ========================================================================== */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ============================================================================
 * 2. PROSES LOGIN & LOGOUT (DARI DATABASE)
 * ========================================================================== */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_config.php");
    exit;
}

$error_login = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];
    $csrfToken     = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $error_login = 'Validasi keamanan (CSRF) gagal. Silakan muat ulang halaman.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1");
    
    if ($stmt) {
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Mendukung password Hash (aman)
            if (password_verify($inputPassword, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                header("Location: admin_config.php");
                exit;
            } else {
                $error_login = 'Username atau Password salah!';
            }
        } else {
            $error_login = 'Username atau Password salah!';
        }
        $stmt->close();
    } else {
        $error_login = 'Gagal terhubung ke tabel admin_users.';
    }
    }
}

/* ============================================================================
 * 2. TAMPILAN FORM LOGIN
 * ========================================================================== */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_config.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h2 style="margin-top:0; color:#1e293b;"><i class="fa-solid fa-lock" style="margin-right: 8px;"></i>Admin Panel</h2>
        <p style="color:#64748b; margin-bottom:25px;">Masukkan kredensial Anda</p>
        <?php if($error_login) echo "<div class='error'>$error_login</div>"; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Masuk</button>
        </form>
    </div>
</body>
</html>
<?php
    exit; 
}

/* ============================================================================
 * 3. PROSES UPDATE MULTI-TABEL
 * ========================================================================== */
$pesanStatus = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_semua'])) {
    
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $pesanStatus = '<div class="alert error"><i class="fa-solid fa-circle-xmark" style="margin-right: 8px;"></i>Gagal menyimpan data: Validasi keamanan (CSRF) gagal.</div>';
    } else {
        $conn->begin_transaction();
        
        try {
        // A. Update Konfigurasi Dasar
        if (isset($_POST['config']) && is_array($_POST['config'])) {
            $stmt = $conn->prepare("UPDATE konfigurasi SET nilai = ? WHERE kunci = ?");
            foreach ($_POST['config'] as $kunci => $nilai) {
                $nilaiClean = trim((string)$nilai);
                $kunciClean = trim((string)$kunci);
                $stmt->bind_param("ss", $nilaiClean, $kunciClean);
                $stmt->execute();
            }
            $stmt->close();
        }

        // B. Update Tarif PKB
        if (isset($_POST['tarif_pkb']) && is_array($_POST['tarif_pkb'])) {
            $stmt = $conn->prepare("UPDATE tarif_pkb SET tarif_lama = ?, tarif_baru = ? WHERE k_guna = ?");
            foreach ($_POST['tarif_pkb'] as $kguna => $vals) {
                $lama = (float)$vals['lama'];
                $baru = (float)$vals['baru'];
                $stmt->bind_param("dds", $lama, $baru, $kguna);
                $stmt->execute();
            }
            $stmt->close();
        }

        // C. Update Tarif STNK & TNKB
        if (isset($_POST['tarif_stnk']) && is_array($_POST['tarif_stnk'])) {
            $stmt = $conn->prepare("UPDATE tarif_stnk_tnkb SET biaya_stnk = ?, biaya_tnkb = ? WHERE jenis = ?");
            foreach ($_POST['tarif_stnk'] as $jenis => $vals) {
                $stnk = (int)$vals['stnk'];
                $tnkb = (int)$vals['tnkb'];
                $stmt->bind_param("iis", $stnk, $tnkb, $jenis);
                $stmt->execute();
            }
            $stmt->close();
        }

        // D. Update Tarif SWDKLLJ
        if (isset($_POST['tarif_swdkllj']) && is_array($_POST['tarif_swdkllj'])) {
            $stmt = $conn->prepare("UPDATE tarif_swdkllj SET tarif = ?, admin = ?, denda_t1 = ?, denda_t2 = ?, denda_t3 = ?, denda_t4 = ? WHERE golongan = ?");
            foreach ($_POST['tarif_swdkllj'] as $gol => $v) {
                $tarif = (int)$v['tarif']; $admin = (int)$v['admin'];
                $t1 = (int)$v['t1']; $t2 = (int)$v['t2']; $t3 = (int)$v['t3']; $t4 = (int)$v['t4'];
                $stmt->bind_param("iiiiiis", $tarif, $admin, $t1, $t2, $t3, $t4, $gol);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        $pesanStatus = '<div class="alert success"><i class="fa-solid fa-check-circle" style="margin-right: 8px;"></i>Seluruh sistem & tarif berhasil diperbarui!</div>';
    } catch (Exception $e) {
        $conn->rollback();
        $pesanStatus = '<div class="alert error"><i class="fa-solid fa-circle-xmark" style="margin-right: 8px;"></i>Gagal menyimpan data: ' . $e->getMessage() . '</div>';
    }
    }
}

/* ============================================================================
 * 4. AMBIL DATA DARI DATABASE
 * ========================================================================== */
$grupAmnesti = []; $grupSistem = []; $grupTarifDasar = [];
$res = $conn->query("SELECT kunci, nilai FROM konfigurasi ORDER BY kunci ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $k = $row['kunci'];
        if (strpos($k, 'amnesti') !== false) {
            $grupAmnesti[] = $row;
        } elseif (strpos($k, 'api') !== false) {
            $grupSistem[] = $row;
        } else {
            $grupTarifDasar[] = $row;
        }
    }
    $res->free();
}

$dataPkb = [];
$res = $conn->query("SELECT k_guna, tarif_lama, tarif_baru FROM tarif_pkb");
if ($res) { while ($row = $res->fetch_assoc()) $dataPkb[] = $row; $res->free(); }

$dataStnk = [];
$res = $conn->query("SELECT jenis, biaya_stnk, biaya_tnkb FROM tarif_stnk_tnkb");
if ($res) { while ($row = $res->fetch_assoc()) $dataStnk[] = $row; $res->free(); }

$dataSwdkllj = [];
$res = $conn->query("SELECT golongan, tarif, admin, denda_t1, denda_t2, denda_t3, denda_t4 FROM tarif_swdkllj");
if ($res) { while ($row = $res->fetch_assoc()) $dataSwdkllj[] = $row; $res->free(); }

function formatLabel(string $kunci): string { return ucwords(str_replace('_', ' ', $kunci)); }

function renderFields($dataArray) {
    foreach ($dataArray as $row) {
        $kunci = htmlspecialchars($row['kunci']);
        $nilai = htmlspecialchars($row['nilai']);
        
        $isBoolean = (strpos($kunci, 'aktif') !== false || strpos($kunci, 'bebas') !== false);
        $inputType = (strpos($kunci, 'tanggal') !== false || strpos($kunci, 'mulai') !== false || strpos($kunci, 'selesai') !== false || strpos($kunci, 'berlaku') !== false) ? 'date' : 'text';
        $fullWidth = ($kunci === 'api_url_samsat') ? 'full-width' : '';
        $stepAttr = '';
        if (strpos($kunci, 'rasio') !== false || strpos($kunci, 'persen') !== false || strpos($kunci, 'diskon') !== false) {
            $inputType = 'number';
            $stepAttr = ' step="any"';
        }

        echo "<div class='form-group {$fullWidth}'>";
        echo "<label for='{$kunci}'>" . formatLabel($kunci) . "</label>";
        
        if ($isBoolean) {
            $checked = ((int)$nilai === 1) ? 'checked' : '';
            $labelColor = ((int)$nilai === 1) ? 'style="color: #10b981;"' : 'style="color: #475569;"';
            $labelText = ((int)$nilai === 1) ? 'AKTIF' : 'NON-AKTIF';
            
            echo "<div class='switch-wrapper'>
                    <label class='switch'>
                        <input type='hidden' name='config[{$kunci}]' value='0'>
                        <input type='checkbox' name='config[{$kunci}]' value='1' {$checked} class='master-toggle-item'>
                        <span class='slider'></span>
                    </label>
                    <span class='switch-label' {$labelColor}>{$labelText}</span>
                  </div>";
        } else {
            echo "<input type='{$inputType}'{$stepAttr} id='{$kunci}' name='config[{$kunci}]' value='{$nilai}' required>";
        }
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Konfigurasi & Tarif Pajak</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin_config.css?v=<?= filemtime(__DIR__ . '/assets/css/admin_config.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-title">
            <h1><i class="fa-solid fa-gears" style="margin-right: 8px;"></i>Admin Panel</h1>
            <p class="subtitle">Manajemen Konfigurasi Sistem & Master Tarif Pajak</p>
        </div>
        <div class="user-info">
            <span class="admin-badge"><i class="fa-solid fa-user" style="margin-right: 6px;"></i><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
            <a href="?logout=1" class="logout-btn">Keluar</a>
        </div>
    </div>

    <div class="card" style="position: relative;">
        <?= $pesanStatus ?>

        <!-- TOMBOL TABS -->
        <div class="tabs">
            <button class="tab-btn active" data-target="tab-amnesti"><i class="fa-solid fa-star" style="margin-right: 6px;"></i>Amnesti</button>
            <button class="tab-btn" data-target="tab-sistem"><i class="fa-solid fa-gear" style="margin-right: 6px;"></i>Sistem Dasar</button>
            <button class="tab-btn" data-target="tab-pkb"><i class="fa-solid fa-car" style="margin-right: 6px;"></i>Tarif PKB</button>
            <button class="tab-btn" data-target="tab-stnk"><i class="fa-solid fa-scroll" style="margin-right: 6px;"></i>STNK & TNKB</button>
            <button class="tab-btn" data-target="tab-swdkllj"><i class="fa-solid fa-shield-halved" style="margin-right: 6px;"></i>SWDKLLJ</button>
        </div>

        <!-- FORM UTAMA -->
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <!-- TAB 1: AMNESTI -->
            <div class="tab-content active" id="tab-amnesti">
                <div class="form-grid">
                    <?php renderFields($grupAmnesti); ?>
                </div>
            </div>

            <!-- TAB 2: SISTEM DASAR -->
            <div class="tab-content" id="tab-sistem">
                <div class="form-grid">
                    <?php renderFields($grupTarifDasar); ?>
                    <?php renderFields($grupSistem); ?>
                </div>
            </div>

            <!-- TAB 3: TARIF PKB -->
            <div class="tab-content" id="tab-pkb">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Penggunaan (K_GUNA)</th>
                                <th>Tarif Lama (Desimal)</th>
                                <th>Tarif Baru (Desimal)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataPkb as $row): $k = htmlspecialchars($row['k_guna']); ?>
                            <tr>
                                <td><span class="k-guna"><?= $k ?></span></td>
                                <td><input type="number" step="0.0001" name="tarif_pkb[<?= $k ?>][lama]" value="<?= $row['tarif_lama'] ?>" required></td>
                                <td><input type="number" step="0.0001" name="tarif_pkb[<?= $k ?>][baru]" value="<?= $row['tarif_baru'] ?>" required></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top: 10px; font-size: 13px; color: #64748b;">*Catatan: Nilai persentase dimasukkan dalam bentuk desimal (contoh: 1.5% ditulis 0.0150).</p>
            </div>

            <!-- TAB 4: STNK & TNKB -->
            <div class="tab-content" id="tab-stnk">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Jenis Kendaraan</th>
                                <th>Biaya STNK (Rp)</th>
                                <th>Biaya TNKB (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataStnk as $row): $j = htmlspecialchars($row['jenis']); ?>
                            <tr>
                                <td><span class="k-guna"><?= $j ?></span></td>
                                <td><input type="number" name="tarif_stnk[<?= $j ?>][stnk]" value="<?= $row['biaya_stnk'] ?>" required></td>
                                <td><input type="number" name="tarif_stnk[<?= $j ?>][tnkb]" value="<?= $row['biaya_tnkb'] ?>" required></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: SWDKLLJ -->
            <div class="tab-content" id="tab-swdkllj">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Gol</th>
                                <th>Tarif Pokok</th>
                                <th>Admin</th>
                                <th>Denda T1</th>
                                <th>Denda T2</th>
                                <th>Denda T3</th>
                                <th>Denda T4</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataSwdkllj as $row): $g = htmlspecialchars($row['golongan']); ?>
                            <tr>
                                <td><span class="k-guna" style="background:#fef3c7; color:#b45309;"><?= $g ?></span></td>
                                <td><input type="number" name="tarif_swdkllj[<?= $g ?>][tarif]" value="<?= $row['tarif'] ?>" required></td>
                                <td><input type="number" name="tarif_swdkllj[<?= $g ?>][admin]" value="<?= $row['admin'] ?>" required></td>
                                <td><input type="number" name="tarif_swdkllj[<?= $g ?>][t1]" value="<?= $row['denda_t1'] ?>" required></td>
                                <td><input type="number" name="tarif_swdkllj[<?= $g ?>][t2]" value="<?= $row['denda_t2'] ?>" required></td>
                                <td><input type="number" name="tarif_swdkllj[<?= $g ?>][t3]" value="<?= $row['denda_t3'] ?>" required></td>
                                <td><input type="number" name="tarif_swdkllj[<?= $g ?>][t4]" value="<?= $row['denda_t4'] ?>" required></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TOMBOL SIMPAN GLOBAL -->
            <div class="btn-save-container">
                <button type="submit" name="simpan_semua" class="btn-save"><i class="fa-solid fa-floppy-disk" style="margin-right: 8px;"></i>Simpan Seluruh Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/admin_config.js"></script>

</body>
</html>