<?php
declare(strict_types=1);

/**
 * Parent Class Abstract: Mahasiswa
 * Modul Praktikum 06: Inheritance (Pewarisan Class)
 * 
 * Karakteristik Arsitektur:
 * 1. Menjaga enkapsulasi state identitas umum mahasiswa (NIM, Nama, Prodi, Semester)
 * 2. Mencegah duplikasi kode di kelas-kelas anak (DRY Principle)
 * 3. Mendeklarasikan method abstract hitungBiaya() dan getJenis() sebagai kontrak polimorfis
 * 
 * @method int getPotonganPersen()
 */
abstract class Mahasiswa {
    // Properti private: Dilindungi secara ketat demi integritas enkapsulasi
    // Properti ini TIDAK BOLEH digandakan di child class
    private string $nim;
    private string $nama;
    private string $prodi;
    private int $semester;

    /**
     * Constructor Parent Mahasiswa
     * Memvalidasi seluruh atribut umum identitas mahasiswa sejak awal
     */
    public function __construct(string $nim, string $nama, string $prodi, int $semester) {
        $nimClean   = trim($nim);
        $namaClean  = trim($nama);
        $prodiClean = trim($prodi);

        // Validasi NIM wajib diisi
        if ($nimClean === '') {
            throw new InvalidArgumentException("NIM wajib diisi dan tidak boleh kosong.");
        }

        // Validasi Nama wajib diisi
        if ($namaClean === '') {
            throw new InvalidArgumentException("Nama mahasiswa wajib diisi dan tidak boleh kosong.");
        }

        // Validasi Prodi wajib diisi
        if ($prodiClean === '') {
            throw new InvalidArgumentException("Program studi wajib diisi dan tidak boleh kosong.");
        }

        // Validasi Semester harus 1 - 14 (Aturan Akademik)
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException("Semester tidak valid! Harus bernilai antara 1 sampai 14 (diberikan: {$semester}).");
        }

        $this->nim      = $nimClean;
        $this->nama     = $namaClean;
        $this->prodi    = $prodiClean;
        $this->semester = $semester;
    }

    // =========================================================================
    // GETTER (Method Akses Pembacaan Sah - Read-Only)
    // =========================================================================

    public function getNim(): string {
        return $this->nim;
    }

    public function getNama(): string {
        return $this->nama;
    }

    public function getProdi(): string {
        return $this->prodi;
    }

    public function getSemester(): int {
        return $this->semester;
    }

    // =========================================================================
    // SETTER (Method Pembaruan yang Diizinkan)
    // CATATAN: NIM TIDAK memiliki setter karena NIM bersifat IMMUTABLE (Permanen)
    // =========================================================================

    public function setNama(string $nama): void {
        $namaClean = trim($nama);
        if ($namaClean === '') {
            throw new InvalidArgumentException("Nama mahasiswa tidak boleh kosong.");
        }
        $this->nama = $namaClean;
    }

    public function setProdi(string $prodi): void {
        $prodiClean = trim($prodi);
        if ($prodiClean === '') {
            throw new InvalidArgumentException("Program studi tidak boleh kosong.");
        }
        $this->prodi = $prodiClean;
    }

    public function setSemester(int $semester): void {
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException("Semester tidak valid! Harus bernilai antara 1 sampai 14 (diberikan: {$semester}).");
        }
        $this->semester = $semester;
    }

    // =========================================================================
    // ABSTRACT METHODS (Kontrak Pewarisan yang Wajib Diimplementasikan Child)
    // =========================================================================

    /**
     * Menghitung besaran biaya kuliah akhir sesuai kategori mahasiswa
     */
    abstract public function hitungBiaya(): int;

    /**
     * Mengembalikan nama jenis kategori mahasiswa ('Reguler' atau 'Beasiswa')
     */
    abstract public function getJenis(): string;

    /**
     * Mengembalikan nilai nominal UKT dasar
     */
    abstract public function getUkt(): int;
}
