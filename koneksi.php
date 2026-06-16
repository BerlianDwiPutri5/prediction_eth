<?php
// koneksi.php

// 1. Memuat autoload Composer. Ini penting agar phpdotenv bisa digunakan.
// Pastikan path ini benar relatif terhadap lokasi file koneksi.php ini.
require __DIR__ . '/vendor/autoload.php';

// 2. Membuat instance Dotenv dan memuat variabel dari file .env
// __DIR__ adalah direktori tempat file koneksi.php ini berada.
// phpdotenv akan mencari file .env di direktori ini atau direktori induknya.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load(); // Ini akan memuat semua variabel dari .env ke $_ENV

// 3. Mengambil nilai dari variabel lingkungan yang sudah dimuat
// Kita mengaksesnya melalui superglobal $_ENV
$host     = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];
$database = $_ENV['DB_NAME'];

// 4. Melakukan koneksi ke database menggunakan nilai dari .env
$koneksi = new mysqli($host, $username, $password, $database);

// 5. Penanganan error koneksi database
if ($koneksi->connect_error) {
    header('Content-Type: application/json'); // Mengirim header bahwa respons adalah JSON
    echo json_encode(["error" => "Koneksi database gagal: " . $koneksi->connect_error]);
    exit(); // Hentikan eksekusi skrip jika koneksi gagal
}

// Opsional: Untuk debugging, kamu bisa menambahkan baris ini untuk memastikan koneksi berhasil.
// echo "Koneksi database berhasil menggunakan variabel dari .env!";

// Variabel $koneksi sekarang berisi objek koneksi yang siap digunakan
// di bagian lain aplikasi PHP-mu untuk berinteraksi dengan database.

?>