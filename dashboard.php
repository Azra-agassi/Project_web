<?php
require __DIR__ . '/configtmdb.php';

// Pilihan filter yang diizinkan.
$pilihan = [
    'tahun' => [
        '' => 'Semua tahun',
        '2026' => '2026',
        '2025' => '2025',
        '2024' => '2024',
        '2023' => '2023',
        '2022' => '2022'
    ],
    'rating' => [
        '' => 'Semua rating',
        '9' => '9 ke atas',
        '8' => '8 ke atas',
        '7' => '7 ke atas',
        '6' => '6 ke atas'
    ],
    'urutan' => [
        'popularity.desc' => 'Populer',
        'vote_average.desc' => 'Rating tertinggi',
        'primary_release_date.desc' => 'Rilis terbaru'
    ],
    'genre' => [
        '' => 'Semua genre',
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

$labelFilter = [
    'tahun' => 'Tahun',
    'rating' => 'Rating minimum',
    'urutan' => 'Urutan',
    'genre' => 'Genre'
];

// Nilai awal filter.
$filter = [
    'tahun' => '',
    'rating' => '',
    'urutan' => 'popularity.desc',
    'genre' => ''
];

// Terima hanya nilai yang tersedia dalam pilihan.
foreach ($filter as $nama => $nilai) {
    if (
        isset($_GET[$nama]) &&
        is_string($_GET[$nama]) &&
        array_key_exists($_GET[$nama], $pilihan[$nama])
    ) {
        $filter[$nama] = $_GET[$nama];
    }
}

// Membaca pencarian.
$pencarian = '';

if (isset($_GET['q']) && is_string($_GET['q'])) {
    $pencarian = trim($_GET['q']);
}

// Membaca halaman.
$halaman = 1;

if (isset($_GET['page']) && is_string($_GET['page'])) {
    $halaman = (int) $_GET['page'];
}

if ($halaman < 1) {
    $halaman = 1;
}

if ($halaman > 500) {
    $halaman = 500;
}

// Siapkan variabel sebelum HTML.
$data = [];
$film = [];
$error = '';
$totalHalaman = 0;
$sebelumnya = '';
$berikutnya = '';

$judulKatalog = 'Jelajahi film';

$parameter = [
    'language' => 'id-ID',
    'page' => $halaman,
    'include_adult' => 'false'
];

if ($pencarian !== '') {
    // Mode pencarian judul.
    $alamat = 'https://api.themoviedb.org/3/search/movie';
    $parameter['query'] = $pencarian;
    $parameterHalaman = ['q' => $pencarian];
    $judulKatalog = 'Hasil pencarian: ' . $pencarian;
} else {
    // Mode katalog dan filter.
    $alamat = 'https://api.themoviedb.org/3/discover/movie';
    $parameter['sort_by'] = $filter['urutan'];

    if ($filter['tahun'] !== '') {
        $parameter['primary_release_year'] = $filter['tahun'];
    }

    if ($filter['rating'] !== '') {
        $parameter['vote_average.gte'] = $filter['rating'];
    }

    if ($filter['genre'] !== '') {
        $parameter['with_genres'] = $filter['genre'];
    }

    $parameterHalaman = $filter;
}

$url = $alamat . '?' . http_build_query($parameter);

// Mengambil data dari TMDB.
if (
    !isset($apiKey) ||
    !is_string($apiKey) ||
    trim($apiKey) === '' ||
    $apiKey === 'ISI_READ_ACCESS_TOKEN_TMDB'
) {
    $error = 'Isi Read Access Token TMDB di configtmdb.php terlebih dahulu.';
} else {
    $opsi = [
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: Bearer $apiKey\r\n"
                      . "Accept: application/json\r\n",
            'timeout' => 20,
            'ignore_errors' => true
        ]
    ];

    $pengaturan = stream_context_create($opsi);

    // Peringatan teknis diganti dengan pesan di halaman.
    $response = @file_get_contents($url, false, $pengaturan);

    if ($response === false) {
        $error = 'Tidak dapat terhubung ke TMDB. Coba lagi sebentar.';
    } else {
        $data = json_decode($response, true);

        if (
            is_array($data) &&
            isset($data['success']) &&
            $data['success'] === false
        ) {
            $error = 'Permintaan ditolak TMDB. Periksa token dan parameter permintaan.';
        } elseif (
            !is_array($data) ||
            !isset($data['results']) ||
            !is_array($data['results']) ||
            !isset($data['total_pages']) ||
            !is_numeric($data['total_pages'])
        ) {
            $error = 'Respons TMDB tidak valid. Data film gagal dimuat.';
        } else {
            $film = $data['results'];
            $totalHalaman = (int) $data['total_pages'];
            $totalHalaman = max(0, min(500, $totalHalaman));
        }
    }
}

// Pagination mempertahankan mode yang sedang digunakan.
if ($halaman > 1) {
    $parameterSebelumnya = $parameterHalaman;
    $parameterSebelumnya['page'] = $halaman - 1;

    $sebelumnya = 'dashboard.php?'
               . http_build_query($parameterSebelumnya);
}

if ($halaman < $totalHalaman) {
    $parameterBerikutnya = $parameterHalaman;
    $parameterBerikutnya['page'] = $halaman + 1;

    $berikutnya = 'dashboard.php?'
               . http_build_query($parameterBerikutnya);
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Temukan Film Favoritmu — Sinempak</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="dsstyle.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar border-bottom">
    <div class="container py-2">
        <a
            class="navbar-brand fs-3 fw-bold text-decoration-none"
            href="dashboard.php"
        >
            Sinempak<span class="text-primary">.</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <span
                class="rounded-circle bg-primary-subtle text-primary-emphasis fw-bold p-3 lh-1"
                aria-hidden="true"
            >T</span>

            <div>
                <p class="small fw-semibold mb-0">Tamu</p>
                <p class="small text-body-secondary mb-0">
                    Belum masuk
                </p>
            </div>
        </div>
    </div>
</nav>

   <main class="container py-4 py-lg-5 flex-grow-1">
    <header class="py-3 py-lg-5 mb-4">
        <p class="small text-primary fw-bold mb-3">
            RUANG UNTUK PECINTA SINEMA
        </p>

        <h1 class="display-4 fw-bold mb-4">
            Temukan Film<br>
            <span class="text-primary">Favoritmu.</span>
        </h1>

        <div class="row">
            <div class="col-12 col-lg-7">
                <p class="lead text-body-secondary mb-0">
                    Jelajahi film menarik, temukan tontonan berikutnya,
                    dan pilih cerita yang ingin kamu nikmati.
                </p>
            </div>
        </div>
    </header>

        <section
            class="bg-body-tertiary border rounded-3 p-3 p-lg-4 mb-5"
            aria-label="Pencarian dan filter film"
        >
            <div class="row g-4 align-items-end">
                <div class="col-12 col-lg-8">
                    <p class="small text-warning fw-semibold mb-3">
                        BROWSE BY
                    </p>

                    <!-- Tanpa q dan page: filter membuka katalog halaman 1. -->
                    <form method="get" action="dashboard.php">
                        <div class="row g-2">
                            <?php foreach ($pilihan as $nama => $daftar): ?>
                                <div class="col-6 col-md-3">
                                    <label
                                        class="form-label small text-body-secondary"
                                        for="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <?= htmlspecialchars($labelFilter[$nama], ENT_QUOTES, 'UTF-8') ?>
                                    </label>

                                    <select
                                        class="form-select form-select-sm"
                                        id="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
                                        name="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
                                        onchange="this.form.submit()"
                                    >
                                        <?php foreach ($daftar as $nilai => $label): ?>
                                            <option
                                                value="<?= htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8') ?>"
                                                <?php if ((string) $nilai === $filter[$nama]): ?>
                                                    selected
                                                <?php endif; ?>
                                            >
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <noscript>
                            <button
                                class="btn btn-warning btn-sm mt-3"
                                type="submit"
                            >
                                Terapkan
                            </button>
                        </noscript>
                    </form>
                </div>

                <div class="col-12 col-lg-4">
                    <!-- Form terpisah untuk pencarian judul. -->
                    <form method="get" action="dashboard.php">
                        <label
                            class="form-label small text-body-secondary"
                            for="pencarian"
                        >
                            Cari judul film
                        </label>

                        <div class="input-group input-group-sm">
                            <input
                                class="form-control"
                                type="search"
                                id="pencarian"
                                name="q"
                                placeholder="Contoh: Interstellar"
                                value="<?= htmlspecialchars($pencarian, ENT_QUOTES, 'UTF-8') ?>"
                            >

                            <button
                                class="btn btn-warning fw-semibold"
                                type="submit"
                            >
                                Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                <a
                    class="btn btn-outline-secondary btn-sm"
                    href="dashboard.php"
                >
                    Reset
                </a>

                <span class="small text-body-secondary">
                    Filter berlaku pada katalog. Pencarian berdasarkan judul.
                </span>
            </div>
        </section>

        <section aria-labelledby="judul-katalog">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <h2 class="h3 fw-semibold mb-0" id="judul-katalog">
                    <?= htmlspecialchars($judulKatalog, ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <?php if ($error === '' && count($film) > 0): ?>
                    <span class="small text-body-secondary">
                        <?= count($film) ?> film di halaman ini
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>

            <?php elseif (count($film) === 0): ?>
                <div class="bg-body-tertiary border rounded-3 p-4 p-lg-5 text-center">
                    <h3 class="h5 mb-3">Belum ada film untuk ditampilkan</h3>

                    <p class="text-body-secondary mb-4">
                        <?php if ($halaman > $totalHalaman && $halaman > 1): ?>
                            Halaman ini tidak tersedia. Kembali ke katalog awal.
                        <?php elseif ($pencarian !== ''): ?>
                            Tidak ada hasil untuk judul tersebut.
                            Coba kata pencarian lain.
                        <?php else: ?>
                            Tidak ada film yang cocok.
                            Coba ubah pilihan filter.
                        <?php endif; ?>
                    </p>

                    <a class="btn btn-warning btn-sm" href="dashboard.php">
                        Kembali ke katalog
                    </a>
                </div>

            <?php else: ?>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-lg-4">
                    <?php
                    $i = 0;

                    while ($i < count($film)) {
                        $item = $film[$i];

                        $judul = 'Judul tidak tersedia';
                        $tahun = 'Tahun belum tersedia';
                        $poster = '';
                        $rating = null;

                        if (
                            isset($item['title']) &&
                            is_string($item['title'])
                        ) {
                            $judul = $item['title'];
                        }

                        if (
                            isset($item['release_date']) &&
                            is_string($item['release_date']) &&
                            strlen($item['release_date']) >= 4
                        ) {
                            $tahun = substr($item['release_date'], 0, 4);
                        }

                        if (
                            !empty($item['poster_path']) &&
                            is_string($item['poster_path'])
                        ) {
                            $poster = 'https://image.tmdb.org/t/p/w500'
                                    . $item['poster_path'];
                        }

                        if (
                            isset($item['vote_average']) &&
                            is_numeric($item['vote_average'])
                        ) {
                            $rating = (float) $item['vote_average'];
                        }
                    ?>
                        <div class="col">
                            <article class="card h-100 bg-body-tertiary border-secondary-subtle shadow-sm overflow-hidden">
                                <?php if ($poster !== ''): ?>
                                    <img
                                        class="card-img-top img-fluid"
                                        src="<?= htmlspecialchars($poster, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="Poster <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <!-- SVG menjaga rasio poster tanpa CSS tambahan. -->
                                    <svg
                                        class="card-img-top bg-secondary-subtle text-body-secondary"
                                        viewBox="0 0 500 750"
                                        role="img"
                                        aria-label="Poster belum tersedia"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <text
                                            x="250"
                                            y="375"
                                            text-anchor="middle"
                                            fill="currentColor"
                                            font-size="24"
                                        >
                                            Poster belum tersedia
                                        </text>
                                    </svg>
                                <?php endif; ?>

                                <div class="card-body d-flex flex-column p-3">
                                    <h3 class="h6 fw-semibold lh-base mb-2">
                                        <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>
                                    </h3>

                                    <p class="small text-body-secondary mb-3">
                                        <?= htmlspecialchars($tahun, ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-auto">
                                        <?php if ($rating !== null): ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                 <?= number_format($item['vote_average'], 1) ?>/10
                                            </span>
                                        <?php else: ?>
                                            <span class="small text-body-secondary">
                                                Rating belum tersedia
                                            </span>
                                        <?php endif; ?>

                                        <span class="small text-body-secondary">
                                            TMDB
                                        </span>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php
                        $i++;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($error === '' && $totalHalaman > 0): ?>
                <nav class="mt-5" aria-label="Pagination film">
                    <ul class="pagination pagination-sm justify-content-center align-items-center flex-wrap gap-2">
                        <?php if ($sebelumnya !== ''): ?>
                            <li class="page-item">
                                <a
                                    class="page-link text-warning"
                                    href="<?= htmlspecialchars($sebelumnya, ENT_QUOTES, 'UTF-8') ?>"
                                    rel="prev"
                                >
                                    &larr; Sebelumnya
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="page-item">
                            <span class="page-link text-body" aria-current="page">
                                Halaman <?= $halaman ?> dari <?= $totalHalaman ?>
                            </span>
                        </li>

                        <?php if ($berikutnya !== ''): ?>
                            <li class="page-item">
                                <a
                                    class="page-link text-warning"
                                    href="<?= htmlspecialchars($berikutnya, ENT_QUOTES, 'UTF-8') ?>"
                                    rel="next"
                                >
                                    Berikutnya &rarr;
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-body-tertiary border-top mt-4">
        <div class="container py-4">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-5">
                    <p class="fs-5 fw-semibold mb-1">
                        Sinempak<span class="text-warning">.</span>
                    </p>

                    <p class="small text-body-secondary mb-0">
                        Temukan tontonan. Temukan cerita.
                    </p>
                </div>

                <div class="col-12 col-md-7">
                    <p class="small text-body-secondary mb-0">
                        Data film disediakan oleh TMDB.
                        Produk ini menggunakan API TMDB, tetapi tidak
                        didukung atau disertifikasi oleh TMDB.
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>