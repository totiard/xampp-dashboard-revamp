<?php
// REVISI: Mengatur zona waktu default ke WIB (GMT+7)
date_default_timezone_set('Asia/Jakarta');

// --- PENGATURAN & KONFIGURASI ---

// File untuk menyimpan cache ukuran folder agar loading lebih cepat
define('CACHE_FILE', '_dashboard_cache.json');

// REVISI: Logika refresh cache dihapus
// ... (blok refresh dihapus) ...

// Abaikan folder/file ini dari daftar
$ignore_list = [
    '.',
    '..',
    'dashboard', // Folder XAMPP bawaan
    'img', // Folder XAMPP bawaan
    'webalizer', // Folder XAMPP bawaan
    'xampp', // Folder XAMPP bawaan
    basename(__FILE__), // Sembunyikan file ini
    basename(CACHE_FILE), // Sembunyikan file cache
    'desktop.ini',
    'node_modules', // Opsional: Sembunyikan node_modules dari daftar utama
    '.git',
    '.vscode',
];

// --- FUNGSI HELPER ---

/**
 * Menghitung total ukuran folder secara rekursif.
 * Dibuat lebih aman dengan try-catch.
 * @param string $dir Path ke direktori
 * @return int Ukuran dalam bytes
 */
function getDirSize($dir)
{
    $size = 0;
    try {
        // Gunakan FilesystemIterator::SKIP_DOTS untuk melewati '.' dan '..' secara otomatis
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));
        foreach ($iterator as $file) {
            if ($file->isReadable()) {
                $size += $file->getSize();
            }
        }
    } catch (Exception $e) {
        // Abaikan file/folder yang tidak bisa dibaca (permission issue)
        $size = 0; // Set ke 0 jika ada error
    }
    return $size;
}

/**
 * Memformat ukuran file dari bytes menjadi unit yang mudah dibaca.
 * @param int $bytes Ukuran dalam bytes
 * @return string Ukuran yang diformat (KB, MB, GB)
 */
function formatSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

// --- LOGIKA UTAMA (Caching & Pengumpulan Data) ---

// 1. Muat Cache yang Ada (jika ada)
$cache = @file_exists(CACHE_FILE) ? json_decode(file_get_contents(CACHE_FILE), true) : [];
$projects = [];
$needs_cache_update = false;
$current_dir = '.';

// 2. Pindai Direktori
$files = scandir($current_dir);

foreach ($files as $file) {
    // Lewati jika ada di daftar ignore atau bukan direktori
    if (in_array($file, $ignore_list) || !is_dir($file)) {
        continue;
    }

    $path = $current_dir . '/' . $file;
    $mtime = filemtime($path); // Waktu modifikasi terakhir

    // 3. Logika Caching Cerdas
    // Cek cache: Apakah folder ini tidak ada di cache ATAU waktu modifikasinya berubah?
    if (!isset($cache[$file]) || $cache[$file]['mtime'] != $mtime) {
        // Ya, hitung ulang ukurannya (proses lambat)
        $size = getDirSize($path);
        // Perbarui cache
        $cache[$file] = ['mtime' => $mtime, 'size' => $size];
        $needs_cache_update = true;
    } else {
        // Tidak, gunakan ukuran dari cache (proses cepat)
        $size = $cache[$file]['size'];
    }

    // 4. Tambahkan ke daftar proyek
    $projects[] = [
        'name' => $file,
        'mtime' => $mtime,
        'size' => $size,
        'mtime_formatted' => date('d M Y, H:i', $mtime),
        'size_formatted' => formatSize($size),
    ];
}

// 5. Urutkan default berdasarkan 'Terbaru' (waktu modifikasi)
// Kita lakukan di PHP agar tampilan awal sudah terurut
usort($projects, function ($a, $b) {
    return $b['mtime'] - $a['mtime']; // Descending (terbaru dulu)
});

// 6. Simpan cache baru jika ada perubahan
if ($needs_cache_update) {
    @file_put_contents(CACHE_FILE, json_encode($cache, JSON_PRETTY_PRINT));
}

