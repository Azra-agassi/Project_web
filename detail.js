/* URL dari katalog berisi ID TMDB, misalnya detail.html?id=693134.
   API dipanggil lewat PHP agar token tidak masuk ke kode browser. */
'use strict';
const filmId = new URLSearchParams(location.search).get('id');
const el = id => document.getElementById(id);
let reviewPage = 0;
let reviewPages = 0;
let loadingReviews = false;

// textContent menampilkan data sebagai teks, bukan HTML yang dapat dieksekusi.
function teks(id, value) { el(id).textContent = value; }
function tanggal(value) {
  if (!/^\d{4}-\d{2}-\d{2}/.test(value || '')) return 'Belum tersedia';
  const date = new Date(value.slice(0, 10) + 'T00:00:00');
  return Number.isNaN(date.getTime()) ? 'Belum tersedia' : date.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
}

// Jika gambar tidak tersedia/rusak, sembunyikan gambar dan tampilkan keterangan.
function gambar(img, path, size, alt) {
  img.alt = alt;
  if (!/^\/[a-zA-Z0-9_.-]+$/.test(path || '')) { img.hidden = true; return; }
  img.hidden = false;
  img.onerror = () => { img.hidden = true; };
  img.src = `https://image.tmdb.org/t/p/${size}${path}`;
}

async function api(bagian = 'detail', page = 1) {
  const response = await fetch(`film_api.php?${new URLSearchParams({id: filmId, bagian, page})}`, {signal: AbortSignal.timeout(45000)});
  const data = await response.json();
  if (!response.ok) throw new Error(data.error || 'Data film belum bisa dimuat.');
  return data;
}

function pemeran(actors) {
  el('pemeran-film').replaceChildren();
  for (const actor of actors) {
    const li = document.createElement('li');
    li.className = 'col-12 sm:col-6 lg:col-3 d-flex align-items-center gap-5';
    const img = document.createElement('img');
    img.className = 'rounded-circle object-fit-cover flex-shrink-0';
    img.width = img.height = 48;
    img.loading = 'lazy';
    gambar(img, actor.profile_path, 'w185', actor.name);
    const info = document.createElement('div');
    const name = document.createElement('p');
    name.className = 'cast-name fg-white fw-medium mb-0';
    name.textContent = actor.name;
    const role = document.createElement('p');
    role.className = 'cast-role fg-2 mb-0';
    role.textContent = actor.character || 'Peran belum tersedia';
    info.append(name, role);
    li.append(img, info);
    el('pemeran-film').append(li);
  }
  if (!actors.length) el('pemeran-film').textContent = 'Informasi pemeran belum tersedia.';
}

// Setiap review memakai elemen DOM; teks API tidak disisipkan dengan innerHTML.
function tampilUlasan(review) {
  const article = document.createElement('article');
  article.className = 'border-bottom py-5';
  const header = document.createElement('div');
  header.className = 'd-flex justify-content-between align-items-center gap-3 mb-1';
  const info = document.createElement('div');
  info.className = 'd-flex align-items-center flex-wrap gap-3';
  const name = document.createElement('h3');
  name.className = 'review-name fw-semibold fg-white m-0';
  name.textContent = review.author;
  const date = document.createElement('time');
  date.className = 'review-date fg-2';
  date.textContent = tanggal(review.created_at);
  if (review.created_at) date.dateTime = review.created_at;
  const score = document.createElement('span');
  score.className = 'small fg-emphasis-warning text-nowrap';
  score.textContent = review.rating == null ? 'Tanpa rating' : `★ ${review.rating}/10`;
  info.append(name, date);
  header.append(info, score);
  const text = document.createElement('p');
  text.className = 'review-text mb-0';
  text.textContent = review.content;
  // Ulasan panjang dapat dibuka agar halaman tetap mudah dipindai.
  if (review.content.length > 700) {
    const details = document.createElement('details');
    const summary = document.createElement('summary');
    summary.textContent = 'Baca ulasan lengkap';
    details.append(summary, text);
    article.append(header, details);
  } else article.append(header, text);
  el('daftar-ulasan').append(article);
}

