<?php
declare(strict_types=1);


require_once __DIR__ . '/tugas3/DataMahasiswa.php';

$hasilPengujian = [];

// =============================================================================
// Skenario 01: Tambah Reguler UKT 5.000.000 -> Biaya akhir 5.000.000
// =============================================================================
try {
    $m = new MahasiswaReguler('TEST01', 'Budi Santoso', 'Informatika', 4, 5000000);
    $biaya = $m->hitungBiaya();
    $lulus = ($biaya === 5000000 && $m->getJenis() === 'Reguler');
    $hasilPengujian[] = [
        'no'         => '01',
        'skenario'   => 'Tambah Reguler UKT 5.000.000',
        'ekspektasi' => 'Biaya akhir 5.000.000',
        'realita'    => 'Biaya: ' . number_format($biaya, 0, ',', '.') . ' (' . $m->getJenis() . ')',
        'status'     => $lulus ? 'SESUAI' : 'GAGAL'
    ];
} catch (Throwable $e) {
    $hasilPengujian[] = [
        'no' => '01', 'skenario' => 'Tambah Reguler UKT 5.000.000',
        'ekspektasi' => 'Biaya akhir 5.000.000', 'realita' => 'Error: ' . $e->getMessage(), 'status' => 'GAGAL'
    ];
}

// =============================================================================
// Skenario 02: Tambah Beasiswa UKT 5.000.000; potongan 60% -> Biaya akhir 2.000.000
// =============================================================================
try {
    $m = new MahasiswaBeasiswa('TEST02', 'Siti Aminah', 'Informatika', 4, 5000000, 60);
    $biaya = $m->hitungBiaya();
    $lulus = ($biaya === 2000000 && $m->getJenis() === 'Beasiswa');
    $hasilPengujian[] = [
        'no'         => '02',
        'skenario'   => 'Tambah Beasiswa UKT 5.000.000, potongan 60%',
        'ekspektasi' => 'Biaya akhir 2.000.000',
        'realita'    => 'Biaya: ' . number_format($biaya, 0, ',', '.') . ' (' . $m->getJenis() . ')',
        'status'     => $lulus ? 'SESUAI' : 'GAGAL'
    ];
} catch (Throwable $e) {
    $hasilPengujian[] = [
        'no' => '02', 'skenario' => 'Tambah Beasiswa UKT 5.000.000, potongan 60%',
        'ekspektasi' => 'Biaya akhir 2.000.000', 'realita' => 'Error: ' . $e->getMessage(), 'status' => 'GAGAL'
    ];
}

// =============================================================================
// Skenario 03: NIM kosong -> Ditolak parent constructor
// =============================================================================
try {
    new MahasiswaReguler('   ', 'Nama Valid', 'Informatika', 2, 5000000);
    $hasilPengujian[] = [
        'no' => '03', 'skenario' => 'NIM kosong',
        'ekspektasi' => 'Ditolak parent constructor', 'realita' => 'Lolos tanpa exception', 'status' => 'GAGAL'
    ];
} catch (InvalidArgumentException $e) {
    $hasilPengujian[] = [
        'no' => '03', 'skenario' => 'NIM kosong',
        'ekspektasi' => 'Ditolak parent constructor', 'realita' => 'Ditolak: ' . $e->getMessage(), 'status' => 'SESUAI'
    ];
}

// =============================================================================
// Skenario 04: Semester 0 atau 15 -> Ditolak parent constructor
// =============================================================================
$ditolakSem0 = false;
$ditolakSem15 = false;
try {
    new MahasiswaReguler('TEST04A', 'Nama Valid', 'Informatika', 0, 5000000);
} catch (InvalidArgumentException) {
    $ditolakSem0 = true;
}

try {
    new MahasiswaReguler('TEST04B', 'Nama Valid', 'Informatika', 15, 5000000);
} catch (InvalidArgumentException) {
    $ditolakSem15 = true;
}

