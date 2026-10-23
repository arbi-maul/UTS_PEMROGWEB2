<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0b1120">
<title>UTS PEMROGRAMAN 2</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#0b1120;color:#edf2fc;font-family:Arial,Helvetica,sans-serif;min-height:100vh;background-image:radial-gradient(ellipse at 90% 5%,#16334a88,transparent 45%),radial-gradient(ellipse at 5% 70%,#33215a66,transparent 45%)}a{color:inherit}.shell{max-width:1080px;margin:auto;padding:32px 24px}header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #283349;padding-bottom:24px;gap:16px}.brand{display:flex;align-items:center;gap:12px;font-size:14px;font-weight:bold;letter-spacing:1px}.mark{display:grid;place-items:center;width:42px;height:42px;background:#a78bfa;color:#131025;border-radius:12px;font-size:15px}.badge{border:1px solid #3d4860;color:#abb9d0;border-radius:30px;padding:9px 13px;font-size:12px}main{padding:68px 0 32px}.eyebrow{font-size:12px;color:#b6a3f8;letter-spacing:3px;font-weight:bold}h1{font-size:clamp(32px,6vw,60px);letter-spacing:-2px;line-height:1.12;margin:20px 0;max-width:760px}h1 span{color:#b69bf9}.intro{color:#a4b2c9;font-size:16px;line-height:1.8;max-width:560px;margin:0 0 34px}.identity{display:flex;gap:12px 26px;flex-wrap:wrap;margin-bottom:38px;color:#d6ddec;font-size:13px}.identity span{color:#8e9bb2;margin-right:7px}.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}.card{display:flex;flex-direction:column;padding:30px;border:1px solid #303b52;border-radius:20px;background:linear-gradient(135deg,#1b2439,#111b2c);text-decoration:none;transition:transform .2s,border-color .2s}.card:hover{transform:translateY(-5px);border-color:#b69bf9}.card:focus-visible{outline:3px solid #c4b5fd;outline-offset:5px}.top{display:flex;align-items:center;justify-content:space-between}.icon{width:55px;height:55px;display:grid;place-items:center;background:#a78bfa1c;border:1px solid #a78bfa40;border-radius:14px;color:#c4b5fd}.cyan .icon{color:#67e8f9;background:#22d3ee12;border-color:#22d3ee40}.number{color:#8391a8;font-size:12px;letter-spacing:2px}h2{font-size:25px;margin:26px 0 12px}.card p{font-size:14px;color:#a8b5cb;line-height:1.8;margin:0 0 26px}.tech{display:flex;gap:7px;flex-wrap:wrap;margin-top:auto}.tech span{color:#aebbd1;border:1px solid #364158;border-radius:6px;font-size:11px;padding:6px 9px}.open{display:flex;justify-content:space-between;border-top:1px solid #303b52;padding-top:20px;margin-top:26px;color:#c4b5fd;font-size:14px;font-weight:bold}.cyan .open{color:#67e8f9}footer{display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-top:38px;padding-top:22px;border-top:1px solid #283349;color:#8492aa;font-size:12px}@media(max-width:640px){.grid{grid-template-columns:1fr}main{padding-top:40px}header .badge{display:none}.card{padding:24px}h1{letter-spacing:-1px}.shell{padding:24px 18px}}@media(prefers-reduced-motion:reduce){.card{transition:none}}
</style>
</head>
<body>
<div class="shell">
<header><div class="brand"><span class="mark">P2</span>PEMROGRAMAN 2</div><span class="badge">Ujian Tengah Semester</span></header>
<main>
<div class="eyebrow">PORTAL APLIKASI</div>
<h1>UTS <span>PEMROGRAMAN 2</span></h1>
<p class="intro">Pilih aplikasi untuk mengelola biodata mahasiswa atau menampilkan data media sosial melalui integrasi Apify.</p>
<div class="identity"><div><span>Nama</span>Arbi Maulana</div><div><span>NIM</span>231011402268</div><div><span>Kelas</span>07TPLP032</div></div>
<div class="grid">
<a class="card" href="crud/index.php">
<div class="top"><span class="icon"><svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 4 16 4 16 0V5M4 12c0 4 16 4 16 0"/></svg></span><span class="number">01 / APLIKASI</span></div>
<h2>CRUD</h2><p>Tambah, lihat, ubah, dan hapus biodata mahasiswa yang terhubung dengan Google Sheets.</p>
<div class="tech"><span>PHP</span><span>Google Sheets</span><span>Apps Script</span></div><div class="open">Buka CRUD <span aria-hidden="true">&#8599;</span></div>
</a>
<a class="card cyan" href="apify/index.php">
<div class="top"><span class="icon"><svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="6" height="6" rx="1.5"/><rect x="15" y="15" width="6" height="6" rx="1.5"/><path d="M9 6h6a3 3 0 0 1 3 3v6M15 18H9a3 3 0 0 1-3-3V9"/></svg></span><span class="number">02 / APLIKASI</span></div>
<h2>Apify Integrasi</h2><p>Tampilkan nama akun, isi komentar, sentimen, dan link sumber TikTok terkait turunnya IHSG.</p>
<div class="tech"><span>PHP</span><span>Apify API</span><span>TikTok</span></div><div class="open">Buka Apify Integrasi <span aria-hidden="true">&#8599;</span></div>
</a>
</div>
</main>
<footer><span>Arbi Maulana &middot; 231011402268</span><span>UTS PEMROGRAMAN 2</span></footer>
</div>
</body>
</html>
