<?php
declare(strict_types=1);
session_start();

/*
 * Aplikasi CRUD biodata: PHP (index.php) + Google Apps Script (Code.gs) + Google Sheets.
 * Simpan sebagai C:\laragon\www\biodata\index.php, buka http://localhost/biodata/
 *
 * API key HARUS sama dengan yang diset di setup() pada Code.gs.
 * File ini berdiri sendiri: tidak memuat file lain (config, CA bundle, dsb).
 */
$settingsFile = dirname(__DIR__) . '/config.local.php';
$settings = is_file($settingsFile) ? require $settingsFile : [];
$config = [
    'api_url' => $settings['apps_script_url'] ?? getenv('APPS_SCRIPT_URL') ?: '',
    'api_key' => $settings['apps_script_key'] ?? getenv('APPS_SCRIPT_KEY') ?: '',
];

$fields = [
    'nama'         => 'Nama',
    'nim'          => 'NIM',
    'kelas'        => 'Kelas',
    'hobby'        => 'Hobi',
    'email'        => 'Email',
    'kota tinggal' => 'Kota tinggal',
    'telepon'      => 'Telepon',
    'usia'         => 'Usia',
];

function h($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function api(array $payload): array
{
    global $config;

    if (!function_exists('curl_init')) {
        throw new RuntimeException('Aktifkan ekstensi cURL pada PHP, lalu restart Laragon/Apache.');
    }
    if (!preg_match('~^https://script\.google\.com/macros/s/[^/]+/exec$~', $config['api_url'])) {
        throw new RuntimeException('Periksa URL Apps Script pada bagian awal index.php.');
    }

    $ch = curl_init($config['api_url']);
    $options = [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload + ['api_key' => $config['api_key']], JSON_THROW_ON_ERROR),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT        => 60,
    ];

    curl_setopt_array($ch, $options);
    $body   = curl_exec($ch);
    $error  = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException('Koneksi API gagal: ' . $error . '. Jangan kirim ulang perubahan sebelum memeriksa Google Sheets.');
    }
    if ($status !== 200) {
        throw new RuntimeException('API mengembalikan HTTP ' . $status . '. Periksa deployment dan izin akses (Anyone).');
    }

    $result = json_decode($body, true);
    if (!is_array($result) || !array_key_exists('success', $result)) {
        throw new RuntimeException('Respons bukan JSON API. Pastikan deployment Web app dapat diakses Anyone dan URL berakhir /exec.');
    }
    if (!$result['success']) {
        $message = (string)($result['message'] ?? 'Operasi API gagal.');
        if (stripos($message, 'API key') !== false) {
            $message = 'API key tidak cocok atau setup() belum dijalankan. Samakan api_key dengan Code.gs, jalankan setup(), lalu perbarui deployment ke versi baru.';
        }
        throw new RuntimeException($message);
    }
    return $result;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error  = '';
$rows   = [];
$values = array_fill_keys(array_keys($fields), '');
$editId = '';
$flash  = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
            throw new RuntimeException('Sesi formulir tidak valid. Muat ulang halaman.');
        }
        $action = (string)($_POST['action'] ?? '');
        if (!in_array($action, ['create', 'update', 'delete'], true)) {
            throw new RuntimeException('Aksi tidak valid.');
        }

        $payload = ['action' => $action, 'id' => (string)($_POST['id'] ?? '')];
        if ($action !== 'delete') {
            $input = $_POST['data'] ?? [];
            if (!is_array($input)) $input = [];
            foreach ($fields as $key => $label) {
                $values[$key] = trim((string)($input[$key] ?? ''));
            }
            $editId = $action === 'update' ? $payload['id'] : '';
            $payload['data'] = $values;
        }

        $result = api($payload);
        $_SESSION['flash'] = $result['message'] ?? 'Berhasil.';
        header('Location: index.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

try {
    $rows = api(['action' => 'list'])['data'] ?? [];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['edit'])) {
        $editParam = (string)$_GET['edit'];
        $found = false;
        foreach ($rows as $row) {
            if ($row['id'] === $editParam) {
                $values = $row;
                $editId = $row['id'];
                $found  = true;
                break;
            }
        }
        if (!$found) {
            throw new RuntimeException('Data yang akan diubah tidak ditemukan.');
        }
    }
} catch (Throwable $e) {
    if ($error === '') $error = $e->getMessage();
}

