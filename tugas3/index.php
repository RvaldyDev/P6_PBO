<?php
declare(strict_types=1);

/**
 * Tugas 3: Pengembangan CRUD Mahasiswa Berbasis Inheritance (Kelompok)
 * Modul Praktikum 06: Pewarisan Class (Inheritance)
 * 
 * Pengembang:
 * Mahasiswa : Sanziya Rivaldi
 * NIM       : 2530521070
 * Prodi     : Teknik Informatika
 * Dosen     : Prajoko, M.Kom.
 */

require_once __DIR__ . '/DataMahasiswa.php';

// Inisialisasi Repository
$repo = new DataMahasiswa();

// Ambil pesan notifikasi flash session (PRG Pattern)
$flashSukses = $_SESSION['flash_sukses'] ?? '';
$flashError  = $_SESSION['flash_error'] ?? '';
$oldInput    = $_SESSION['old_input'] ?? [];
unset($_SESSION['flash_sukses'], $_SESSION['flash_error'], $_SESSION['old_input']);

// State Awal Form
$isEditMode = false;
$editTarget = null;
$formData   = [
    'nim'      => $oldInput['nim'] ?? '',
    'nama'     => $oldInput['nama'] ?? '',
    'prodi'    => $oldInput['prodi'] ?? 'Teknik Informatika',
    'semester' => isset($oldInput['semester']) ? (int)$oldInput['semester'] : 1,
    'jenis'    => $oldInput['jenis'] ?? 'reguler',
    'ukt'      => isset($oldInput['ukt']) ? (int)$oldInput['ukt'] : 5000000,
    'potongan' => isset($oldInput['potongan']) ? (int)$oldInput['potongan'] : 0,
];

// -----------------------------------------------------------------------------
// 1. Tangani Aksi GET (Reset & Persiapan Edit)
// -----------------------------------------------------------------------------
if (isset($_GET['aksi'])) {
    $aksiGet = (string)$_GET['aksi'];

    if ($aksiGet === 'reset') {
        $repo->resetData();
        $_SESSION['flash_sukses'] = "Data mahasiswa berhasil direset ke data sampel praktikum!";
        header('Location: index.php');
        exit;
    }

    if ($aksiGet === 'edit' && !empty($_GET['nim'])) {
        $nimTarget = trim((string)$_GET['nim']);
        $editTarget = $repo->cari($nimTarget);

        if ($editTarget !== null) {
            $isEditMode = true;
            $formData['nim']      = $editTarget->getNim();
            $formData['nama']     = $editTarget->getNama();
            $formData['prodi']    = $editTarget->getProdi();
            $formData['semester'] = $editTarget->getSemester();
            $formData['jenis']    = mb_strtolower($editTarget->getJenis());
            $formData['ukt']      = $editTarget->getUkt();
            $formData['potongan'] = ($editTarget instanceof MahasiswaBeasiswa) ? $editTarget->getPotonganPersen() : 0;
        } else {
            $_SESSION['flash_error'] = "Data mahasiswa dengan NIM '{$nimTarget}' tidak ditemukan.";
            header('Location: index.php');
            exit;
        }
    }

    if ($aksiGet === 'hapus' && !empty($_GET['nim'])) {
        $nimTarget = trim((string)$_GET['nim']);
        $mhsHapus  = $repo->cari($nimTarget);

        if ($mhsHapus !== null) {
            $namaMhs = $mhsHapus->getNama();
            $repo->hapus($nimTarget);
            $_SESSION['flash_sukses'] = "Data mahasiswa '{$namaMhs}' (NIM: {$nimTarget}) berhasil dihapus.";
        } else {
            $_SESSION['flash_error'] = "Data dengan NIM '{$nimTarget}' tidak ditemukan.";
        }
        header('Location: index.php');
        exit;
    }
}

