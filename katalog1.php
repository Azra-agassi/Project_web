<?php
require 'configtmdb.php';

// 1. Alamat untuk meminta film populer.
// Pilihan yang tersedia pada menu.
$pilihan = [
    'tahun' => [
        '' => 'YEAR',
        '2026' => '2026',
        '2025' => '2025',
        '2024' => '2024',
        '2023' => '2023',
        '2022' => '2022'
    ],
    'rating' => [
        '' => 'RATING',
        '9' => '9 ke atas',
        '8' => '8 ke atas',
        '7' => '7 ke atas',
        '6' => '6 ke atas'
    ],
    'urutan' => [
        'popularity.desc' => 'POPULAR',
        'vote_average.desc' => 'Rating tertinggi',
        'primary_release_date.desc' => 'Rilis terbaru'
    ],
    'genre' => [
        '' => 'GENRE',
        '28' => 'Action',
        '12' => 'Adventure',
        '16' => 'Animation',
        '35' => 'Comedy',
        '18' => 'Drama',
        '27' => 'Horror',
        '10749' => 'Romance',
        '878' => 'Science Fiction'
    ]
];

// Nilai awal jika belum memilih filter.
$filter = [
    'tahun' => '',
    'rating' => '',
    'urutan' => 'popularity.desc',
    'genre' => ''
];

// Membaca pilihan dari URL.
// Hanya menerima nilai yang tersedia pada menu.
foreach ($filter as $nama => $nilai) {
    if (
        isset($_GET[$nama]) &&
        is_string($_GET[$nama]) &&
        array_key_exists($_GET[$nama], $pilihan[$nama])
    ) {
        $filter[$nama] = $_GET[$nama];
    }
}

$halaman = 1;

if (isset($_GET['page'])) {
    $halaman = (int) $_GET['page'];
}

if ($halaman < 1) {
    $halaman = 1;
}

if ($halaman > 500) {
    $halaman = 500;
}

// Parameter permintaan ke TMDB.
$parameter = [
    'language' => 'id-ID',
    'page' => $halaman,
    'sort_by' => $filter['urutan'],
    'include_adult' => 'false'
];

if ($filter['tahun'] !== '') {
    $parameter['primary_release_year'] = $filter['tahun'];
}

if ($filter['rating'] !== '') {
    $parameter['vote_average.gte'] = $filter['rating'];
}

if ($filter['genre'] !== '') {
    $parameter['with_genres'] = $filter['genre'];
}

$url = 'https://api.themoviedb.org/3/discover/movie?'
     . http_build_query($parameter);

// Alamat pagination, sambil mempertahankan filter.
$sebelumnya = '?' . http_build_query(
    array_merge($filter, ['page' => $halaman - 1])
);

$berikutnya = '?' . http_build_query(
    array_merge($filter, ['page' => $halaman + 1])
);

// 2. Pengaturan untuk mengirim token.
$opsi = [
    'http' => [
        'header' => "Authorization: Bearer $apiKey\r\n"
    ]
];
$pengaturan = stream_context_create($opsi);

// 3. Mengambil data dari TMDB.
$response = file_get_contents($url, false, $pengaturan);
$film = [];
if ($response !== false) {
    $data = json_decode($response, true);

    if (isset($data['results'])) {
        $film = $data['results'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Katalog Film</title>

</head>
<body>
    <main class="container">
        <h1>Film Populer</h1>
<form method="get" action="katalog1.php" class="browse">
    <span>BROWSE BY</span>

    <div class="browse-menu">
        <?php foreach ($pilihan as $nama => $daftar): ?>
            <select
                name="<?= $nama ?>"
                aria-label="<?= $nama ?>"
                onchange="this.form.submit()"
            >
                <?php foreach ($daftar as $nilai => $label): ?>
                    <option
                        value="<?= htmlspecialchars((string) $nilai) ?>"
                        <?php if ((string) $nilai === $filter[$nama]): ?>
                            selected
                        <?php endif; ?>
                    >
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endforeach; ?>
    </div>

    <a href="katalog1.php">Kembali ke populer</a>

    <noscript>
        <button type="submit">Terapkan</button>
    </noscript>
</form>
        <div class="katalog">
            <?php
            $i = 0;

            while ($i < count($film)) {
                $item = $film[$i];
            ?>
                <article>
                    <!-- ID TMDB pada URL menentukan film yang dimuat halaman detail. -->
                    <a href="detail.html?id=<?= (int) $item['id'] ?>">
                    <?php if (!empty($item['poster_path'])): ?>
                        <img
                            src="https://image.tmdb.org/t/p/w500<?= htmlspecialchars($item['poster_path']) ?>"
                            alt="<?= htmlspecialchars($item['title']) ?>"
                        >
                    <?php endif; ?>

                    <h2><?= htmlspecialchars($item['title']) ?></h2>
                    </a>

                    <p>
                        Rating:
                        <?= number_format($item['vote_average'], 1) ?>/10
                    </p>
                </article>
            <?php
                $i++;
            }
            ?>
        </div>

      <nav class="pagination">
    <?php if ($halaman > 1): ?>
        <a href="<?= htmlspecialchars($sebelumnya) ?>">
            Sebelumnya
        </a>
    <?php endif; ?>

    <span>Halaman <?= $halaman ?></span>

    <?php
    $totalHalaman = $data['total_pages'] ?? 0;

    if ($halaman < $totalHalaman && $halaman < 500):
    ?>
        <a href="<?= htmlspecialchars($berikutnya) ?>">
            Berikutnya
        </a>
    <?php endif; ?>
</nav>
    </main>
</body>
</html>
