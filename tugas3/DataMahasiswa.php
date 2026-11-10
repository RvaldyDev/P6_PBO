<?php
declare(strict_types=1);

require_once __DIR__ . '/Mahasiswa.php';
require_once __DIR__ . '/MahasiswaReguler.php';
require_once __DIR__ . '/MahasiswaBeasiswa.php';


class DataMahasiswa {
    public const SESSION_KEY = 'mahasiswa_p6_repo';

    /**
     * Koleksi objek mahasiswa terindeks berdasarkan NIM
     * @var array<string, Mahasiswa>
     */
    private array $data = [];

    /**
     * Constructor DataMahasiswa
     * Menginisialisasi session dan mengisi data awal demonstrasi jika masih kosong
     */
    public function __construct(?array $awal = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($awal !== null) {
            foreach ($awal as $m) {
                if ($m instanceof Mahasiswa) {
                    $this->data[$m->getNim()] = $m;
                }
            }
        } elseif (isset($_SESSION[self::SESSION_KEY]) && is_array($_SESSION[self::SESSION_KEY])) {
            foreach ($_SESSION[self::SESSION_KEY] as $item) {
                if ($item instanceof Mahasiswa) {
                    $this->data[$item->getNim()] = $item;
                }
            }
        } else {
            $this->seedInitialData();
        }
    }

    /**
     * Mengisi data awal demonstrasi dengan variasi Reguler dan Beasiswa
     */
    public function seedInitialData(): void {
        $this->data = [];

        $m1 = new MahasiswaReguler(
            '2530521070',
            'Sanziya Rivaldi',
            'Teknik Informatika',
            4,
            5000000
        );

        $m2 = new MahasiswaBeasiswa(
            '2310511002',
            'Alya Putri Maharani',
            'Teknik Informatika',
            4,
            5000000,
            60 // Diskon 60% -> Biaya akhir 2.000.000
        );

        $m3 = new MahasiswaReguler(
            '2310511015',
            'David Kristanto',
            'Sistem Informasi',
            6,
            4800000
        );

        $m4 = new MahasiswaBeasiswa(
            '2310511048',
            'Nadia Safitri',
            'Teknik Sipil',
            2,
            5500000,
            40 // Diskon 40% -> Biaya akhir 3.300.000
        );

        $this->data[$m1->getNim()] = $m1;
        $this->data[$m2->getNim()] = $m2;
        $this->data[$m3->getNim()] = $m3;
        $this->data[$m4->getNim()] = $m4;

        $this->simpanKeSession();
    }

    /**
     * Sinkronisasi data lokal ke $_SESSION
     */
    private function simpanKeSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[self::SESSION_KEY] = $this->data;
    }

    /**
     * Menambahkan objek mahasiswa baru
     * Mengembalikan false jika NIM sudah terdaftar (Aturan NIM Unik)
     */
    public function tambah(Mahasiswa $m): bool {
        $nim = $m->getNim();
        if (isset($this->data[$nim])) {
            return false;
        }

        $this->data[$nim] = $m;
        $this->simpanKeSession();
        return true;
    }

    /**
     * Mencari objek mahasiswa berdasarkan NIM
     */
    public function cari(string $nim): ?Mahasiswa {
        $nimClean = trim($nim);
        return $this->data[$nimClean] ?? null;
    }

    /**
     * Mengambil seluruh koleksi objek mahasiswa
     * @return array<string, Mahasiswa>
     */
    public function semua(): array {
        return $this->data;
    }

    /**
     * Memperbarui data mahasiswa.
     * Sesuai ketentuan praktikum:
     * - Edit diperbolehkan mengubah jenis (Reguler <-> Beasiswa)
     * - Edit TIDAK boleh mengubah NIM
     * - Mengganti jenis dilakukan dengan membuat objek child baru pada NIM yang sama
     */
    public function ubah(string $nimLama, Mahasiswa $m): bool {
        $nimLamaClean = trim($nimLama);

        // Jika data lama tidak ada, atau NIM objek baru tidak sama dengan NIM lama -> Tolak
        if (!isset($this->data[$nimLamaClean]) || $m->getNim() !== $nimLamaClean) {
            return false;
        }

        $this->data[$nimLamaClean] = $m;
        $this->simpanKeSession();
        return true;
    }

    /**
     * Menghapus objek mahasiswa berdasarkan NIM
     */
    public function hapus(string $nim): bool {
        $nimClean = trim($nim);
        if (!isset($this->data[$nimClean])) {
            return false;
        }

        unset($this->data[$nimClean]);
        $this->simpanKeSession();
        return true;
    }

    /**
     * Mereset seluruh repository ke data awal demonstrasi
     */
    public function resetData(): void {
        $this->seedInitialData();
    }

    /**
     * Fitur pencarian server-side berdasarkan kata kunci pada NIM, Nama, atau Prodi
     * @return array<string, Mahasiswa>
     */
    public function cariBerdasarkanKeyword(string $keyword): array {
        $keyword = mb_strtolower(trim($keyword));
        if ($keyword === '') {
            return $this->data;
        }

        $hasil = [];
        foreach ($this->data as $nim => $m) {
            $nimCocok   = str_contains(mb_strtolower($m->getNim()), $keyword);
            $namaCocok  = str_contains(mb_strtolower($m->getNama()), $keyword);
            $prodiCocok = str_contains(mb_strtolower($m->getProdi()), $keyword);
            $jenisCocok = str_contains(mb_strtolower($m->getJenis()), $keyword);

            if ($nimCocok || $namaCocok || $prodiCocok || $jenisCocok) {
                $hasil[$nim] = $m;
            }
        }

        return $hasil;
    }
}