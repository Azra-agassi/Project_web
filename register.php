<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Buat Akun — Sinempak</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-B/GM4XqrwHnWXNOWMbloTmrYXZg10cakYGmpfsR/bbzQ6JAJI4ihuyADKLnBgrCe" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <main class="container-fluid flex-grow-1 d-flex align-items-center justify-content-center py-12 px-7">
    <div class="row justify-content-center w-100">
      <section class="col-12 md:col-8 lg:col-5 xl:col-4" aria-labelledby="account-title">
        <div class="card bg-2">
          <div class="card-body p-7 md:p-9">
            <a href="index.html">&larr; Kembali ke Beranda</a>

            <h1 class="text-2xl fw-bold mt-7 mb-5" id="account-title">
              Buat akun Sinempak.
            </h1>
            <p class="fg-2 mb-7">Temukan tontonan dan bagikan pendapatmu.</p>

            <!-- Kirim username, name, dan password ke PHP. -->
            <form action="register_process.php" method="post">
              <div class="mb-5">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" id="username" name="username"
                       type="text" autocomplete="username" required>
              </div>

              <div class="mb-5">
                <label class="form-label" for="name">Nama lengkap</label>
                <input class="form-control" id="name" name="name"
                       type="text" autocomplete="name" required>
              </div>

              <div class="mb-5">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                  <input class="form-control" id="password" name="password"
                         type="password" autocomplete="new-password" required>

                  <button class="btn-outline theme-secondary input-group-btn"
                          id="toggle-password" type="button"
                          aria-controls="password" aria-pressed="false"
                          aria-label="Lihat password">
                    Lihat
                  </button>
                </div>
              </div>

              <button class="btn-solid theme-primary w-100 mt-3" type="submit" name="register" value="1">
                Buat Akun
              </button>
            </form>

            <p class="mt-5 mb-0">
              Sudah punya akun? <a href="login.html">Masuk</a>
            </p>
          </div>
        </div>
      </section>
    </div>
  </main>

  <script type="module" src="https://cdn.jsdelivr.net/npm/bootstrap@6.0.0-alpha.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-1a/pXj49ZQ1aHEmrJ+gMw1otqoVsYwlEnlD8mIfY2TV03r20Y0CN7uqx1tQogjPL" crossorigin="anonymous"></script>

  <!-- Fungsi lihat/sembunyikan password, langsung di halaman ini. -->
  <script>
    const password = document.getElementById('password');
    const toggle = document.getElementById('toggle-password');

    toggle.addEventListener('click', function () {
      const tampilkan = password.type === 'password';

      password.type = tampilkan ? 'text' : 'password';
      toggle.textContent = tampilkan ? 'Sembunyikan' : 'Lihat';
      toggle.setAttribute('aria-pressed', String(tampilkan));
      toggle.setAttribute(
        'aria-label',
        tampilkan ? 'Sembunyikan password' : 'Lihat password'
      );
    });
  </script>
</body>
</html>