$q = trim((string)($_GET['q'] ?? ''));
$visible = array_filter($rows, function ($r) use ($q, $fields) {
    if ($q === '') return true;
    foreach ($fields as $k => $label) {
        if (stripos((string)($r[$k] ?? ''), $q) !== false) return true;
    }
    return false;
});
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Data Mahasiswa • CRUD Google Sheets</title>
<style>
:root{color-scheme:dark;--bg:#0b1120;--panel:#141e31;--line:#29354b;--text:#e5edf8;--muted:#a9b7cc;--accent:#66e2bd}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font:15px/1.5 system-ui,-apple-system,Segoe UI,sans-serif}
main{max-width:1440px;margin:auto;padding:44px 28px}
header{margin-bottom:28px}
.eyebrow{font-size:11px;letter-spacing:2px;color:var(--accent);font-weight:700}
h1{font-size:clamp(30px,4vw,46px);letter-spacing:-1.7px;margin:10px 0}
h1 span{color:var(--accent)}
header p,footer{color:var(--muted)}
.panel{background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:24px;margin:22px 0}
.section-title{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap}
h2{margin:0;font-size:20px}
.section-title>span{color:var(--muted);font-size:13px}
.form-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}
label{font-size:13px;font-weight:600;display:block}
input{width:100%;border:1px solid #3c4960;border-radius:8px;background:#0e1728;color:var(--text);font:inherit;padding:11px 12px;margin-top:7px}
input:focus{outline:2px solid var(--accent);outline-offset:2px}
button,.button{background:var(--accent);color:#062f24;border:0;border-radius:8px;padding:11px 17px;font:600 13px system-ui;cursor:pointer;text-decoration:none;display:inline-block;white-space:nowrap}
button:disabled{opacity:.5;cursor:wait}
.secondary{background:#26364d;color:var(--text)}
.danger{background:#452732;color:#ffb7c4}
.actions{display:flex;gap:10px;margin-top:22px}
.search{display:flex;gap:8px;align-items:center}
.search input{margin:0;max-width:240px}
.search a{color:var(--accent)}
.badge{display:inline-block;background:#233c3b;color:var(--accent);font-size:13px;padding:3px 10px;border-radius:20px;margin-left:7px}
.table-wrap{overflow:auto}
table{width:100%;border-collapse:collapse;text-align:left;font-size:13px}
th{color:var(--muted);font-weight:500;white-space:nowrap}
td,th{padding:15px 12px;border-bottom:1px solid var(--line)}
td{overflow-wrap:anywhere;min-width:85px;max-width:260px}
.row-actions{display:flex;gap:7px}
.small{padding:7px 11px}
.notice{padding:16px 20px;border-radius:10px;overflow-wrap:anywhere;margin-bottom:12px}
.success{background:#173c32;color:#a9f4d9}
.error{background:#44242f;color:#ffc4d0}
.empty{text-align:center;color:var(--muted);padding:35px}
footer{font-size:12px;margin:28px 0}
@media(max-width:900px){.form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:500px){main{padding:24px 14px}.panel{padding:17px}.form-grid{grid-template-columns:1fr}.search{width:100%}.search input{max-width:none}}
</style>
</head>
<body>
<nav aria-label="Menu utama" style="display:flex;gap:10px;flex-wrap:wrap;max-width:1100px;margin:0 auto 22px;padding:14px;background:#101b30;border:1px solid #334155;border-radius:12px;font:600 14px system-ui;">
<a href="../index.php" style="color:#e2e8f0;text-decoration:none;padding:8px 12px;">&#8592; Beranda</a>
<a href="../crud/index.php" style="color:#c4b5fd;text-decoration:none;padding:8px 12px;">CRUD</a>
<a href="../apify/index.php" style="color:#67e8f9;text-decoration:none;padding:8px 12px;">Apify Integrasi</a>
</nav>

<main>
<header>
  <div class="eyebrow">PEMROGRAMAN WEB • SOAL NOMOR 1</div>
  <h1>Data Mahasiswa<span>.</span></h1>
  <p>Kelola biodata melalui PHP dan API Google Sheets.</p>
</header>

<?php if ($flash): ?><div class="notice success" role="status"><?= h($flash) ?></div><?php endif ?>
<?php if ($error): ?><div class="notice error" role="alert"><?= h($error) ?></div><?php endif ?>

<section class="panel">
  <div class="section-title">
    <h2><?= $editId ? 'Ubah biodata' : 'Tambah biodata' ?></h2>
    <span>8 kolom biodata</span>
  </div>
  <form method="post" action="index.php">
    <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
    <input type="hidden" name="action" value="<?= $editId ? 'update' : 'create' ?>">
    <input type="hidden" name="id" value="<?= h($editId) ?>">
    <div class="form-grid">
      <?php foreach ($fields as $key => $label):
          $type = $key === 'email' ? 'email' : ($key === 'usia' ? 'number' : ($key === 'telepon' ? 'tel' : 'text')); ?>
        <label><?= h($label) ?>
          <input name="data[<?= h($key) ?>]" type="<?= $type ?>" value="<?= h($values[$key] ?? '') ?>" required maxlength="200"
            <?= $key === 'usia' ? 'min="1" max="120" step="1"' : '' ?>
            <?= $key === 'nim' ? 'inputmode="numeric" pattern="[0-9]{5,30}" title="5-30 digit angka"' : '' ?>>
        </label>
      <?php endforeach ?>
    </div>
    <div class="actions">
      <button type="submit"><?= $editId ? 'Simpan perubahan' : '+ Tambah data' ?></button>
      <?php if ($editId): ?><a class="button secondary" href="index.php">Batal</a><?php endif ?>
    </div>
  </form>
</section>

<section class="panel">
  <div class="section-title">
    <h2>Daftar mahasiswa <span class="badge"><?= count($visible) ?></span></h2>
    <form method="get" class="search">
      <input name="q" value="<?= h($q) ?>" placeholder="Cari biodata…" aria-label="Cari biodata">
      <button class="secondary" type="submit">Cari</button>
      <?php if ($q !== ''): ?><a href="index.php">Reset</a><?php endif ?>
    </form>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>No.</th>
          <?php foreach ($fields as $label): ?><th><?= h($label) ?></th><?php endforeach ?>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach ($visible as $row): ?>
          <tr>
            <td><?= $no++ ?></td>
            <?php foreach ($fields as $key => $label): ?><td><?= h($row[$key] ?? '') ?></td><?php endforeach ?>
            <td>
              <div class="row-actions">
                <a class="button small secondary" href="?edit=<?= rawurlencode((string)$row['id']) ?>">Ubah</a>
                <form method="post" action="index.php" onsubmit="return confirm('Hapus data ini dari Google Sheets?')">
                  <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= h($row['id']) ?>">
                  <button class="small danger" type="submit">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach ?>
        <?php if (!$visible): ?>
          <tr><td colspan="<?= count($fields) + 2 ?>" class="empty"><?= $error ? 'Data belum dapat dimuat.' : 'Tidak ada data yang ditampilkan.' ?></td></tr>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</section>

<footer>PHP + Google Apps Script + Google Sheets • Create, Read, Update, Delete</footer>
</main>

<script>
// Cegah klik ganda: Apps Script bisa lambat, dan create tanpa guard akan membuat data duplikat.
document.querySelectorAll('form[method=post]').forEach(function (f) {
  f.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    setTimeout(function () {
      f.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
    }, 0);
  });
});
</script>
</body>
</html>