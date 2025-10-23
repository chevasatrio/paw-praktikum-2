<?php
session_start();
// Cek apakah sudah login (seperti di file lainnya)
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}

include("connection.php");

// Pastikan parameter NIM ada
if (isset($_GET["nim"])) {
    $nim = mysqli_real_escape_string($connection, $_GET["nim"]);

    // Query untuk menghapus data berdasarkan NIM
    $query = "DELETE FROM student WHERE nim='$nim'";

    if (mysqli_query($connection, $query)) {
        $message = "Mahasiswa dengan NIM \"<b>{$nim}</b>\" berhasil dihapus.";
        $message = urlencode($message);
        // Arahkan kembali ke tampilan data dengan pesan sukses
        header("Location: student_view.php?message={$message}");
    } else {
        // Handle error jika query gagal
        die("Query gagal dijalankan: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
    }
}

mysqli_close($connection);
?>