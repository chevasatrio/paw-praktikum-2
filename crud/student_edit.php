<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}

include("connection.php");
$nim_get = ""; // Variabel untuk menampung NIM dari GET

// --- PROSES TAMPIL DATA LAMA ---
if (isset($_GET["nim"])) {
    $nim_get = mysqli_real_escape_string($connection, $_GET["nim"]);
    $query = "SELECT * FROM student WHERE nim='$nim_get'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) == 0) {
        // Jika data tidak ditemukan
        header("Location: student_view.php?message=" . urlencode("Data Mahasiswa tidak ditemukan."));
        die();
    }

    // Ambil data lama dari database
    $data_old = mysqli_fetch_assoc($result);

    // Inisialisasi variabel form dengan data lama
    $nim = $data_old['nim'];
    $name = $data_old['name'];
    $birth_city = $data_old['birth_city'];
    $department = $data_old['department'];
    $gpa = $data_old['gpa'];

    // Memecah tanggal lahir (YYYY-MM-DD)
    list($birth_year, $birth_month, $birth_date) = explode('-', $data_old['birth_date']);

    // Menentukan selected untuk Fakultas
    $select_ftib = ($data_old['faculty'] == 'FTIB') ? "selected" : "";
    $select_fteic = ($data_old['faculty'] == 'FTEIC') ? "selected" : "";

    // Variabel error dan message di-reset
    $error_message = "";
}