// -----------------------------------------------------------------------------
// 2. Tangani Aksi POST (Tambah, Edit/Ubah, Hapus)
// -----------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $aksi = (string)($_POST['aksi'] ?? '');

    // Aksi: Hapus via POST (Lebih aman dari CSRF dasar)
    if ($aksi === 'hapus') {
        $nimTarget = trim((string)($_POST['nim'] ?? ''));
        $target    = $repo->cari($nimTarget);

        if ($target !== null && $repo->hapus($nimTarget)) {
            $_SESSION['flash_sukses'] = "Mahasiswa '{$target->getNama()}' (NIM: {$nimTarget}) berhasil dihapus!";
        } else {
            $_SESSION['flash_error'] = "Gagal menghapus data. NIM tidak ditemukan.";
        }
        header('Location: index.php');
        exit;
    }

    // Aksi: Tambah atau Ubah/Edit
    if ($aksi === 'tambah' || $aksi === 'ubah') {
        try {
            $nimRaw      = trim((string)($_POST['nim'] ?? ''));
            $nimLama     = trim((string)($_POST['nim_lama'] ?? ''));
            $nama        = trim((string)($_POST['nama'] ?? ''));
            $prodi       = trim((string)($_POST['prodi'] ?? ''));
            $semesterRaw = $_POST['semester'] ?? null;
            $jenis       = mb_strtolower(trim((string)($_POST['jenis'] ?? 'reguler')));
            $uktRaw      = $_POST['ukt'] ?? null;
            $potonganRaw = $_POST['potongan'] ?? 0;

            // Validasi tipe bilangan bulat (Integer Filter)
            $semester = filter_var($semesterRaw, FILTER_VALIDATE_INT);
            $ukt      = filter_var($uktRaw, FILTER_VALIDATE_INT);

            if ($semester === false) {
                throw new InvalidArgumentException("Semester harus berupa bilangan bulat antara 1 sampai 14.");
            }
            if ($ukt === false) {
                throw new InvalidArgumentException("UKT harus berupa bilangan bulat valid (tanpa titik atau koma).");
            }

            // Pada mode ubah, NIM tidak boleh diubah
            $nimFinal = ($aksi === 'ubah') ? $nimLama : $nimRaw;

            // Instansiasi objek child yang sesuai secara polimorfis
            /** @var Mahasiswa $objekMahasiswa */
            if ($jenis === 'reguler') {
                $objekMahasiswa = new MahasiswaReguler(
                    $nimFinal,
                    $nama,
                    $prodi,
                    $semester,
                    $ukt
                );
            } elseif ($jenis === 'beasiswa') {
                $potongan = filter_var($potonganRaw, FILTER_VALIDATE_INT);
                if ($potongan === false) {
                    throw new InvalidArgumentException("Potongan beasiswa harus berupa bilangan bulat antara 0 sampai 100%.");
                }
                $objekMahasiswa = new MahasiswaBeasiswa(
                    $nimFinal,
                    $nama,
                    $prodi,
                    $semester,
                    $ukt,
                    $potongan
                );
            } else {
                throw new InvalidArgumentException("Jenis mahasiswa '{$jenis}' tidak dikenali.");
            }

            // Simpan ke Repository
            if ($aksi === 'ubah') {
                $berhasil = $repo->ubah($nimLama, $objekMahasiswa);
                if (!$berhasil) {
                    throw new InvalidArgumentException("Gagal memperbarui data: Data NIM '{$nimLama}' tidak ditemukan atau perubahan NIM dilarang.");
                }
                $_SESSION['flash_sukses'] = "Data mahasiswa '{$objekMahasiswa->getNama()}' (NIM: {$nimFinal}) berhasil diperbarui!";
            } else {
                $berhasil = $repo->tambah($objekMahasiswa);
                if (!$berhasil) {
                    throw new InvalidArgumentException("NIM '{$nimFinal}' sudah terdaftar dalam sistem! Setiap mahasiswa harus memiliki NIM unik.");
                }
                $_SESSION['flash_sukses'] = "Mahasiswa baru '{$objekMahasiswa->getNama()}' ({$objekMahasiswa->getJenis()}) berhasil ditambahkan!";
            }

            header('Location: index.php');
            exit;

        } catch (InvalidArgumentException $e) {
            $_SESSION['flash_error'] = "Validasi Gagal: " . $e->getMessage();
            $_SESSION['old_input']   = $_POST;
            $redirectUrl = ($aksi === 'ubah' && !empty($nimLama)) ? "index.php?aksi=edit&nim=" . urlencode($nimLama) : "index.php";
            header("Location: {$redirectUrl}");
            exit;
        }
    }
}

