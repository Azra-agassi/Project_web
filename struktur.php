<?php
require __DIR__ . '/configtmdb.php';

$url = 'https://api.themoviedb.org/3/discover/movie?language=id-ID&page=1';

$opsi = [
    'http' => [
        'header' => "Authorization: Bearer $apiKey\r\n",
        'timeout' => 15
    ]
];

$pengaturan = stream_context_create($opsi);
$response = @file_get_contents($url, false, $pengaturan);

$data = [];
$error = '';
$tampilan = 'semua';

if (isset($_GET['tampilan']) && $_GET['tampilan'] === 'satu') {
    $tampilan = 'satu';
}

if ($response === false) {
    $error = 'Gagal mengambil data dari TMDB. Periksa koneksi dan token.';
} else {
    $data = json_decode($response, true);

    if (!is_array($data) || !isset($data['results'])) {
        $error = 'Respons TMDB tidak sesuai.';
    }
}

$isi = $data;

if ($error === '' && $tampilan === 'satu') {
    if (!empty($data['results'])) {
        $isi = $data['results'][0];
    } else {
        $isi = [];
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Penjelajah Data TMDB</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body>
    <main class="container py-4">
        <h1>Penjelajah Data TMDB</h1>

        <p class="text-body-secondary">
            Melihat nama field dan isi data yang diterima dari TMDB.
        </p>

        <form action="struktur.php" method="get" class="d-flex gap-2 mb-4">
            <select name="tampilan" class="form-select w-auto">
                <option
                    value="semua"
                    <?php if ($tampilan === 'semua'): ?>
                        selected
                    <?php endif; ?>
                >
                    Seluruh respons
                </option>

                <option
                    value="satu"
                    <?php if ($tampilan === 'satu'): ?>
                        selected
                    <?php endif; ?>
                >
                    Satu film pertama
                </option>
            </select>

            <button type="submit" class="btn btn-warning">
                Tampilkan
            </button>
        </form>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <?php if ($tampilan === 'satu'): ?>
                        Data satu film pertama
                    <?php else: ?>
                        Seluruh respons — halaman 1
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <pre class="mb-0"><?php
                        echo htmlspecialchars(
                            print_r($isi, true),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                    ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>