$hasilPengujian[] = [
    'no'         => '04',
    'skenario'   => 'Semester 0 atau 15',
    'ekspektasi' => 'Ditolak parent constructor',
    'realita'    => ($ditolakSem0 && $ditolakSem15) ? 'Keduanya ditolak dengan benar' : 'Gagal memvalidasi',
    'status'     => ($ditolakSem0 && $ditolakSem15) ? 'SESUAI' : 'GAGAL'
];

// =============================================================================
// Skenario 05: UKT -100 -> Ditolak child constructor
// =============================================================================
try {
    new MahasiswaReguler('TEST05', 'Nama Valid', 'Informatika', 3, -100);
    $hasilPengujian[] = [
        'no' => '05', 'skenario' => 'UKT -100',
        'ekspektasi' => 'Ditolak child constructor', 'realita' => 'Lolos tanpa exception', 'status' => 'GAGAL'
    ];
} catch (InvalidArgumentException $e) {
    $hasilPengujian[] = [
        'no' => '05', 'skenario' => 'UKT -100',
        'ekspektasi' => 'Ditolak child constructor', 'realita' => 'Ditolak: ' . $e->getMessage(), 'status' => 'SESUAI'
    ];
}

// =============================================================================
// Skenario 06: Potongan 120% -> Ditolak child constructor
// =============================================================================
try {
    new MahasiswaBeasiswa('TEST06', 'Nama Valid', 'Informatika', 3, 5000000, 120);
    $hasilPengujian[] = [
        'no' => '06', 'skenario' => 'Potongan 120%',
        'ekspektasi' => 'Ditolak child constructor', 'realita' => 'Lolos tanpa exception', 'status' => 'GAGAL'
    ];
} catch (InvalidArgumentException $e) {
    $hasilPengujian[] = [
        'no' => '06', 'skenario' => 'Potongan 120%',
        'ekspektasi' => 'Ditolak child constructor', 'realita' => 'Ditolak: ' . $e->getMessage(), 'status' => 'SESUAI'
    ];
}

// =============================================================================
// Skenario 07: NIM duplikat -> Ditolak repository
// =============================================================================
$repoTest = new DataMahasiswa([]);
$mOriginal = new MahasiswaReguler('TEST07', 'Mahasiswa Asli', 'Informatika', 2, 4000000);
$repoTest->tambah($mOriginal);

$mDuplikat = new MahasiswaBeasiswa('TEST07', 'Mahasiswa Pengganda', 'Informatika', 2, 4000000, 50);
$hasilTambahDuplikat = $repoTest->tambah($mDuplikat);

$hasilPengujian[] = [
    'no'         => '07',
    'skenario'   => 'NIM duplikat',
    'ekspektasi' => 'Ditolak repository ($repo->tambah == false)',
    'realita'    => ($hasilTambahDuplikat === false) ? 'Ditolak repository (return false)' : 'Diterima repository (salah)',
    'status'     => ($hasilTambahDuplikat === false) ? 'SESUAI' : 'GAGAL'
];

// =============================================================================
// Skenario 08: Edit Reguler menjadi Beasiswa pada NIM sama -> Jenis berubah; data tetap satu record
// =============================================================================
$repoTest8 = new DataMahasiswa([]);
$mReguler = new MahasiswaReguler('TEST08', 'Santika', 'Informatika', 3, 5000000);
$repoTest8->tambah($mReguler);

// Ubah menjadi MahasiswaBeasiswa dengan NIM sama
$mBeasiswa = new MahasiswaBeasiswa('TEST08', 'Santika Beasiswa', 'Informatika', 3, 5000000, 50);
$hasilUbah = $repoTest8->ubah('TEST08', $mBeasiswa);
$dataSetelahUbah = $repoTest8->cari('TEST08');
$totalRecord = count($repoTest8->semua());

$lulus8 = ($hasilUbah === true && $dataSetelahUbah instanceof MahasiswaBeasiswa && $totalRecord === 1);

