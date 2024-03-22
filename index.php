<?php
session_start();

// Sertakan file koneksi.php
require_once 'koneksi.php';

// Inisialisasi variabel hasil
$hasil = '';

// Fungsi validasi untuk memeriksa apakah nilai adalah angka yang valid
function isValidNumber($value) {
    return is_numeric($value);
}

// Ambil semua riwayat perhitungan dari database
$riwayat_perhitungan = [];
$sql = "SELECT * FROM calculation_results ORDER BY id DESC";
$result = $koneksi->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $riwayat_perhitungan[] = $row;
    }
}

if(isset($_POST['eksekusi'])) {
    $angka1 = $_POST['angka1'];
    $angka2 = $_POST['angka2'];
    $operator = $_POST['operator'];

    // Validasi input angka
    if (!isValidNumber($angka1) || !isValidNumber($angka2)) {
        echo "Angka harus berupa nilai numerik.";
        exit; // Hentikan eksekusi jika input tidak valid
    }

    // Lakukan perhitungan
    switch ($operator) {
        case '+':
            $hasil = $angka1 + $angka2;
            break;
        case '-':
            $hasil = $angka1 - $angka2;
            break;
        case 'x':
            $hasil = $angka1 * $angka2;
            break;
        case '/':
            if ($angka2 == 0) {
                echo "Tidak dapat melakukan pembagian dengan angka nol.";
                exit; // Hentikan eksekusi jika terjadi pembagian dengan nol
            }
            $hasil = $angka1 / $angka2;
            break;
        default:
            echo "Operator tidak valid.";
            exit; // Hentikan eksekusi jika operator tidak valid
    }

    // Simpan hasil perhitungan ke dalam session
    $_SESSION['hasil_perhitungan'] = [
        'angka1' => $angka1,
        'angka2' => $angka2,
        'operator' => $operator,
        'hasil' => $hasil
    ];

    // Simpan hasil perhitungan ke dalam tabel
    $sql = "INSERT INTO calculation_results (angka1, angka2, operator, hasil) VALUES ('$angka1', '$angka2', '$operator', '$hasil')";
    if ($koneksi->query($sql) === TRUE) {
        echo "Data tersimpan.";
    } else {
        echo "Error: " . $sql . "<br>" . $koneksi->error;
    }

     // Perbarui riwayat perhitungan setelah perhitungan yang baru dilakukan
array_unshift($riwayat_perhitungan, [
    'angka1' => $angka1,
    'angka2' => $angka2,
    'operator' => $operator,
    'hasil' => $hasil
]);
}

// Tutup koneksi tidak perlu dilakukan di sini karena koneksi.php telah menutup koneksi.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perhitungan</title>
</head>
<body>
    <form action="" method="post">
        Angka 1 <input type="text" name="angka1" value="<?php echo isset($_SESSION['hasil_perhitungan']['angka1']) ? $_SESSION['hasil_perhitungan']['angka1'] : ''; ?>"> <br>
        Angka 2 <input type="text" name="angka2" value="<?php echo isset($_SESSION['hasil_perhitungan']['angka2']) ? $_SESSION['hasil_perhitungan']['angka2'] : ''; ?>"> <br>
        Operator <select name="operator">
            <option value="+" <?php echo isset($_SESSION['hasil_perhitungan']['operator']) && $_SESSION['hasil_perhitungan']['operator'] == '+' ? 'selected' : ''; ?>>+</option>
            <option value="-" <?php echo isset($_SESSION['hasil_perhitungan']['operator']) && $_SESSION['hasil_perhitungan']['operator'] == '-' ? 'selected' : ''; ?>>-</option>
            <option value="x" <?php echo isset($_SESSION['hasil_perhitungan']['operator']) && $_SESSION['hasil_perhitungan']['operator'] == 'x' ? 'selected' : ''; ?>>x</option>
            <option value="/" <?php echo isset($_SESSION['hasil_perhitungan']['operator']) && $_SESSION['hasil_perhitungan']['operator'] == '/' ? 'selected' : ''; ?>>/</option>
        </select>
        <button type="submit" name="eksekusi">Eksekusi</button>
    </form>
    
    <?php if(isset($_SESSION['hasil_perhitungan'])): ?>
    <div>
        <?php
        echo "Hasil perhitungan: ";
        echo $_SESSION['hasil_perhitungan']['angka1'] . " ";
        echo $_SESSION['hasil_perhitungan']['operator'] . " ";
        echo $_SESSION['hasil_perhitungan']['angka2'] . " = ";
        echo $_SESSION['hasil_perhitungan']['hasil'];
        ?>
    </div>
    <?php endif; ?>

    <hr>
    <h2>Riwayat Perhitungan</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Angka 1</th>
                <th>Angka 2</th>
                <th>Operator</th>
                <th>Hasil</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($riwayat_perhitungan as $riwayat): ?>
            <tr>
                <td><?php echo $riwayat['angka1']; ?></td>
                <td><?php echo $riwayat['angka2']; ?></td>
                <td><?php echo $riwayat['operator']; ?></td>
                <td><?php echo $riwayat['hasil']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
