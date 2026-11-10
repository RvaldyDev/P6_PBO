<?php
declare(strict_types=1);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PBO Praktikum 6 - Inheritance (Pewarisan Class)</title>
    <style>
    :root {
        --bg-color: #0b0d10;
        --panel-bg: #14171d;
        --panel-card: #181c24;
        --accent-cyan: #00f2fe;
        --accent-blue: #4facfe;
        --accent-green: #00e676;
        --accent-gold: #ffb703;
        --accent-purple: #a855f7;
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
        margin: 0;
        padding: 2.5rem 1.5rem;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .container {
        max-width: 1040px;
        width: 100%;
    }

    .header-title {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .header-badge {
        display: inline-block;
        background: rgba(0, 242, 254, 0.12);
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 242, 254, 0.3);
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
    }

    .header-title h1 {
        font-size: 2.3rem;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
        margin-bottom: 0.5rem;
    }

    .header-title p {
        color: var(--text-muted);
        font-size: 1.05rem;
    }

    .student-info {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        margin-top: 0.75rem;
        font-size: 0.88rem;
        color: #94a3b8;
        flex-wrap: wrap;
    }

    .grid-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .task-card {
        background-color: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.75rem;
        text-decoration: none;
        color: var(--text-main);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .task-card:hover {
        transform: translateY(-4px);
        border-color: var(--accent-cyan);
        box-shadow: 0 14px 32px rgba(0, 242, 254, 0.18);
    }

    .task-badge {
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 4px;
        margin-bottom: 1rem;
        width: fit-content;
    }

    .badge-1 {
        background-color: rgba(168, 85, 247, 0.15);
        color: var(--accent-purple);
        border: 1px solid rgba(168, 85, 247, 0.3);
    }

    .badge-2 {
        background-color: rgba(0, 230, 118, 0.15);
        color: var(--accent-green);
        border: 1px solid rgba(0, 230, 118, 0.3);
    }

    .badge-3 {
        background: linear-gradient(135deg, rgba(0, 242, 254, 0.2), rgba(79, 172, 254, 0.2));
        color: var(--accent-cyan);
        border: 1px solid rgba(0, 242, 254, 0.4);
    }

    .badge-test {
        background-color: rgba(79, 172, 254, 0.15);
        color: var(--accent-blue);
        border: 1px solid rgba(79, 172, 254, 0.3);
    }

    .task-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        color: #ffffff;
    }

    .task-desc {
        font-size: 0.88rem;
        color: var(--text-muted);
        line-height: 1.55;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }

    .task-action {
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--accent-cyan);
        gap: 0.4rem;
    }

    .footer {
        margin-top: 3rem;
        text-align: center;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    </style>
</head>

<body>

    <div class="container">
        <div class="header-title">
            <span class="header-badge">Modul Praktikum 06 &bull; PBO</span>
            <h1>Praktikum 6: Pewarisan Class (Inheritance)</h1>
            <p>Penguasaan Konsep <em>is-a</em>, <code>extends</code>, <code>parent::__construct()</code>, &amp; Method
                Overriding</p>
            <div class="student-info">
                <span><strong>Nama:</strong> Sanziya Rivaldi</span>
                <span>&bull;</span>
                <span><strong>NIM:</strong> 2530521070</span>
                <span>&bull;</span>
                <span><strong>Kelas:</strong> Teknik Informatika</span>
            </div>
        </div>

        <div class="grid-cards">
            <!-- Tugas 1 -->
            <a href="tugas1.php" class="task-card">
                <div>
                    <span class="task-badge badge-1">Tugas 1 (Individu)</span>
                    <h2 class="task-title">Diagram Class &amp; Desain</h2>
                    <p class="task-desc">
                        Visualisasi hierarki class <code>Mahasiswa</code>, pemisahan tanggung jawab parent-child, dan
                        analisis komparasi visibilitas <code>private</code> vs <code>protected</code>.
                    </p>
                </div>
                <div class="task-action" style="color: var(--accent-purple);">
                    Buka Tugas 1 &rarr;
                </div>
            </a>

            <!-- Tugas 2 -->
            <a href="tugas2.php" class="task-card">
                <div>
                    <span class="task-badge badge-2">Tugas 2 (Individu)</span>
                    <h2 class="task-title">Inheritance Pegawai</h2>
                    <p class="task-desc">
                        Parent class <code>Pegawai</code> dengan child <code>PegawaiTetap</code> dan
                        <code>PegawaiKontrak</code>.
                        Implementasi rumus <code>hitungGaji()</code> berbeda, <code>parent::__construct()</code>, dan
                        validasi angka non-negatif.
                    </p>
                </div>
                <div class="task-action" style="color: var(--accent-green);">
                    Buka Tugas 2 &rarr;
                </div>
            </a>

            <!-- Tugas 3 -->
            <a href="tugas3/index.php" class="task-card" style="border-color: rgba(0, 242, 254, 0.4);">
                <div>
                    <span class="task-badge badge-3">Tugas 3 (Kelompok - Utama)</span>
                    <h2 class="task-title">CRUD Mahasiswa Inheritance</h2>
                    <p class="task-desc">
                        Dashboard CRUD Mahasiswa dengan <code>MahasiswaReguler</code> &amp;
                        <code>MahasiswaBeasiswa</code>.
                        Penyesuaian UKT &amp; diskon potongan, proteksi NIM immutable, serta manipulasi polymorphic
                        repository.
                    </p>
                </div>
                <div class="task-action" style="color: var(--accent-cyan);">
                    Buka Dashboard CRUD &rarr;
                </div>
            </a>

            <!-- Uji Skenario Modul P6 -->
            <a href="uji_skenario.php" class="task-card" style="grid-column: 1 / -1;">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <span class="task-badge badge-test">Skenario Modul P6 (Tabel J)</span>
                        <h2 class="task-title" style="margin-bottom: 0.35rem;">Pengujian Otomatis 12 Kasus Batas</h2>
                        <p class="task-desc" style="margin-bottom: 0;">
                            Menjalankan uji eksekusi otomatis 12 skenario pengujian dari modul praktikum (Tabel J) untuk
                            memastikan semua validasi dan logika berjalan 100% SESUAI.
                        </p>
                    </div>
                    <div class="task-action" style="color: var(--accent-blue); white-space: nowrap;">
                        Jalankan Pengujian &rarr;
                    </div>
                </div>
            </a>
        </div>

        <div class="footer">
            PBO Praktikum 6 &bull; PHP <?= PHP_VERSION ?> &bull; Universitas Muhammadiyah Sukabumi
        </div>
    </div>

</body>

</html>