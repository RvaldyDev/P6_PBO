<?php
declare(strict_types=1);

require_once __DIR__ . '/Mahasiswa.php';

/**
 * Child Class 1: MahasiswaReguler
 * Turunan langsung dari Mahasiswa (Hubungan is-a)
 * 
 * Aturan Bisnis:
 * - Memiliki UKT tetap tanpa potongan beasiswa
 * - hitungBiaya() mengembalikan nilai penuh UKT
 */
final class MahasiswaReguler extends Mahasiswa {
    // Properti khusus MahasiswaReguler
    // TIDAK mendeklarasikan ulang $nim, $nama, $prodi, atau $semester (anti-duplikasi)
    private int $ukt;

    /**
     * Constructor MahasiswaReguler
     */
    public function __construct(
        string $nim,
        string $nama,
        string $prodi,
        int $semester,
        int $ukt
    ) {
        // Panggil constructor parent untuk inisialisasi identitas umum
        parent::__construct($nim, $nama, $prodi, $semester);

        // Validasi UKT tidak boleh negatif
        $this->setUkt($ukt);
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

    /**
     * Implementasi hitungBiaya() untuk Mahasiswa Reguler:
     * Biaya akhir = UKT penuh tanpa potongan
     */
    public function hitungBiaya(): int {
        return $this->ukt;
    }

    /**
     * Label kategori mahasiswa
     */
    public function getJenis(): string {
        return 'Reguler';
    }
}
