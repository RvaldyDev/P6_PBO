<?php
declare(strict_types=1);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas 1 - Diagram Class &amp; Penjelasan Konsep Inheritance</title>
    <style>
    :root {
        --bg-color: #0b0d10;
        --panel-bg: #14171d;
        --card-bg: #181c24;
        --accent-cyan: #00f2fe;
        --accent-blue: #4facfe;
        --accent-green: #00e676;
        --accent-purple: #a855f7;
        --accent-gold: #ffb703;
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
        padding: 2.5rem 1.25rem;
        min-height: 100vh;
        line-height: 1.6;
    }

    .container {
        max-width: 980px;
        margin: 0 auto;
    }

    .nav-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--accent-cyan);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 1.75rem;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .nav-back:hover {
        transform: translateX(-4px);
        color: #ffffff;
    }

    .header {
        background: linear-gradient(135deg, rgba(20, 23, 29, 0.95), rgba(24, 28, 36, 0.95));
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 2.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .header h1 {
        font-size: 1.95rem;
        margin-bottom: 0.6rem;
        background: linear-gradient(135deg, #ffffff, #c084fc);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }

    .badge-tugas {
        display: inline-block;
        background: rgba(168, 85, 247, 0.15);
        color: var(--accent-purple);
        border: 1px solid rgba(168, 85, 247, 0.3);
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-bottom: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .author-info {
        display: flex;
        gap: 1.5rem;
        margin-top: 1rem;
        font-size: 0.88rem;
        color: #94a3b8;
        flex-wrap: wrap;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 1rem;
    }

    .section-card {
        background-color: var(--panel-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2.25rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    .section-title {
        font-size: 1.3rem;
        color: #ffffff;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding-bottom: 0.75rem;
    }

    /* Diagram Image Container */
    .diagram-wrapper {
        background: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 1.75rem;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 1.25rem 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        overflow-x: auto;
    }

    .diagram-img {
        max-width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
    }

    .diagram-caption {
        font-size: 0.88rem;
        color: var(--text-muted);
        text-align: center;
        margin-top: 0.5rem;
    }

    /* Grid Explanation Cards */
    .grid-concepts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-top: 1.5rem;
    }

    .concept-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.35rem;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .concept-card:hover {
        transform: translateY(-3px);
        border-color: var(--accent-cyan);
    }

    .concept-card h3 {
        font-size: 1.05rem;
        color: var(--accent-cyan);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .concept-card p {
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.55;
    }

    /* Table Component */
    .table-compare {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
        margin-top: 1.25rem;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        overflow: hidden;
    }

    .table-compare th,
    .table-compare td {
        padding: 0.9rem 1.15rem;
        border: 1px solid var(--border-color);
        text-align: left;
    }

    .table-compare th {
        background: rgba(0, 0, 0, 0.45);
        color: #cbd5e1;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-compare tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }

    .code-inline {
        background: rgba(255, 255, 255, 0.08);
        padding: 2px 7px;
        border-radius: 4px;
        font-family: 'Consolas', monospace;
        font-size: 0.86rem;
        color: #38bdf8;
    }

    .footer {
        text-align: center;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 2rem;
        padding-bottom: 1rem;
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="nav-back">&larr; Kembali ke Portal Praktikum 06</a>

        <div class="header">
            <span class="badge-tugas">Tugas 1 (Individu)</span>
            <h1>Diagram Class &amp; Penjelasan Konsep Inheritance</h1>
            <p>Pemodelan hierarki objek <code>Mahasiswa</code>, implementasi pewarisan (<em>is-a</em>), prinsip
                enkapsulasi, serta evaluasi konsep arsitektural OOP.</p>
            <div class="author-info">
                <span><strong>Mahasiswa:</strong> Sanziya Rivaldi</span>
                <span>&bull;</span>
                <span><strong>NIM:</strong> 2530521070</span>
                <span>&bull;</span>
                <span><strong>Kelas:</strong> Teknik Informatika</span>
                <span>&bull;</span>
                <span><strong>Dosen:</strong> Prajoko, M.Kom.</span>
            </div>
        </div>

        <!-- 1. Visualisasi Diagram Class -->
        <div class="section-card">
            <h2 class="section-title">&#128394; 1. Diagram Hierarki Class (UML)</h2>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 0.75rem;">
                Visualisasi class diagram relasi pewarisan (*inheritance*) antara Parent Class <code>Mahasiswa</code>
                dengan dua Child Class: <code>Mahasiswa_Reguler</code> dan <code>Mahasiswa_Beasiswa</code>:
            </p>

            <div class="diagram-wrapper">
                <img src="diagram.png" alt="UML Class Diagram Mahasiswa, Mahasiswa_Reguler, Mahasiswa_Beasiswa"
                    class="diagram-img">
            </div>

            <div class="diagram-caption">
                <em>Gambar 1: UML Class Diagram Hierarki Pewarisan Class Mahasiswa (Notasi Simbol: <span
                        class="code-inline">-</span> Private, <span class="code-inline">+</span> Public, &Delta;
                    Inheritance Relasi is-a)</em>
            </div>

            <!-- Tabel Dekomposisi Komponen Diagram -->
            <table class="table-compare">
                <thead>
                    <tr>
                        <th>Kelas / Komponen</th>
                        <th>Peran (Role)</th>
                        <th>Atribut Enkapsulasi</th>
                        <th>Method &amp; Perilaku</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Mahasiswa</strong></td>
                        <td><span style="color: var(--accent-purple); font-weight: 600;">Parent (Superclass)</span></td>
                        <td>
                            <code>- nim : String</code><br>
                            <code>- nama : String</code><br>
                            <code>- prodi : String</code><br>
                            <code>- semester : Integer</code>
                        </td>
                        <td>
                            <code>+ __construct() : void</code><br>
                            <code>+ getNim() : String</code><br>
                            <code>+ getNama() : String</code><br>
                            <code>+ getProdi() : String</code><br>
                            <code>+ getSemester() : Integer</code><br>
                            <code>+ getJenis() : String</code><br>
                            <code>+ hitungBiaya() : Integer</code>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Mahasiswa_Reguler</strong></td>
                        <td><span style="color: var(--accent-green); font-weight: 600;">Child (Subclass 1)</span></td>
                        <td>
                            <code>- ukt : Integer</code>
                        </td>
                        <td>
                            <code>+ __construct() : void</code><br>
                            <code>+ getJenis() : String</code> (Overriding)<br>
                            <code>+ hitungBiaya() : Integer</code> (Overriding)
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Mahasiswa_Beasiswa</strong></td>
                        <td><span style="color: var(--accent-cyan); font-weight: 600;">Child (Subclass 2)</span></td>
                        <td>
                            <code>- ukt : Integer</code><br>
                            <code>- potongan : Integer</code>
                        </td>
                        <td>
                            <code>+ __construct() : void</code><br>
                            <code>+ getJenis() : String</code> (Overriding)<br>
                            <code>+ hitungBiaya() : Integer</code> (Overriding)
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 2. Penjelasan Konsep Inheritance -->
        <div class="section-card">
            <h2 class="section-title">&#128161; 2. Penjelasan Konsep Inheritance (Pewarisan)</h2>
            <p style="color: var(--text-muted); font-size: 0.92rem;">
                Berdasarkan diagram hierarki di atas, terdapat beberapa konsep fundamental *Object-Oriented Programming*
                (OOP) yang diterapkan:
            </p>

            <div class="grid-concepts">
                <div class="concept-card">
                    <h3>&#128279; Hubungan Relasi "is-a"</h3>
                    <p>
                        Pewarisan memodelkan hubungan semantik <em>is-a</em> (adalah seorang/sebuah).
                        <code>Mahasiswa_Reguler</code> adalah seorang <code>Mahasiswa</code>, dan
                        <code>Mahasiswa_Beasiswa</code> juga adalah seorang <code>Mahasiswa</code>. Keduanya mewarisi
                        seluruh karakteristik dasar mahasiswa.
                    </p>
                </div>

                <div class="concept-card">
                    <h3>&#9851; Prinsip DRY &amp; Enkapsulasi</h3>
                    <p>
                        Atribut identitas umum (<code>nim</code>, <code>nama</code>, <code>prodi</code>,
                        <code>semester</code>) hanya didefinisikan satu kali di parent class dengan visibilitas
                        <code>private (-)</code>. Hal ini mengeliminasi duplikasi kode dan melindungi integritas data
                        dari modifikasi sembarangan di luar class.
                    </p>
                </div>

                <div class="concept-card">
                    <h3>&#9881; Konstruktor Berantai</h3>
                    <p>
                        Child class memanfaatkan mekanisme <code>parent::__construct()</code> untuk mendelegasikan
                        inisialisasi identitas umum ke parent, sebelum menginisialisasi atribut spesifiknya
                        masing-masing (seperti nilai <code>ukt</code> atau <code>potongan</code>).
                    </p>
                </div>

                <div class="concept-card">
                    <h3>&#128031; Method Overriding &amp; Polimorfisme</h3>
                    <p>
                        Method <code>hitungBiaya()</code> dan <code>getJenis()</code> di-override oleh setiap child
                        class sesuai aturan bisnis uniknya: Reguler membayar biaya UKT penuh, sedangkan Beasiswa
                        memperhitungkan diskon potongan beasiswa.
                    </p>
                </div>
            </div>
        </div>

        <div class="footer">
            PBO Praktikum 6 &bull; Teknik Informatika &bull; Universitas Muhammadiyah Sukabumi
        </div>
    </div>
</body>

</html>