// --- PROSES UPDATE DATA BARU ---
if (isset($_POST["submit"])) {
    // Ambil data dari POST (gunakan nim yang dikirimkan melalui hidden field)
    $nim_post = htmlentities(strip_tags(trim($_POST["nim_hidden"])));
    $name = htmlentities(strip_tags(trim($_POST["name"])));
    $birth_city = htmlentities(strip_tags(trim($_POST["birth_city"])));
    $faculty = htmlentities(strip_tags(trim($_POST["faculty"])));
    $department = htmlentities(strip_tags(trim($_POST["department"])));
    $gpa = htmlentities(strip_tags(trim($_POST["gpa"])));
    $birth_date = htmlentities(strip_tags(trim($_POST["birth_date"])));
    $birth_month = htmlentities(strip_tags(trim($_POST["birth_month"])));
    $birth_year = htmlentities(strip_tags(trim($_POST["birth_year"])));

    $error_message = "";
    // Lakukan validasi seperti di student_add.php
    // Catatan: Anda tidak perlu cek NIM duplikat karena NIM tidak diubah

    if (empty($name))
        $error_message .= " Nama belum diisi <br>";
    if (empty($birth_city))
        $error_message .= " Tempat lahir belum diisi <br>";
    if (empty($department))
        $error_message .= " Jurusan belum diisi <br>";
    if (!is_numeric($gpa) or ($gpa <= 0))
        $error_message .= " IPK harus diisi dengan angka";

    // Set selected untuk Fakultas baru
    $select_ftib = ($faculty == 'FTIB') ? "selected" : "";
    $select_fteic = ($faculty == 'FTEIC') ? "selected" : "";

    if ($error_message === "") {
        // Sanitasi dan format data untuk Query Update
        $name = mysqli_real_escape_string($connection, $name);
        $birth_city = mysqli_real_escape_string($connection, $birth_city);
        $faculty = mysqli_real_escape_string($connection, $faculty);
        $department = mysqli_real_escape_string($connection, $department);
        $birth_date_full = $birth_year . "-" . $birth_month . "-" . $birth_date;
        $gpa = (float) $gpa;

        // Query UPDATE
        $query = "UPDATE student SET
            name = '$name',
            birth_city = '$birth_city',
            birth_date = '$birth_date_full',
            faculty = '$faculty',
            department = '$department',
            gpa = $gpa
            WHERE nim = '$nim_post'";

        if (mysqli_query($connection, $query)) {
            $message = "Mahasiswa dengan NIM \"<b>{$nim_post}</b>\" sudah berhasil di update";
            $message = urlencode($message);
            header("Location: student_view.php?message={$message}");
        } else {
            die("Query gagal dijalankan: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
        }
    }
    // Jika ada error, NIM yang digunakan di form adalah NIM yang lama
    $nim = $nim_post;
}
// Tambahkan array bulan dan tahun seperti di student_add.php di sini
$arr_month = [
    "1" => "Januari",
    "2" => "Februari",
    "3" => "Maret",
    "4" => "April",
    "5" => "Mei",
    "6" => "Juni",
    "7" => "Juli",
    "8" => "Agustus",
    "9" => "September",
    "10" => "Oktober",
    "11" => "Nopember",
    "12" => "Desember"
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Mahasiswa</title>
    <link href="assets/style.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div id="header">
            <h1 id="logo">Data Mahasiswa</h1>
        </div>
        <hr>
        <nav>
            <ul>
                <li><a href="student_view.php">Tampil</a></li>
                <li><a href="student_add.php">Tambah</a>
                <li><a href="logout.php">Logout</a>
            </ul>
        </nav>
        <h2>Edit Data Mahasiswa (NIM: <?= $nim ?>)</h2>
        <?php
        if ($error_message !== "") {
            echo "<div class='error'>$error_message</div>";
        }
        ?>
        <form id="form_mahasiswa" action="student_add.php" method="post">
            <fieldset>
                <legend>Mahasiswa Baru</legend>
                <p>
                    <label for="nim">NIM: </label>
                    <input type="text" name="nim" id="nim" value="<?= $nim ?>" readonly>
                    <input type="hidden" name="nim_hidden" value="<?= $nim ?>">
                </p>
                <p>
                    <label for="name">Nama : </label>
                    <input type="text" name="name" id="name" value="<?php echo
                        $name ?>">
                </p>
                <p>
                    <label for="birth_city">Tempat Lahir : </label>
                    <input type="text" name="birth_city" id="birth_city" value="<?php echo $birth_city ?>">
                </p>
                <p>
                    <label for="birth_date">Tanggal Lahir : </label>
                    <select name="birth_date" id="birth_date">
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            if ($i == $birth_date) {
                                echo "<option value=$i selected>";
                            } else {
                                echo "<option value=$i>";
                            }
                            echo str_pad($i, 2, "0", STR_PAD_LEFT);
                            echo "</option>";
                        }
                        ?>
                    </select>
                    <select name="birth_month">
                        <?php
                        foreach ($arr_month as $key => $value) {
                            if ($key == $birth_month) {
                                echo "<option value=\"{$key}\"
selected>{$value}</option>";
                            } else {
                                echo "<option value=\"{$key}\">{$value}</option>";
                            }
                        }
                        ?>
                    </select>
                    <select name="birth_year">
                        <?php
                        for ($i = 1990; $i <= 2005; $i++) {
                            if ($i == $birth_year) {
                                echo "<option value=$i selected>";
                            } else {
                                echo "<option value=$i>";
                            }
                            echo "$i </option>";
                        }
                        ?>
                    </select>
                </p>
                <p>
                    <label for="faculty">Fakultas : </label>
                    <select name="faculty" id="faculty">
                        <option value="FTIB" <?php echo $select_ftib ?>>FTIB
                        </option>
                        <option value="FTEIC" <?php echo
                            $select_fteic ?>>FTEIC</option>
                    </select>
                </p>
                <p>
                    <label for="department">Jurusan : </label>
                    <input type="text" name="department" id="department" value="<?php echo $department ?>">
                </p>
                <p>
                    <label for="gpa">IPK : </label>
                    <input type="text" name="gpa" id="gpa" value="<?php echo
                        $gpa ?>" placeholder="Contoh: 2.75"> (angka desimal dipisah
                    dengan
                    karakter titik ".")
                </p>
            </fieldset>
            <br>
            <p>
                <input type="submit" name="submit" value="Update Data">
            </p>
        </form>
    </div>
</body>

</html>