// 7. Tampilkan path saat ini
$current_path = realpath($current_dir);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Local Projects</title>
    <!-- Memuat font Inter dari Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- REVISI: Menggunakan Font Awesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" xintegrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* --- Reset & Root Variables --- */
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --color-bg: #0D1117;
            --color-container-bg: rgba(22, 27, 34, 0.8);
            --color-border: rgba(255, 255, 255, 0.1);
            --color-text-primary: #E6EDF3;
            --color-text-secondary: #8B949E;
            --color-text-accent: #C9D1D9;
            --color-accent-blue: #58A6FF;
            --color-accent-purple: #BC8EFF;
            --color-input-bg: #010409;
            --color-input-border: #30363D;
            --shadow-medium: 0 8px 24px rgba(0, 0, 0, 0.2);
            --transition-fast: all 0.2s ease-in-out;
        }

        /* --- Efek Latar Belakang Aurora --- */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -2;
            background: linear-gradient(180deg, var(--color-bg) 0%, #000 100%);
        }

        body::after {
            content: "";
            position: fixed;
            top: -50vh;
            left: -50vw;
            width: 200vw;
            height: 200vh;
            z-index: -1;
            background:
                radial-gradient(circle at 20% 30%, var(--color-accent-blue) 0%, transparent 15%),
                radial-gradient(circle at 80% 70%, var(--color-accent-purple) 0%, transparent 15%);
            filter: blur(120px);
            opacity: 0.15;
            animation: pulse 15s infinite alternate;
        }

        @keyframes pulse {
            0% { transform: scale(0.9) rotate(0deg); opacity: 0.1; }
            100% { transform: scale(1.1) rotate(5deg); opacity: 0.2; }
        }

        /* --- Gaya Dasar --- */
        body {
            font-family: var(--font-sans);
            background-color: var(--color-bg);
            color: var(--color-text-primary);
            margin: 0;
            padding: 40px 20px 0px 20px;
        }

        a {
            color: var(--color-accent-blue);
            text-decoration: none;
            transition: var(--transition-fast);
        }
        a:hover { color: #79C0FF; }

        /* REVISI: Atur ukuran Font Awesome default */
        i.fas {
            font-size: 0.9em;
            width: 16px; /* Beri lebar agar sejajar */
            text-align: center;
        }

        /* --- Kontainer Utama (Efek Glassmorphism) --- */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--color-container-bg);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            overflow: hidden;
        }

        /* --- Header --- */
        .header {
            padding: 24px 32px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        .header-title h1 {
            margin: 0;
            font-size: 24px;
            color: var(--color-text-primary);
        }
        .header-title p {
            margin: 4px 0 0;
            font-size: 14px;
            color: var(--color-text-secondary);
            font-family: monospace;
        }

        .quick-links {
            display: flex;
            gap: 12px;
        }
        .quick-links a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--color-border);
            color: var(--color-text-accent);
            font-size: 14px;
            font-weight: 500;
        }
        .quick-links a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* --- Kontrol (Filter, Sort, View) --- */
        .controls {
            padding: 24px 32px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            border-bottom: 1px solid var(--color-border);
            background-color: rgba(0, 0, 0, 0.1);
            justify-content: space-between; /* REVISI: Dorong item ke kiri & kanan */
            align-items: center; /* REVISI: Sejajarkan secara vertikal */
        }

        /* REVISI: Perbaikan layout search bar & control group */
        .search-bar {
            flex: 1 1 300px; /* flex-grow, flex-shrink, flex-basis */
            position: relative;
            max-width: 450px; /* REVISI: Perkecil lagi lebar maksimum search bar */
        }
        .search-bar i.fas {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-text-secondary);
        }
        .search-bar input {
            width: 100%;
            padding: 10px 12px 10px 40px;
            background-color: var(--color-input-bg);
            border: 1px solid var(--color-input-border);
            border-radius: 8px;
            color: var(--color-text-primary);
            font-size: 14px;
            transition: var(--transition-fast);
        }
        .search-bar input:focus {
            outline: none;
            border-color: var(--color-accent-blue);
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.3);
        }

        .control-group {
            flex-shrink: 0; /* Mencegah grup kontrol mengecil */
            display: flex;
            gap: 8px;
        }

        /* REVISI: Tambahan style untuk tombol Refresh */
        .control-button, .sort-select, .view-toggle button {
            padding: 10px 16px;
            background-color: var(--color-input-bg);
            border: 1px solid var(--color-input-border);
            border-radius: 8px;
            color: var(--color-text-accent);
            font-size: 14px;
            font-family: var(--font-sans);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        .control-button {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .control-button:hover {
            border-color: var(--color-accent-blue);
            color: var(--color-accent-blue);
        }
        
        .sort-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%238B949E' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        .view-toggle button {
            background-color: transparent;
            opacity: 0.6;
        }
        .view-toggle button.active {
            background-color: var(--color-accent-blue);
            color: #FFF;
            border-color: var(--color-accent-blue);
            opacity: 1;
        }

        /* --- Konten Proyek (Grid & List) --- */
        .project-content {
            padding: 32px;
        }

        /* REVISI: Kontainer #projectGrid akan diubah oleh JS */
        #projectGrid {
            display: grid;
            gap: 20px;
        }
        
        /* REVISI: Struktur HTML item disatukan. CSS akan menatanya. */
        .project-item {
            color: var(--color-text-primary);
            transition: var(--transition-fast);
        }
        .project-item:hover {
            color: var(--color-text-primary);
        }

        .item-icon {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .item-icon i.fas {
            font-size: 20px; /* Ukuran ikon folder besar */
            color: var(--color-accent-blue);
            width: 24px;
        }
        .item-name {
            font-weight: 600;
            word-break: break-all;
        }
        .item-meta-modified, .item-meta-size {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--color-text-secondary);
        }
        
        /* === Tampilan GRID === */
        .grid-view #projectGrid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
        .grid-view .project-item {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--color-border);
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .grid-view .project-item:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .grid-view .item-name {
            font-size: 18px;
        }
        .grid-view .item-name:hover {
            text-decoration: underline;
        }
        .grid-view .item-meta-modified {
            margin-top: auto; /* Mendorong meta ke bawah */
            font-size: 13px;
        }
        .grid-view .item-meta-size {
            font-size: 13px;
        }
        .grid-view .project-list-header {
            display: none; /* Sembunyikan header list */
        }

        /* === Tampilan LIST === */
        .list-view #projectGrid {
            display: grid;
            gap: 4px; /* Gap lebih kecil untuk list */
        }
        .list-view .project-list-header {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr;
            gap: 16px;
            padding: 0 20px 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--color-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--color-border);
        }
        .list-view .project-item {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr; /* Name | Modified | Size */
            gap: 16px;
            padding: 16px 20px;
            border-radius: 8px;
            border: 1px solid transparent;
            align-items: center;
        }
        .list-view .project-item:hover {
            background-color: rgba(255, 255, 255, 0.03);
            border-color: var(--color-border);
        }
        .list-view .item-icon {
            font-size: 1em; /* Ukuran normal */
        }
        .list-view .item-icon i.fas {
            font-size: 1em; /* Ukuran normal */
            width: 16px;
        }
        .list-view .item-name {
            font-weight: 500;
        }
         .list-view .item-name:hover {
            text-decoration: underline;
        }
        .list-view .item-meta-modified,
        .list-view .item-meta-size {
            font-size: 14px;
        }
        /* Sembunyikan ikon di dalam meta list, karena sudah ada header */
        .list-view .item-meta-modified i.fas,
        .list-view .item-meta-size i.fas {
            display: none;
        }
        
        /* State Kosong (jika tidak ada proyek) */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: var(--color-text-secondary);
            grid-column: 1 / -1; /* Agar terentang penuh */
        }
        .empty-state i.fas {
            font-size: 48px;
            opacity: 0.5;
            margin-bottom: 16px;
        }
        .empty-state h3 {
            margin: 0 0 8px 0;
            color: var(--color-text-primary);
            font-size: 20px;
        }

        /* --- Footer --- */
        .footer {
            text-align: center;
            padding: 24px;
            font-size: 13px;
            color: var(--color-text-secondary);
        }

        /* -- Gaya Neon untuk Link di Footer -- */
        .neon-link {
            color: #00d9ff; /* Warna teks neon (biru cyan) */
            text-decoration: none; /* Hilangkan garis bawah */
            text-shadow: 
                0 0 5px #00d9ff,
                0 0 10px #00d9ff,
                0 0 15px #00d9ff;
            transition: all 0.3s ease-in-out; /* Animasi halus */
        }

        .neon-link:hover {
            color: #fff; /* Ubah jadi putih pas di-hover */
            text-shadow: 
                0 0 10px #00d9ff,
                0 0 20px #00d9ff,
                0 0 30px #00d9ff; /* Bikin glow-nya lebih kuat */
        }

        /* --- Media Queries untuk Responsif --- */
        @media (max-width: 768px) {
            body { padding: 20px 10px; }
            .header { padding: 20px; flex-direction: column; align-items: stretch; }
            .controls { padding: 20px; }
            .project-content { padding: 20px; }
            
            /* Tampilan Grid di mobile = 1 kolom */
            .grid-view #projectGrid { grid-template-columns: 1fr; }
            
            /* Tampilan List di mobile = menumpuk */
            .list-view .project-list-header { display: none; } /* Sembunyikan header tabel */
            
            .list-view .project-item {
                grid-template-columns: 1fr; /* Tumpuk semua */
                gap: 12px;
                padding: 16px;
                border: 1px solid var(--color-border);
            }
            /* Tampilkan kembali ikon meta di mobile */
            .list-view .item-meta-modified i.fas,
            .list-view .item-meta-size i.fas {
                display: inline-block;
            }
        }
    </style>