$hasilPengujian[] = [
    'no'         => '08',
    'skenario'   => 'Edit Reguler menjadi Beasiswa pada NIM sama',
    'ekspektasi' => 'Jenis berubah; data tetap satu record',
    'realita'    => "Jenis: {$dataSetelahUbah->getJenis()}, Total Record: {$totalRecord}",
    'status'     => $lulus8 ? 'SESUAI' : 'GAGAL'
];

// =============================================================================
// Skenario 09: Edit dengan NIM berbeda -> Ditolak repository
// =============================================================================
$repoTest9 = new DataMahasiswa([]);
$mExisting = new MahasiswaReguler('TEST09_OLD', 'Target', 'Informatika', 3, 5000000);
$repoTest9->tambah($mExisting);

// Coba edit tapi objek yang dikirim membawa NIM baru
$mBedaNim = new MahasiswaReguler('TEST09_NEW', 'Target', 'Informatika', 3, 5000000);
$hasilUbahBeda = $repoTest9->ubah('TEST09_OLD', $mBedaNim);

$hasilPengujian[] = [
    'no'         => '09',
    'skenario'   => 'Edit dengan NIM berbeda',
    'ekspektasi' => 'Ditolak repository ($repo->ubah == false)',
    'realita'    => ($hasilUbahBeda === false) ? 'Ditolak repository (return false)' : 'Diterima repository (salah)',
    'status'     => ($hasilUbahBeda === false) ? 'SESUAI' : 'GAGAL'
];

// =============================================================================
// Skenario 10: Hapus satu record -> Record lain tetap tersedia
// =============================================================================
$repoTest10 = new DataMahasiswa([]);
$mA = new MahasiswaReguler('REC_A', 'Mahasiswa A', 'Informatika', 2, 4000000);
$mB = new MahasiswaReguler('REC_B', 'Mahasiswa B', 'Informatika', 2, 4000000);
$repoTest10->tambah($mA);
$repoTest10->tambah($mB);

$repoTest10->hapus('REC_A');
$recATersedia = ($repoTest10->cari('REC_A') !== null);
$recBTersedia = ($repoTest10->cari('REC_B') !== null);

$lulus10 = (!$recATersedia && $recBTersedia && count($repoTest10->semua()) === 1);
$hasilPengujian[] = [
    'no'         => '10',
    'skenario'   => 'Hapus satu record',
    'ekspektasi' => 'Record lain tetap tersedia',
    'realita'    => 'REC_A terhapus, REC_B tetap tersedia di repository',
    'status'     => $lulus10 ? 'SESUAI' : 'GAGAL'
];

// =============================================================================
// Skenario 11: Refresh browser selama Session aktif -> Data tetap ada
// =============================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$repoTest11 = new DataMahasiswa();
$dataSessionAwal = $repoTest11->semua();

// Simulasi reload repo dari session yang sama
$repoTest11Reload = new DataMahasiswa();
$dataSessionReload = $repoTest11Reload->semua();

$lulus11 = (count($dataSessionAwal) > 0 && count($dataSessionAwal) === count($dataSessionReload));
$hasilPengujian[] = [
    'no'         => '11',
    'skenario'   => 'Refresh browser selama Session aktif',
    'ekspektasi' => 'Data tetap ada',
    'realita'    => 'Data tersimpan konsisten di $_SESSION[' . DataMahasiswa::SESSION_KEY . ']',
    'status'     => $lulus11 ? 'SESUAI' : 'GAGAL'
];

// =============================================================================
// Skenario 12: Akses $m->nim langsung -> Ditolak karena private
// =============================================================================
$ditolakPrivate = false;
$pesanErrorPrivate = '';

try {
    $ref = new ReflectionProperty(Mahasiswa::class, 'nim');
    $isPrivate = $ref->isPrivate();
    if ($isPrivate) {
        $ditolakPrivate = true;
        $pesanErrorPrivate = "Properti \$nim bertipe private (Cannot access private property Mahasiswa::\$nim)";
    }
} catch (Throwable $e) {
    $pesanErrorPrivate = $e->getMessage();
}