// Ambil seluruh data mahasiswa
$daftarMahasiswa = $repo->semua();

// Hitung Statistik Data
$totalMahasiswa = count($daftarMahasiswa);
$totalReguler   = 0;
$totalBeasiswa  = 0;
$totalEstimasiBiaya = 0;

foreach ($daftarMahasiswa as $m) {
    if ($m instanceof MahasiswaBeasiswa) {
        $totalBeasiswa++;
    } else {
        $totalReguler++;
    }
    $totalEstimasiBiaya += $m->hitungBiaya();
}

// Helper sanitasi output HTML
function aman(mixed $val): string {
    return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatRp(int $angka): string {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas 3 - CRUD Mahasiswa Berbasis Inheritance (P6 PBO)</title>
    <style>
    :root {
        --bg-color: #0b0d10;
        --panel-bg: #14171d;
        --card-bg: #181c24;
        --input-bg: #0e1117;
        --accent-cyan: #00f2fe;
        --accent-blue: #4facfe;
        --accent-green: #00e676;
        --accent-gold: #ffb703;
        --accent-red: #ff5252;
        --text-main: #e2e8f0;
        --text-muted: #8a99ad;
        --border-color: #232936;
        --border-focus: #00f2fe;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-main);
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
        padding: 1.5rem;
        min-height: 100vh;
    }

    .container {
        max-width: 1280px;
        margin: 0 auto;
    }

    /* Top Header & Navigation */
    .top-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--accent-cyan);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 6px 14px;
        background: rgba(0, 242, 254, 0.08);
        border: 1px solid rgba(0, 242, 254, 0.2);
        border-radius: 8px;
        transition: all 0.2s;
    }

    .nav-link:hover {
        background: rgba(0, 242, 254, 0.16);
        transform: translateX(-3px);
    }

    .btn-reset {
        background: rgba(255, 82, 82, 0.1);
        color: var(--accent-red);
        border: 1px solid rgba(255, 82, 82, 0.3);
        padding: 6px 14px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-reset:hover {
        background: rgba(255, 82, 82, 0.2);
    }

    .header-title-box {
        background: linear-gradient(135deg, rgba(20, 23, 29, 0.95), rgba(24, 28, 36, 0.95));
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .header-badge {
        display: inline-block;
        background: rgba(0, 242, 254, 0.12);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 242, 254, 0.3);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .header-title-box h1 {
        font-size: 1.9rem;
        background: linear-gradient(135deg, #ffffff, #a5b4fc);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.35rem;
    }

    .header-title-box p {
        color: var(--text-muted);
        font-size: 0.92rem;
    }

    /* Notification Alerts */
    .alert {
        padding: 1rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: rgba(0, 230, 118, 0.12);
        border: 1px solid rgba(0, 230, 118, 0.35);
        color: #a7f3d0;
    }

    .alert-error {
        background: rgba(255, 82, 82, 0.12);
        border: 1px solid rgba(255, 82, 82, 0.35);
        color: #fca5a5;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.35rem;
    }

    .stat-value {
        font-size: 1.55rem;
        font-weight: 800;
        color: #ffffff;
    }

    /* Main Workspace Grid (Form + Table) */
    .workspace-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .workspace-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Form Card */
    .form-card {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        position: sticky;
        top: 1rem;
    }

    .form-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .mode-badge {
        font-size: 0.72rem;
        padding: 3px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        font-weight: 700;
    }

    .mode-add {
        background: rgba(0, 242, 254, 0.15);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 242, 254, 0.3);
    }

    .mode-edit {
        background: rgba(255, 183, 3, 0.15);
        color: var(--accent-gold);
        border: 1px solid rgba(255, 183, 3, 0.3);
    }

    .form-group {
        margin-bottom: 1.15rem;
    }

    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0.4rem;
    }

    .form-label .required {
        color: var(--accent-red);
    }

    .form-control {
        width: 100%;
        padding: 0.65rem 0.85rem;
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: #ffffff;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(0, 242, 254, 0.15);
    }

    .form-control[readonly] {
        background: rgba(255, 255, 255, 0.04);
        color: var(--text-muted);
        cursor: not-allowed;
        border-style: dashed;
    }

    .input-hint {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.3rem;
        display: block;
    }

    /* Jenis Mahasiswa Selector (Radio Buttons) */
    .radio-pills {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        background: var(--input-bg);
        padding: 4px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
    }

    .radio-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.55rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        color: var(--text-muted);
    }

    .radio-label input {
        display: none;
    }

    .radio-label.active-reguler {
        background: rgba(0, 242, 254, 0.15);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 242, 254, 0.4);
    }

    .radio-label.active-beasiswa {
        background: rgba(255, 183, 3, 0.15);
        color: var(--accent-gold);
        border: 1px solid rgba(255, 183, 3, 0.4);
    }

    /* Live Calculation Box */
    .calc-box {
        background: rgba(0, 0, 0, 0.35);
        border: 1px dashed var(--border-color);
        border-radius: 10px;
        padding: 0.85rem 1rem;
        margin-top: 1rem;
        margin-bottom: 1.25rem;
    }

    .calc-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        margin-bottom: 0.25rem;
        color: var(--text-muted);
    }

    .calc-total {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 0.4rem;
        margin-top: 0.4rem;
        display: flex;
        justify-content: space-between;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--accent-green);
    }

    .btn-submit {
        width: 100%;
        padding: 0.75rem;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
        color: #0b0d10;
        font-weight: 700;
        font-size: 0.92rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.15s;
    }

    .btn-submit:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }

    .btn-cancel {
        display: block;
        width: 100%;
        text-align: center;
        margin-top: 0.6rem;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.82rem;
        padding: 0.4rem;
    }

    .btn-cancel:hover {
        color: #ffffff;
    }

    /* Table Card & Toolbar */
    .table-card {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .table-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #ffffff;
    }

    .search-box-wrap {
        position: relative;
        width: 280px;
    }

    .search-input {
        width: 100%;
        padding: 0.55rem 0.85rem 0.55rem 2.2rem;
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: #ffffff;
        font-size: 0.85rem;
        outline: none;
    }

    .search-input:focus {
        border-color: var(--border-focus);
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.85rem;
        pointer-events: none;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
        text-align: left;
    }

    .data-table th {
        background: rgba(0, 0, 0, 0.35);
        padding: 0.85rem 1rem;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .data-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        vertical-align: middle;
    }

    .data-table tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .badge-jenis {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 4px;
        text-transform: uppercase;
    }

    .badge-reguler {
        background: rgba(0, 242, 254, 0.15);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 242, 254, 0.3);
    }

    .badge-beasiswa {
        background: rgba(255, 183, 3, 0.15);
        color: var(--accent-gold);
        border: 1px solid rgba(255, 183, 3, 0.3);
    }

    .biaya-akhir {
        font-weight: 700;
        color: var(--accent-green);
    }

    .actions-group {
        display: flex;
        gap: 0.4rem;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-edit {
        background: rgba(79, 172, 254, 0.15);
        color: var(--accent-blue);
        border: 1px solid rgba(79, 172, 254, 0.3);
    }

    .btn-edit:hover {
        background: rgba(79, 172, 254, 0.3);
    }

    .btn-delete {
        background: rgba(255, 82, 82, 0.15);
        color: var(--accent-red);
        border: 1px solid rgba(255, 82, 82, 0.3);
    }

    .btn-delete:hover {
        background: rgba(255, 82, 82, 0.3);
    }

    .empty-row {
        text-align: center;
        color: var(--text-muted);
        padding: 2.5rem 1rem !important;
    }

    /* Scenarios Information Box */
    .scenarios-box {
        margin-top: 2rem;
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.5rem;
    }

    .scenarios-box h3 {
        font-size: 1.1rem;
        color: #ffffff;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .scenarios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 0.75rem;
        font-size: 0.83rem;
    }

    .scenario-item {
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
    }

    .scenario-num {
        font-weight: 700;
        color: var(--accent-cyan);
        margin-right: 0.25rem;
    }

    .footer {
        text-align: center;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 2rem;
        padding-bottom: 1.5rem;
    }
    </style>
</head>

<body>

    <div class="container">
        <!-- Top Navigation -->
        <div class="top-nav">
            <a href="../index.php" class="nav-link">&larr; Kembali ke Portal Praktikum 06</a>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a href="../tugas2.php" class="nav-link"
                    style="color: var(--accent-green); border-color: rgba(0, 230, 118, 0.3); background: rgba(0, 230, 118, 0.08);">Buka
                    Tugas 2 (Pegawai) &rarr;</a>
                <a href="index.php?aksi=reset" class="btn-reset"
                    onclick="return confirm('Apakah Anda yakin ingin mereset seluruh data ke data sampel demonstrasi?')">Reset
                    Data Sampel</a>
            </div>
        </div>

        <!-- Header -->
        <div class="header-title-box">
            <span class="header-badge">Tugas 3 Praktikum PBO (Kelompok)</span>
            <h1>Pengembangan CRUD Mahasiswa Berbasis Inheritance</h1>
            <p>
                Penerapan class abstract <code>Mahasiswa</code> dengan dua child class <code>MahasiswaReguler</code> dan
                <code>MahasiswaBeasiswa</code>.
                Mendukung pemilihan jenis, penyesuaian UKT, potongan beasiswa, serta pencegahan mutasi NIM saat edit.
            </p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flashSukses): ?>
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <div><?= aman($flashSukses) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
        <div class="alert alert-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <div><?= aman($flashError) ?></div>
        </div>
        <?php endif; ?>

        <!-- Quick Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Mahasiswa</div>
                <div class="stat-value"><?= $totalMahasiswa ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Kategori Reguler</div>
                <div class="stat-value" style="color: var(--accent-cyan);"><?= $totalReguler ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Penerima Beasiswa</div>
                <div class="stat-value" style="color: var(--accent-gold);"><?= $totalBeasiswa ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Penerimaan UKT</div>
                <div class="stat-value" style="color: var(--accent-green); font-size: 1.35rem;">
                    <?= formatRp($totalEstimasiBiaya) ?></div>
            </div>
        </div>

        <!-- Main Workspace: Form Input on Left, Table on Right -->
        <div class="workspace-grid">
            <!-- Form Input / Edit -->
            <div class="form-card">
                <div class="form-title">
                    <span><?= $isEditMode ? 'Form Ubah Data Mahasiswa' : 'Tambah Mahasiswa Baru' ?></span>
                    <span class="mode-badge <?= $isEditMode ? 'mode-edit' : 'mode-add' ?>">
                        <?= $isEditMode ? 'Mode Edit' : 'Mode Tambah' ?>
                    </span>
                </div>

                <form method="POST" action="index.php" id="formMahasiswa">
                    <input type="hidden" name="aksi" value="<?= $isEditMode ? 'ubah' : 'tambah' ?>">
                    <?php if ($isEditMode): ?>
                    <input type="hidden" name="nim_lama" value="<?= aman($formData['nim']) ?>">
                    <?php endif; ?>

                    <!-- NIM Input -->
                    <div class="form-group">
                        <label class="form-label" for="nimInput">
                            NIM (Nomor Induk Mahasiswa) <span class="required">*</span>
                        </label>
                        <input type="text" id="nimInput" name="nim" class="form-control"
                            value="<?= aman($formData['nim']) ?>" placeholder="Contoh: 2530521070"
                            <?= $isEditMode ? 'readonly' : 'required' ?>>
                        <?php if ($isEditMode): ?>
                        <small class="input-hint" style="color: var(--accent-gold);">
                            &#128274; NIM bersifat permanen (immutable) dan tidak boleh diubah saat edit.
                        </small>
                        <?php else: ?>
                        <small class="input-hint">NIM harus unik dan wajib diisi.</small>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label class="form-label" for="namaInput">
                            Nama Lengkap <span class="required">*</span>
                        </label>
                        <input type="text" id="namaInput" name="nama" class="form-control"
                            value="<?= aman($formData['nama']) ?>" placeholder="Nama lengkap mahasiswa" required>
                    </div>

                    <!-- Program Studi -->
                    <div class="form-group">
                        <label class="form-label" for="prodiSelect">
                            Program Studi <span class="required">*</span>
                        </label>
                        <select id="prodiSelect" name="prodi" class="form-control" required>
                            <?php
                            $daftarProdi = [
                                'Teknik Informatika',
                                'Sistem Informasi',
                                'Teknik Sipil',
                                'Teknik Mesin',
                                'Pendidikan Teknologi Informasi'
                            ];
                            foreach ($daftarProdi as $p):
                            ?>
                            <option value="<?= aman($p) ?>" <?= ($formData['prodi'] === $p) ? 'selected' : '' ?>>
                                <?= aman($p) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Semester -->
                    <div class="form-group">
                        <label class="form-label" for="semesterInput">
                            Semester (1 - 14) <span class="required">*</span>
                        </label>
                        <input type="number" id="semesterInput" name="semester" class="form-control" min="1" max="14"
                            value="<?= aman($formData['semester']) ?>" required>
                        <small class="input-hint">Validasi semester mematuhi aturan akademik (1 s/d 14).</small>
                    </div>

                    <!-- Jenis Mahasiswa (Inheritance Selection) -->
                    <div class="form-group">
                        <label class="form-label">
                            Jenis Mahasiswa <span class="required">*</span>
                        </label>
                        <div class="radio-pills">
                            <label class="radio-label <?= ($formData['jenis'] === 'reguler') ? 'active-reguler' : '' ?>"
                                id="labelReguler">
                                <input type="radio" name="jenis" value="reguler"
                                    <?= ($formData['jenis'] === 'reguler') ? 'checked' : '' ?>>
                                <span>Mahasiswa Reguler</span>
                            </label>
                            <label
                                class="radio-label <?= ($formData['jenis'] === 'beasiswa') ? 'active-beasiswa' : '' ?>"
                                id="labelBeasiswa">
                                <input type="radio" name="jenis" value="beasiswa"
                                    <?= ($formData['jenis'] === 'beasiswa') ? 'checked' : '' ?>>
                                <span>Mahasiswa Beasiswa</span>
                            </label>
                        </div>
                    </div>

                    <!-- UKT Dasar -->
                    <div class="form-group">
                        <label class="form-label" for="uktInput">
                            Biaya UKT Dasar (Rp) <span class="required">*</span>
                        </label>
                        <input type="number" id="uktInput" name="ukt" class="form-control" min="0" step="100000"
                            value="<?= aman($formData['ukt']) ?>" required>
                        <small class="input-hint">Validasi: UKT &ge; 0 (tidak boleh negatif).</small>
                    </div>

                    <!-- Potongan Beasiswa (Khusus Beasiswa) -->
                    <div class="form-group" id="potonganGroup"
                        style="display: <?= ($formData['jenis'] === 'beasiswa') ? 'block' : 'none' ?>;">
                        <label class="form-label" for="potonganInput">
                            Potongan Beasiswa (%) <span class="required">*</span>
                        </label>
                        <input type="number" id="potonganInput" name="potongan" class="form-control" min="0" max="100"
                            value="<?= aman($formData['potongan']) ?>">
                        <small class="input-hint">Validasi: Potongan harus antara 0% s/d 100%.</small>
                    </div>

                    <!-- Realtime Calculation Preview -->
                    <div class="calc-box">
                        <div class="calc-row">
                            <span>UKT Dasar:</span>
                            <span id="previewUkt">Rp 0</span>
                        </div>
                        <div class="calc-row" id="previewPotonganRow">
                            <span>Diskon Beasiswa:</span>
                            <span id="previewPotongan" style="color: var(--accent-gold);">- Rp 0 (0%)</span>
                        </div>
                        <div class="calc-total">
                            <span>Estimasi Biaya Akhir:</span>
                            <span id="previewTotal">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <?= $isEditMode ? 'Simpan Perubahan Data' : 'Tambah Mahasiswa' ?>
                    </button>

                    <?php if ($isEditMode): ?>
                    <a href="index.php" class="btn-cancel">&times; Batalkan Mode Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Table Mahasiswa -->
            <div class="table-card">
                <div class="table-toolbar">
                    <div class="table-title">Daftar Mahasiswa Terdaftar (Session)</div>
                    <div class="search-box-wrap">
                        <span class="search-icon">&#128269;</span>
                        <input type="text" id="searchInput" class="search-input"
                            placeholder="Cari NIM, nama, prodi, atau jenis..." autocomplete="off">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="mahasiswaTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama Lengkap</th>
                                <th>Program Studi</th>
                                <th style="text-align: center;">Sem</th>
                                <th>Jenis</th>
                                <th>UKT Dasar</th>
                                <th>Potongan</th>
                                <th>Biaya Akhir</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftarMahasiswa)): ?>
                            <tr id="noDataRow">
                                <td colspan="9" class="empty-row">Belum ada data mahasiswa dalam session. Silakan klik
                                    tombol Reset Data Sampel di atas.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($daftarMahasiswa as $m): ?>
                            <?php 
                                        $isBeasiswa = ($m instanceof MahasiswaBeasiswa);
                                        $potonganPersen = ($m instanceof MahasiswaBeasiswa) ? $m->getPotonganPersen() : 0;
                                    ?>
                            <tr class="mhs-row"
                                data-search="<?= aman(mb_strtolower($m->getNim() . ' ' . $m->getNama() . ' ' . $m->getProdi() . ' ' . $m->getJenis())) ?>">
                                <td style="font-family: 'Consolas', monospace; font-weight: 600; color: #ffffff;">
                                    <?= aman($m->getNim()) ?>
                                </td>
                                <td style="font-weight: 600; color: #ffffff;">
                                    <?= aman($m->getNama()) ?>
                                </td>
                                <td><?= aman($m->getProdi()) ?></td>
                                <td style="text-align: center;"><?= aman($m->getSemester()) ?></td>
                                <td>
                                    <span class="badge-jenis <?= $isBeasiswa ? 'badge-beasiswa' : 'badge-reguler' ?>">
                                        <?= aman($m->getJenis()) ?>
                                    </span>
                                </td>
                                <td><?= formatRp($m->getUkt()) ?></td>
                                <td>
                                    <?php if ($isBeasiswa): ?>
                                    <span style="color: var(--accent-gold); font-weight: 600;">
                                        <?= $potonganPersen ?>%
                                    </span>
                                    <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="biaya-akhir">
                                    <?= formatRp($m->hitungBiaya()) ?>
                                </td>
                                <td>
                                    <div class="actions-group" style="justify-content: center;">
                                        <a href="index.php?aksi=edit&nim=<?= urlencode($m->getNim()) ?>"
                                            class="btn-action btn-edit" title="Edit Mahasiswa">
                                            Ubah
                                        </a>
                                        <form method="POST" action="index.php" style="display: inline;"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data mahasiswa <?= aman($m->getNama()) ?> (NIM: <?= aman($m->getNim()) ?>)?');">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
                                            <button type="submit" class="btn-action btn-delete" title="Hapus Mahasiswa">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Modul Pengujian & Penjelasan Skenario Modul P6 -->
                <div class="scenarios-box">
                    <h3>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M9 11l3 3L22 4" />
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                        </svg>
                        Pemenuhan 12 Skenario Pengujian Modul Praktikum 06 (Tabel J)
                    </h3>
                    <div class="scenarios-grid">
                        <div class="scenario-item"><span class="scenario-num">#01</span> Reguler UKT 5.000.000 &rarr;
                            Biaya 5.000.000</div>
                        <div class="scenario-item"><span class="scenario-num">#02</span> Beasiswa UKT 5.000.000, 60%
                            &rarr; Biaya 2.000.000</div>
                        <div class="scenario-item"><span class="scenario-num">#03</span> NIM Kosong &rarr; Ditolak
                            Parent Constructor</div>
                        <div class="scenario-item"><span class="scenario-num">#04</span> Semester 0 / 15 &rarr; Ditolak
                            Parent Constructor</div>
                        <div class="scenario-item"><span class="scenario-num">#05</span> UKT -100 &rarr; Ditolak Child
                            Constructor</div>
                        <div class="scenario-item"><span class="scenario-num">#06</span> Potongan 120% &rarr; Ditolak
                            Child Constructor</div>
                        <div class="scenario-item"><span class="scenario-num">#07</span> NIM Duplikat &rarr; Ditolak
                            Repository ($repo->tambah)</div>
                        <div class="scenario-item"><span class="scenario-num">#08</span> Edit Reguler &rarr; Beasiswa
                            pada NIM sama &rarr; Berhasil</div>
                        <div class="scenario-item"><span class="scenario-num">#09</span> Edit dengan NIM Berbeda &rarr;
                            Ditolak Repository</div>
                        <div class="scenario-item"><span class="scenario-num">#10</span> Hapus Record &rarr; Record lain
                            tetap utuh & aman</div>
                        <div class="scenario-item"><span class="scenario-num">#11</span> Refresh Browser &rarr; Data
                            Session tetap tersimpan</div>
                        <div class="scenario-item"><span class="scenario-num">#12</span> Akses Langsung $m->nim &rarr;
                            Ditolak karena private</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            Praktikum Pemrograman Berorientasi Objek &bull; Pertemuan 06: Inheritance &bull; Universitas Muhammadiyah
            Sukabumi
        </div>
    </div>

    <!-- Client-side Interaction JavaScript -->
    <script>
    // 1. Radio Toggle untuk Jenis Mahasiswa & Input Potongan
    const radioReguler = document.querySelector('input[value="reguler"]');
    const radioBeasiswa = document.querySelector('input[value="beasiswa"]');
    const labelReguler = document.getElementById('labelReguler');
    const labelBeasiswa = document.getElementById('labelBeasiswa');
    const potonganGroup = document.getElementById('potonganGroup');
    const uktInput = document.getElementById('uktInput');
    const potonganInput = document.getElementById('potonganInput');

    const previewUkt = document.getElementById('previewUkt');
    const previewPotongan = document.getElementById('previewPotongan');
    const previewPotonganRow = document.getElementById('previewPotonganRow');
    const previewTotal = document.getElementById('previewTotal');

    function formatRupiahJs(angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }

    function updateFormState() {
        const isBeasiswa = radioBeasiswa.checked;

        if (isBeasiswa) {
            labelBeasiswa.classList.add('active-beasiswa');
            labelReguler.classList.remove('active-reguler');
            potonganGroup.style.display = 'block';
            previewPotonganRow.style.display = 'flex';
        } else {
            labelReguler.classList.add('active-reguler');
            labelBeasiswa.classList.remove('active-beasiswa');
            potonganGroup.style.display = 'none';
            previewPotonganRow.style.display = 'none';
        }

        // Hitung Biaya Preview
        const ukt = parseInt(uktInput.value) || 0;
        const potongan = isBeasiswa ? (parseInt(potonganInput.value) || 0) : 0;
        const diskonRupiah = Math.floor((ukt * potongan) / 100);
        const totalAkhir = Math.max(0, ukt - diskonRupiah);

        previewUkt.textContent = formatRupiahJs(ukt);
        previewPotongan.textContent = `- ${formatRupiahJs(diskonRupiah)} (${potongan}%)`;
        previewTotal.textContent = formatRupiahJs(totalAkhir);
    }

    radioReguler?.addEventListener('change', updateFormState);
    radioBeasiswa?.addEventListener('change', updateFormState);
    uktInput?.addEventListener('input', updateFormState);
    potonganInput?.addEventListener('input', updateFormState);

    // Jalankan inisialisasi awal saat load
    updateFormState();

    // 2. Realtime Table Search Filter
    const searchInput = document.getElementById('searchInput');
    searchInput?.addEventListener('input', function() {
        const keyword = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.mhs-row');

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            if (searchData.includes(keyword)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    </script>
</body>

</html>