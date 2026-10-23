<?php
/**
 * TUGAS NO 2 - Sentimen masyarakat Tangsel di TikTok terkait turunnya IHSG
 * Terkoneksi ke Apify (dataset hasil TikTok Scraper milik sendiri) via API.
 * Menampilkan 8 data: Nama Akun, Isi Komentar, Link Sumber.
 *
 * Cara pakai:
 *  1. Isi APIFY_TOKEN (Apify Console > Settings > Integrations > API token)
 *  2. Jalankan: php -S localhost:8000   lalu buka http://localhost:8000
 *  3. Klik "Ambil Data dari Apify" (hasil di-cache di cache.json).
 */

// ================= KONFIGURASI =================
$settingsFile = dirname(__DIR__) . '/config.local.php';
$settings = is_file($settingsFile) ? require $settingsFile : [];
define('APIFY_TOKEN', $settings['apify_token'] ?? getenv('APIFY_TOKEN') ?: '');
define('DATASET_ID', $settings['apify_dataset_id'] ?? getenv('APIFY_DATASET_ID') ?: '');
const JUMLAH_DATA = 8;                         // sesuai soal: 8 data
const CACHE_FILE  = __DIR__ . '/cache.json';

// ===============================================

/** Ambil data dari dataset Apify (hasil run yang sudah selesai). */
function ambilDataset(): array
{
    if (APIFY_TOKEN === '' || DATASET_ID === '') {
        throw new RuntimeException('Isi apify_token dan apify_dataset_id pada config.local.php terlebih dahulu.');
    }
    $url = 'https://api.apify.com/v2/datasets/' . rawurlencode(DATASET_ID)
         . '/items?format=json&clean=true&limit=100';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . APIFY_TOKEN],
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($res === false) throw new Exception("cURL error: $err");
    $data = json_decode($res, true);
    if ($code >= 400 || !is_array($data)) {
        $msg = $data['error']['message'] ?? substr((string)$res, 0, 200);
        throw new Exception("Apify HTTP $code: $msg");
    }
    return $data;
}

/** Ambil nilai pertama yang ada dari beberapa kemungkinan key (mendukung nested "a.b"). */
function ambil(array $row, array $keys, $default = '-')
{
    foreach ($keys as $k) {
        $v = $row;
        foreach (explode('.', $k) as $part) {
            if (is_array($v) && array_key_exists($part, $v)) { $v = $v[$part]; }
            else { $v = null; break; }
        }
        if ($v !== null && $v !== '' && !is_array($v)) return $v;
    }
    return $default;
}

/** Petakan hasil mentah actor -> 3 kolom yang diminta soal. */
function petakan(array $items): array
{
    $out = [];
    foreach ($items as $row) {
        if (!is_array($row)) continue;
        $isi = ambil($row, ['text', 'comment', 'desc', 'caption']);
        if ($isi === '-') continue;
        $akun = ambil($row, [
            'uniqueId', 'user.uniqueId', 'authorMeta.name', 'authorMeta.nickName',
            'author', 'username', 'nickname',
        ]);
        $link = ambil($row, ['videoWebUrl', 'webVideoUrl', 'postUrl', 'url', 'submittedVideoUrl']);
        $out[] = ['akun' => $akun, 'isi' => $isi, 'link' => $link];
        if (count($out) >= JUMLAH_DATA) break;
    }
    return $out;
}

/** Sentimen sederhana berbasis kata kunci (bonus untuk analisis). */
function sentimen(string $t): string
{
    $t = mb_strtolower($t);
    $neg = ['turun','anjlok','rugi','panik','jatuh','bangkrut','hancur','cemas','merah','loss','boncos','krisis'];
    $pos = ['naik','cuan','untung','bagus','optimis','rebound','hijau','peluang','beli','murah'];
    $s = 0;
    foreach ($neg as $w) if (str_contains($t, $w)) $s--;
    foreach ($pos as $w) if (str_contains($t, $w)) $s++;
    return $s < 0 ? 'Negatif' : ($s > 0 ? 'Positif' : 'Netral');
}