$hasilPengujian[] = [
    'no'         => '12',
    'skenario'   => 'Akses $m->nim langsung',
    'ekspektasi' => 'Ditolak karena private',
    'realita'    => $ditolakPrivate ? $pesanErrorPrivate : 'Properti tidak private',
    'status'     => $ditolakPrivate ? 'SESUAI' : 'GAGAL'
];

// =============================================================================
// OUTPUT DISPLAY (CLI / HTML)
// =============================================================================
if (php_sapi_name() === 'cli') {
    echo "========================================================================================================\n";
    echo "  TABEL HASIL PENGUJIAN 12 SKENARIO MODUL PRAKTIKUM 06 (TABEL J)\n";
    echo "========================================================================================================\n";
    printf("%-4s | %-32s | %-30s | %-8s\n", "No", "Skenario", "Hasil Pengujian", "Status");
    echo "--------------------------------------------------------------------------------------------------------\n";
    foreach ($hasilPengujian as $row) {
        printf("%-4s | %-32s | %-30s | %-8s\n", $row['no'], $row['skenario'], mb_substr($row['realita'], 0, 30), $row['status']);
    }
    echo "========================================================================================================\n";
    $semuaSesuai = array_reduce($hasilPengujian, fn($carry, $item) => $carry && ($item['status'] === 'SESUAI'), true);
    echo $semuaSesuai ? ">> SEMUA 12 SKENARIO TERPENUHI DENGAN STATUS SESUAI (100% SUKSES) <<\n\n" : ">> ADA SKENARIO GAGAL <<\n\n";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Skenario Modul 06 PBO (12 Skenario Lengkap)</title>
    <style>
    :root {
        --bg-color: #0b0d10;
        --panel-bg: #14171d;
        --card-bg: #181c24;
        --accent-cyan: #00f2fe;
        --accent-green: #00e676;
        --accent-red: #ff5252;
        --text-main: #e2e8f0;
        --text-muted: #8a99ad;
        --border-color: #232936;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-main);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 2rem 1rem;
        min-height: 100vh;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .nav-back {
        display: inline-flex;
        gap: 0.5rem;
        color: var(--accent-cyan);
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .card {
        background: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    h1 {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #00f2fe, #4facfe);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    p {
        color: var(--text-muted);
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    th {
        background: rgba(0, 0, 0, 0.3);
        padding: 0.85rem 1rem;
        text-align: left;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border-color);
    }

    td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .badge-sesuai {
        background: rgba(0, 230, 118, 0.15);
        color: var(--accent-green);
        border: 1px solid rgba(0, 230, 118, 0.3);
    }

    .badge-gagal {
        background: rgba(255, 82, 82, 0.15);
        color: var(--accent-red);
        border: 1px solid rgba(255, 82, 82, 0.3);
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="nav-back">&larr; Kembali ke Portal Praktikum 06</a>

        <div class="card">
            <h1>Hasil Pengujian 12 Skenario Modul 06 (Tabel J)</h1>
            <p>Pengujian kepatuhan logika inheritance, validasi constructor, exception handling, dan aturan bisnis
                repository CRUD Mahasiswa.</p>

            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Skenario Pengujian</th>
                        <th>Hasil yang Diharapkan</th>
                        <th>Realita Keluaran</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hasilPengujian as $row): ?>
                    <tr>
                        <td style="font-weight: 700; color: var(--accent-cyan);"><?= htmlspecialchars($row['no']) ?>
                        </td>
                        <td style="font-weight: 600; color: #ffffff;"><?= htmlspecialchars($row['skenario']) ?></td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($row['ekspektasi']) ?></td>
                        <td><?= htmlspecialchars($row['realita']) ?></td>
                        <td style="text-align: center;">
                            <span
                                class="badge-status <?= ($row['status'] === 'SESUAI') ? 'badge-sesuai' : 'badge-gagal' ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>