</head>
<body>

    <!-- Kontainer Utama -->
    <div class="container">
        
        <!-- Header -->
        <header class="header">
            <div class="header-title">
                <h1>My Local Projects</h1>
                <p><?php echo htmlspecialchars($current_path); ?></p>
            </div>
            <div class="quick-links">
                <!-- REVISI: Menggunakan Font Awesome -->
                <a href="/phpmyadmin/" target="_blank">
                    <i class="fas fa-database"></i>
                    <span>phpMyAdmin</span>
                </a>
                <a href="/dashboard/phpinfo.php" target="_blank">
                    <i class="fas fa-info-circle"></i>
                    <span>PHP Info</span>
                </a>
            </div>
        </header>

        <!-- Kontrol -->
        <div class="controls">
            <div class="search-bar">
                <!-- REVISI: Menggunakan Font Awesome -->
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari proyek...">
            </div>
            <div class="control-group">
                <!-- REVISI: Tombol Refresh dihapus -->
                <select id="sortSelect" class="sort-select" aria-label="Urutkan proyek">
                    <option value="mtime-desc">Terbaru</option>
                    <option value="mtime-asc">Terlama</option>
                    <option value="name-asc">Nama (A-Z)</option>
                    <option value="name-desc">Nama (Z-A)</option>
                    <option value="size-desc">Ukuran (Besar-Kecil)</option>
                    <option value="size-asc">Ukuran (Kecil-Besar)</option>
                </select>
                <div class="view-toggle">
                    <!-- REVISI: Menggunakan Font Awesome -->
                    <button type="button" id="gridViewBtn" class="active" aria-label="Tampilan Grid">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" id="listViewBtn" aria-label="Tampilan Daftar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Konten Proyek -->
        <main class="project-content grid-view" id="projectContainer">
            
            <!-- Header Tampilan List (default tersembunyi) -->
            <div class="project-list-header">
                <div>Nama Proyek</div>
                <div>Terakhir Diubah</div>
                <div>Ukuran</div>
            </div>

            <!-- Daftar Proyek (akan diisi oleh PHP) -->
            <div id="projectGrid">
                <?php if (empty($projects)): ?>
                    <!-- Tampilan Jika Kosong -->
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>Tidak Ada Proyek</h3>
                        <p>Buat folder proyek baru di dalam <code><?php echo htmlspecialchars($current_path); ?></code></p>
                    </div>
                <?php endif; ?>

                <?php 
                // REVISI: Struktur Loop Diperbaiki Total
                // Sekarang hanya ada satu item per proyek, CSS akan menatanya
                ?>
                <?php foreach ($projects as $project): ?>
                    <a href="<?php echo rawurlencode($project['name']); ?>/" <?php // REVISI: Link folder di-encode dengan rawurlencode untuk menangani '#' ?>
                       target="_blank"
                       class="project-item" 
                       data-name="<?php echo strtolower(htmlspecialchars($project['name'])); ?>"
                       data-mtime="<?php echo $project['mtime']; ?>"
                       data-size="<?php echo $project['size']; ?>">
                        
                        <!-- Konten item universal -->
                        <div class="item-icon">
                            <i class="fas fa-folder"></i>
                            <span class="item-name"><?php echo htmlspecialchars($project['name']); ?></span>
                        </div>
                        
                        <div class="item-meta-modified">
                            <i class="fas fa-clock"></i>
                            <span><?php echo $project['mtime_formatted']; ?></span>
                        </div>
                        
                        <div class="item-meta-size">
                            <i class="fas fa-hdd"></i>
                            <span><?php echo $project['size_formatted']; // REVISI: Typo <?Vphp diperbaiki ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        Crafted with <i class="fas fa-mug-hot" style="color: #e0ac7a;"></i> by 
        <a href="https://totiard.github.io/Profile-New" target="_blank" rel="noopener noreferrer" class="neon-link">Toti Ardiansyah</a> 
        &copy; 2025
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Ambil Elemen DOM ---
            const searchInput = document.getElementById('searchInput');
            const sortSelect = document.getElementById('sortSelect');
            const projectContainer = document.getElementById('projectContainer');
            const projectGrid = document.getElementById('projectGrid');
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');
            
            // Simpan referensi ke semua item proyek
            const projectItems = Array.from(projectGrid.querySelectorAll('.project-item'));

            // --- Fungsi Filter Pencarian ---
            function filterProjects() {
                const filter = searchInput.value.toLowerCase();
                
                projectItems.forEach(item => {
                    // Cek data-name
                    const name = item.dataset.name;
                    if (name.includes(filter)) {
                        item.style.display = ''; // Tampilkan jika cocok
                    } else {
                        item.style.display = 'none'; // Sembunyikan jika tidak
                    }
                });
                // Sesuaikan 'display' dari grid container berdasarkan view
                // Ini penting agar 'display: none' dari item berfungsi
                const currentView = projectContainer.classList.contains('list-view') ? 'list' : 'grid';
                setView(currentView);
            }

            // --- Fungsi Sorting ---
            function sortProjects() {
                const sortValue = sortSelect.value;
                const [sortBy, sortDir] = sortValue.split('-'); // Cth: ['mtime', 'desc']

                projectItems.sort((a, b) => {
                    let valA, valB;

                    // Ambil nilai berdasarkan data-atribut
                    if (sortBy === 'name') {
                        valA = a.dataset.name;
                        valB = b.dataset.name;
                    } else {
                        // Untuk 'mtime' dan 'size', parsing sebagai angka
                        valA = parseInt(a.dataset[sortBy], 10);
                        valB = parseInt(b.dataset[sortBy], 10);
                    }

                    // Tentukan urutan
                    let comparison = 0;
                    if (valA > valB) {
                        comparison = 1;
                    } else if (valA < valB) {
                        comparison = -1;
                    }

                    return (sortDir === 'desc') ? (comparison * -1) : comparison;
                });

                // Susun ulang item di dalam grid
                projectItems.forEach(item => projectGrid.appendChild(item));
            }

            // --- Fungsi Ganti Tampilan ---
            function setView(view) {
                if (view === 'grid') {
                    projectContainer.classList.remove('list-view');
                    projectContainer.classList.add('grid-view');
                    gridViewBtn.classList.add('active');
                    listViewBtn.classList.remove('active');
                    // REVISI: Sesuaikan display container
                    projectGrid.style.display = 'grid';
                } else if (view === 'list') {
                    projectContainer.classList.remove('grid-view');
                    projectContainer.classList.add('list-view');
                    gridViewBtn.classList.remove('active');
                    listViewBtn.classList.add('active');
                    // REVISI: Sesuaikan display container
                    projectGrid.style.display = 'grid'; // Tetap grid untuk layout list
                }
            }

            // --- Pasang Event Listeners ---
            searchInput.addEventListener('keyup', filterProjects);
            sortSelect.addEventListener('change', sortProjects);
            gridViewBtn.addEventListener('click', () => setView('grid'));
            listViewBtn.addEventListener('click', () => setView('list'));

            // --- Inisialisasi ---
            // Tampilan awal sudah diurutkan oleh PHP (Terbaru)
            // Tampilan awal di set ke 'grid' oleh CSS
        });
    </script>

</body>
</html>
