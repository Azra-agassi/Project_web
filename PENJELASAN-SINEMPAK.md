# Halaman detail Sinempak

Buka `http://localhost/Web/katalog1.php` saat Apache XAMPP menyala, lalu klik poster atau judul film. URL detail memakai ID TMDB, misalnya `http://localhost/Web/detail.html?id=693134`. PHP dan API memerlukan server XAMPP; jangan membuka halaman lewat `file://`.

Implementasi mengikuti frame **52:2** pada file Figma WEB. Halaman utama, login, register, dan CSS halaman lama tidak diubah.

## File yang dipakai

- `detail.html`: struktur halaman, teks, gambar, dan komentar penjelasan tiap bagian.
- `detail.js`: membaca ID dari URL, mengambil data API, dan mengisi elemen HTML sesuai film terpilih.
- `film_api.php`: meminta detail, pemeran, trailer, dan ulasan dari TMDB. Token dibaca dari `configtmdb.php` di server.
- `katalog1.php`: poster dan judul ditautkan ke `detail.html?id=ID_FILM`. Filter tetap mengarah ke katalog.
- `detail.css`: font, warna navy, gradasi banner, dan ukuran khusus Figma.
- `assets/sinempak/`: enam gambar asli Figma, ikon bintang/play/dropdown, dan font Inter.
- Bootstrap **6.0.0-alpha.1**: CSS dan JavaScript dimuat lewat CDN jsDelivr dengan integrity dan crossorigin. Folder `assets/bootstrap/` dihapus. Halaman membutuhkan koneksi internet untuk memuat Bootstrap.

## Cara membaca kelas Bootstrap

| Kelas | Kegunaan pada halaman |
| --- | --- |
| `container` | Memusatkan konten halaman. |
| `d-flex` | Mengatur elemen dengan flexbox. |
| `flex-column sm:flex-row` | Menumpuk poster dan teks di HP, menyejajarkannya mulai 576px. |
| `gap-7` | Memberi jarak 24px antar elemen flex. |
| `row g-5` | Membuat baris grid dengan jarak 16px. |
| `col-12 sm:col-6 lg:col-3` | Menampilkan pemeran dalam satu, dua, atau empat kolom sesuai lebar layar. |
| `mb-0`, `mt-7`, `py-5` | Mengatur margin bawah, margin atas, dan padding vertikal. Angka mengikuti skala Bootstrap, bukan satuan piksel langsung. |
| `rounded-circle object-fit-cover` | Membulatkan dan memotong foto pemeran tanpa meregangkannya. |
| `btn-solid theme-primary` | Membuat tombol biru beserta hover, fokus, dan keadaan aktif. |
| `form-control` | Memberi tampilan Bootstrap pada dropdown dan textarea. |
| `border-bottom` | Menambahkan garis pemisah ulasan. |
| `data-bs-toggle="dialog"` | Membuka dialog melalui JavaScript resmi Bootstrap. |

CSS tambahan diperlukan karena Bootstrap tidak menyediakan warna navy, gradasi banner, dan semua ukuran persis dari Figma sebagai kelas bawaan. Layout, tombol, form, responsivitas, dan dialog tetap menggunakan Bootstrap. JavaScript native dipakai untuk memuat data API, tanpa React atau framework aplikasi tambahan.

## Alur data API

1. Katalog menerima `id` dari setiap film TMDB dan menaruhnya di tautan detail.
2. `detail.js` membaca `id` dengan `URLSearchParams`.
3. Browser meminta `film_api.php?id=ID_FILM` tanpa membawa token TMDB.
4. PHP memvalidasi ID lalu meminta `/movie/{id}` dengan `append_to_response=credits,videos`.
5. Judul, poster, backdrop, genre, durasi, tanggal rilis, rating, sinopsis, empat pemeran, dan trailer diisi dari respons.
6. Ulasan diminta terpisah melalui `film_api.php?id=ID_FILM&bagian=ulasan&page=1`. Tombol berikutnya memuat halaman ulasan selanjutnya.

Sinopsis memakai bahasa Indonesia, dengan bahasa Inggris sebagai cadangan jika terjemahan kosong. Ulasan ditampilkan dalam bahasa asli dari endpoint berbahasa Inggris. Data yang kosong diberi keterangan atau gambarnya disembunyikan, tanpa menampilkan gambar Dune untuk film lain. ID kosong/tidak valid, film tidak ditemukan, dan gangguan API memiliki pesan tersendiri serta tombol kembali atau coba lagi.

## Interaksi

- Logo kembali ke `index.html`.
- Tombol trailer membuka trailer YouTube dari data film terpilih; disembunyikan jika trailer tidak tersedia.
- Dropdown rating dan textarea bisa diisi.
- Tombol kirim ulasan membuka keterangan bahwa pengiriman ulasan Sinempak belum tersedia. **Belum ada penyimpanan ulasan ke database lokal.** Daftar ulasan yang dibaca berasal dari TMDB.
- Dialog mendukung tombol tutup, klik di luar dialog, dan Escape.

Poster, backdrop, dan foto pemeran kini mengikuti TMDB. Aset gambar contoh Figma tetap tersimpan sebagai referensi, tetapi tidak dipakai sebagai pengganti film terpilih.

## Referensi

- Desain: https://www.figma.com/design/962hnJbMNyKs6Ixw4pyfVf/WEB?node-id=52-2
- Bootstrap: https://getbootstrap.com/docs/6.0/
- API detail: https://developer.themoviedb.org/reference/movie-details
- API ulasan: https://developer.themoviedb.org/reference/movie-reviews
