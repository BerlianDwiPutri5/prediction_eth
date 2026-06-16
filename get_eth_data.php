<?php
// get_eth_data.php

// Pastikan untuk mengimpor file koneksi database Anda
include 'koneksi.php';

// Jika koneksi gagal, langsung keluarkan JSON error dan hentikan script
if ($koneksi->connect_error) {
    header('Content-Type: application/json'); // Penting: Tetapkan header JSON
    echo json_encode(["error" => "Koneksi database gagal: " . $koneksi->connect_error]);
    exit(); // Hentikan eksekusi script agar tidak ada output lain
}

// Mendapatkan parameter tanggal dari request GET (dari JavaScript)
$start_date_param = $_GET['start'] ?? null;
$end_date_param   = $_GET['end'] ?? null;

// =======================================================================
// KONTROL KETAT TANGGAL: Memastikan filter tanggal selalu berada di tahun 2025
// =======================================================================

$start_dt = DateTime::createFromFormat('Y-m-d', $start_date_param);
$end_dt   = DateTime::createFromFormat('Y-m-d', $end_date_param);

// Default dan validasi untuk tahun 2025
if (
    !$start_dt || $start_dt->format('Y') !== '2025' || // Jika tanggal mulai tidak valid atau bukan 2025
    !$end_dt   || $end_dt->format('Y')   !== '2025' || // Jika tanggal akhir tidak valid atau bukan 2025
    $start_dt > $end_dt // Jika tanggal mulai lebih besar dari tanggal akhir
) {
    // Paksa ke rentang penuh tahun 2025
    $start_date_str = '2025-01-01';
    $end_date_str   = '2025-1-06';
} else {
    // Gunakan tanggal dari parameter jika valid dan di tahun 2025
    $start_date_str = $start_dt->format('Y-m-d');
    $end_date_str   = $end_dt->format('Y-m-d');
}

// =======================================================================
// DEBUGGING: Aktifkan baris di bawah ini untuk melihat tanggal yang digunakan PHP
// HAPUS TANDA '//' DI AWAL BARIS BERIKUT untuk mengaktifkan debugging
// Anda akan melihat output JSON seperti {"debug_start_date":"2025-01-01", "debug_end_date":"2025-12-31"}
// Setelah debugging, JANGAN LUPA tambahkan kembali tanda '//'
// =======================================================================
// header('Content-Type: application/json');
// echo json_encode(["debug_start_date_php_used" => $start_date_str, "debug_end_date_php_used" => $end_date_str]);
// exit();
// =======================================================================


// Siapkan array untuk menampung data yang akan diambil
$data = [];

// Bangun query SQL dengan filter tanggal yang pasti
// Pastikan nama-nama kolom (date, open, high, low, close_actual, close_predicted, volume)
// sesuai persis dengan nama kolom di tabel database Anda.
$sql = "SELECT date, open, high, low, close_actual, close_predicted, volume 
        FROM eth_data 
        WHERE date BETWEEN ? AND ? 
        ORDER BY date ASC"; 

$stmt = $koneksi->prepare($sql);
if ($stmt === FALSE) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Prepare statement gagal: " . $koneksi->error]);
    exit();
}
$stmt->bind_param("ss", $start_date_str, $end_date_str); // "ss" karena $start_date dan $end_date adalah string
$stmt->execute();
$result = $stmt->get_result();

// Periksa apakah query berhasil dijalankan
if ($result) {
    // Ambil data dan masukkan ke dalam array
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'date'            => $row['date'],
            'open'            => (float)$row['open'],
            'high'            => (float)$row['high'],
            'low'             => (float)$row['low'],
            'close_actual'    => (float)$row['close_actual'],
            'close_predicted' => (float)$row['close_predicted'],
            'volume'          => (float)$row['volume']
        ];
    }
    $stmt->close();
} else {
    // Jika query gagal, kirimkan pesan error sebagai JSON
    header('Content-Type: application/json');
    echo json_encode(["error" => "Query gagal: " . $koneksi->error]);
    exit();
}

// Tutup koneksi database
$koneksi->close();

// Set header Content-Type agar browser tahu ini adalah JSON
header('Content-Type: application/json');

// Keluarkan data dalam format JSON
echo json_encode($data);
?>
