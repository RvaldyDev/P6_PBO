<?php
declare(strict_types=1);

require_once __DIR__ . '/Mahasiswa.php';

/**
 * Child Class 2: MahasiswaBeasiswa
 * Turunan langsung dari Mahasiswa (Hubungan is-a)
 * 
 * Aturan Bisnis:
 * - Memiliki UKT dasar dan persentase potongan beasiswa (0 - 100%)
 * - hitungBiaya() mengembalikan UKT setelah dikurangi diskon potongan
 */
final class MahasiswaBeasiswa extends Mahasiswa {
    // Properti khusus MahasiswaBeasiswa
    // TIDAK menggandakan properti parent ($nim, $nama, $prodi, $semester)
    private int $ukt;
    private int $potonganPersen;

    /**
     * Constructor MahasiswaBeasiswa
     */
    public function __construct(
        string $nim,
        string $nama,
        string $prodi,
        int $semester,
        int $ukt,
        int $potonganPersen
    ) {
        // Panggil constructor parent untuk inisialisasi identitas umum
        parent::__construct($nim, $nama, $prodi, $semester);

        // Validasi UKT dan potongan beasiswa
        $this->setUkt($ukt);
        $this->setPotonganPersen($potonganPersen);
    }

    public function getUkt(): int {
        return $this->ukt;
    }

    public function setUkt(int $ukt): void {
        if ($ukt < 0) {
            throw new InvalidArgumentException("UKT tidak boleh bernilai negatif (diberikan: {$ukt}).");
        }
        $this->ukt = $ukt;
    }

    public function getPotonganPersen(): int {
        return $this->potonganPersen;
    }

    public function setPotonganPersen(int $potonganPersen): void {
        if ($potonganPersen < 0 || $potonganPersen > 100) {
            throw new InvalidArgumentException("Potongan beasiswa harus bernilai antara 0 sampai 100% (diberikan: {$potonganPersen}%).");
        }
        $this->potonganPersen = $potonganPersen;
    }

    /**
     * Implementasi hitungBiaya() untuk Mahasiswa Beasiswa:
     * Biaya akhir = UKT * (100 - Potongan) / 100
     * Menggunakan intdiv() untuk pembagian bulat presisi mata uang rupiah
     */
    public function hitungBiaya(): int {
        return intdiv($this->ukt * (100 - $this->potonganPersen), 100);
    }

    /**
     * Menghitung nilai rupiah potongan beasiswa yang didapatkan
     */
    public function getNominalPotongan(): int {
        return $this->ukt - $this->hitungBiaya();
    }

    /**
     * Label kategori mahasiswa
     */
    public function getJenis(): string {
        return 'Beasiswa';
    }
}