async function muatUlasan() {
  if (loadingReviews) return;
  loadingReviews = true;
  el('ulasan-lain').disabled = true;
  teks('status-ulasan', 'Memuat ulasan TMDB…');
  try {
    const data = await api('ulasan', reviewPage + 1);
    reviewPage = data.page;
    reviewPages = Math.min(data.total_pages, 500);
    teks('judul-ulasan', `Ulasan Pengguna (${data.total_results})`);
    data.results.forEach(tampilUlasan);
    teks('status-ulasan', data.total_results ? 'Ulasan dari pengguna TMDB, dalam bahasa aslinya.' : 'Belum ada ulasan untuk film ini di TMDB.');
    teks('ulasan-lain', 'Lihat ulasan lainnya');
    el('ulasan-lain').hidden = reviewPage >= reviewPages;
  } catch (error) {
    teks('status-ulasan', 'Ulasan belum bisa dimuat. Silakan coba lagi.');
    teks('ulasan-lain', 'Coba muat ulasan lagi');
    el('ulasan-lain').hidden = false;
  } finally {
    loadingReviews = false;
    el('ulasan-lain').disabled = false;
  }
}

async function muatFilm() {
  if (!/^[1-9]\d{0,9}$/.test(filmId || '') || Number(filmId) > 2147483647) {
    teks('pesan-film', 'Pilih film dari katalog untuk melihat detailnya.');
    el('konten').setAttribute('aria-busy', 'false');
    return;
  }
  el('coba-lagi').hidden = true;
  teks('pesan-film', 'Memuat detail film…');
  el('konten').setAttribute('aria-busy', 'true');
  try {
    const film = await api();
    document.title = `${film.title} — Sinempak`;
    document.querySelector('meta[name="description"]').content = `Sinopsis, pemeran, trailer, dan ulasan ${film.title} di Sinempak.`;
    teks('judul-film', film.title);
    const minutes = Number(film.runtime);
    const runtime = minutes > 0 ? [Math.floor(minutes / 60) ? `${Math.floor(minutes / 60)} jam` : '', minutes % 60 ? `${minutes % 60} menit` : ''].filter(Boolean).join(' ') : 'Durasi belum tersedia';
    teks('meta-film', [film.release_date.slice(0, 4) || 'Tahun belum tersedia', film.genres.join(', ') || 'Genre belum tersedia', runtime].join(' · '));
    teks('nilai-rating', film.vote_count ? Number(film.vote_average).toFixed(1) : 'Belum dinilai');
    el('rating-film').setAttribute('aria-label', film.vote_count ? `Rating ${Number(film.vote_average).toFixed(1)} dari 10` : 'Belum ada rating');
    teks('rilis-film', tanggal(film.release_date));
    if (film.release_date) el('rilis-film').dateTime = film.release_date;
    teks('sinopsis-film', film.overview || 'Sinopsis film ini belum tersedia.');
    gambar(el('poster-film'), film.poster_path, 'w500', `Poster ${film.title}`);
    gambar(el('gambar-banner'), film.backdrop_path, 'w1280', '');
    el('banner-film').hidden = !film.backdrop_path;
    pemeran(film.cast);
    const trailer = el('trailer-film');
    trailer.hidden = !film.trailer;
    if (film.trailer) trailer.href = `https://www.youtube.com/watch?v=${encodeURIComponent(film.trailer)}`;
    el('status-film').hidden = true;
    el('data-film').hidden = false;
    // Ulasan dimuat terpisah agar kegagalannya tidak menutupi detail film.
    muatUlasan();
  } catch (error) {
    teks('pesan-film', error.name === 'TimeoutError' ? 'Permintaan terlalu lama. Silakan coba lagi.' : error.message || 'Detail film belum bisa dimuat.');
    el('coba-lagi').hidden = false;
  } finally {
    el('konten').setAttribute('aria-busy', 'false');
  }
}
el('ulasan-lain').addEventListener('click', muatUlasan);
el('coba-lagi').addEventListener('click', muatFilm);
muatFilm();
