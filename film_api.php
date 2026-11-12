<?php
// Browser meminta data ke file ini; token TMDB tetap berada di server PHP.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require __DIR__ . '/configtmdb.php';

function balasFilm($status, $data) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// ID harus berupa bilangan positif, bukan URL bebas atau input array.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
if (!$id) {
    balasFilm(400, ['error' => 'ID film tidak valid. Pilih film dari katalog.']);
}
$bagian = $_GET['bagian'] ?? 'detail';
if (!is_string($bagian) || !in_array($bagian, ['detail', 'ulasan'], true)) {
    balasFilm(400, ['error' => 'Bagian film tidak valid.']);
}

// Hanya endpoint TMDB yang sudah ditentukan di sini yang dapat diakses.
function ambilTmdbFilm($path, $parameter) {
    global $apiKey;
    $curl = curl_init('https://api.themoviedb.org/3/movie/' . $path . '?' . http_build_query($parameter));
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey, 'Accept: application/json'],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20
    ]);
    $isi = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($status === 404) balasFilm(404, ['error' => 'Film tidak ditemukan. Pilih film lain dari katalog.']);
    $data = is_string($isi) ? json_decode($isi, true) : null;
    if ($status !== 200 || !is_array($data)) {
        balasFilm(502, ['error' => 'Data film belum bisa dimuat dari TMDB. Silakan coba lagi.']);
    }
    return $data;
}

if ($bagian === 'ulasan') {
    $page = isset($_GET['page']) ? filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]) : 1;
    if (!$page) balasFilm(400, ['error' => 'Halaman ulasan tidak valid.']);
    // Ulasan TMDB umumnya tersedia dalam bahasa Inggris; tampilkan bahasa aslinya.
    $data = ambilTmdbFilm($id . '/reviews', ['language' => 'en-US', 'page' => $page]);
    balasFilm(200, [
        'page' => $data['page'], 'total_pages' => $data['total_pages'], 'total_results' => $data['total_results'],
        'results' => array_map(function ($review) {
            return ['author' => $review['author'] ?? 'Pengguna TMDB', 'content' => $review['content'] ?? '',
                'created_at' => $review['created_at'] ?? '', 'rating' => $review['author_details']['rating'] ?? null];
        }, $data['results'] ?? [])
    ]);
}

// append_to_response mengambil pemeran dan video bersama detail dalam satu permintaan.
$data = ambilTmdbFilm((string) $id, [
    'language' => 'id-ID', 'append_to_response' => 'credits,videos', 'include_video_language' => 'id,en'
]);
$overview = $data['overview'] ?? '';
if (trim($overview) === '') {
    $english = ambilTmdbFilm((string) $id, ['language' => 'en-US']);
    $overview = $english['overview'] ?? '';
}
$videos = array_values(array_filter($data['videos']['results'] ?? [], function ($video) {
    return ($video['site'] ?? '') === 'YouTube' && ($video['type'] ?? '') === 'Trailer'
        && preg_match('/^[a-zA-Z0-9_-]{11}$/', $video['key'] ?? '');
}));
// Dahulukan trailer resmi jika tersedia.
usort($videos, function ($a, $b) { return (int) ($b['official'] ?? false) <=> (int) ($a['official'] ?? false); });
balasFilm(200, [
    'id' => $data['id'], 'title' => $data['title'] ?? $data['original_title'] ?? 'Film',
    'overview' => $overview, 'release_date' => $data['release_date'] ?? '', 'runtime' => $data['runtime'] ?? 0,
    'genres' => array_column($data['genres'] ?? [], 'name'), 'vote_average' => $data['vote_average'] ?? 0,
    'vote_count' => $data['vote_count'] ?? 0, 'poster_path' => $data['poster_path'] ?? null,
    'backdrop_path' => $data['backdrop_path'] ?? null, 'trailer' => $videos[0]['key'] ?? null,
    'cast' => array_map(function ($actor) {
        return ['name' => $actor['name'] ?? '', 'character' => $actor['character'] ?? '', 'profile_path' => $actor['profile_path'] ?? null];
    }, array_slice($data['credits']['cast'] ?? [], 0, 4))
]);