// ---------- Logika halaman ----------
$error = null;
if (isset($_POST['refresh'])) {
    try {
        $items = ambilDataset();
        file_put_contents(CACHE_FILE, json_encode(petakan($items), JSON_UNESCAPED_UNICODE));
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
$data = file_exists(CACHE_FILE) ? (json_decode(file_get_contents(CACHE_FILE), true) ?: []) : [];
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sentimen Tangsel - IHSG Turun (TikTok via Apify)</title>
<style>
  body{font-family:system-ui,Arial,sans-serif;background:#f4f6f8;margin:0;padding:24px;color:#1f2933}
  .wrap{max-width:1000px;margin:auto}
  h1{font-size:1.4rem;margin:0 0 4px}
  p.sub{margin:0 0 16px;color:#52606d}
  button{background:#0b5fff;color:#fff;border:0;padding:10px 18px;border-radius:8px;cursor:pointer;font-size:.95rem}
  button:hover{background:#0848c4}
  table{width:100%;border-collapse:collapse;background:#fff;margin-top:16px;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.1)}
  th,td{padding:10px 12px;text-align:left;vertical-align:top;border-bottom:1px solid #e4e7eb;font-size:.92rem}
  th{background:#102a43;color:#fff}
  .err{background:#fde8e8;color:#9b1c1c;padding:10px 14px;border-radius:8px;margin-top:12px}
  .tag{padding:2px 8px;border-radius:999px;font-size:.78rem;font-weight:600}
  .Negatif{background:#fde8e8;color:#9b1c1c}.Positif{background:#e3f9e5;color:#0e5c1f}.Netral{background:#e4e7eb;color:#323f4b}
  a{color:#0b5fff;word-break:break-all}
</style>
</head>
<body>
<nav aria-label="Menu utama" style="display:flex;gap:10px;flex-wrap:wrap;max-width:1100px;margin:0 auto 22px;padding:14px;background:#101b30;border:1px solid #334155;border-radius:12px;font:600 14px system-ui;">
<a href="../index.php" style="color:#e2e8f0;text-decoration:none;padding:8px 12px;">&#8592; Beranda</a>
<a href="../crud/index.php" style="color:#c4b5fd;text-decoration:none;padding:8px 12px;">CRUD</a>
<a href="../apify/index.php" style="color:#67e8f9;text-decoration:none;padding:8px 12px;">Apify Integrasi</a>
</nav>

<div class="wrap">
  <h1>Sentimen Masyarakat Tangsel di TikTok</h1>
  <p class="sub">Topik: turunnya IHSG Jumat lalu &middot; Sumber data: Apify (Dataset <?= $h(DATASET_ID) ?>) &middot; <?= JUMLAH_DATA ?> data</p>

  <form method="post"><button name="refresh" value="1">Ambil Data dari Apify</button></form>
  <?php if ($error): ?><div class="err"><strong>Gagal:</strong> <?= $h($error) ?></div><?php endif; ?>

  <table>
    <tr><th>No</th><th>Nama Akun</th><th>Isi Komentar</th><th>Sentimen</th><th>Link Sumber</th></tr>
    <?php if (!$data): ?>
      <tr><td colspan="5">Belum ada data. Klik tombol di atas untuk mengambil data.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $d): $s = sentimen($d['isi']); ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td>@<?= $h(ltrim($d['akun'], '@')) ?></td>
        <td><?= $h($d['isi']) ?></td>
        <td><span class="tag <?= $s ?>"><?= $s ?></span></td>
        <td><?php if (str_starts_with($d['link'], 'http')): ?>
              <a href="<?= $h($d['link']) ?>" target="_blank" rel="noopener">Buka</a>
            <?php else: ?>-<?php endif; ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
</body>
</html>