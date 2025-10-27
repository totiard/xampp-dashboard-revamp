<?php
date_default_timezone_set('Asia/Jakarta');

define('CACHE_FILE', '_dashboard_cache.json');

$ignore_list = [
    '.',
    '..',
    'dashboard',
    'img',
    'webalizer',
    'xampp',
    basename(__FILE__),
    basename(CACHE_FILE),
    'desktop.ini',
    'node_modules',
    '.git',
    '.vscode',
];

function getDirSize($dir)
{
    $size = 0;
    try {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));
        foreach ($iterator as $file) {
            if ($file->isReadable()) {
                $size += $file->getSize();
            }
        }
    } catch (Exception $e) {
        $size = 0;
    }
    return $size;
}

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

$cache = @file_exists(CACHE_FILE) ? json_decode(file_get_contents(CACHE_FILE), true) : [];
$projects = [];
$needs_cache_update = false;
$current_dir = '.';

$files = scandir($current_dir);

foreach ($files as $file) {
    if (in_array($file, $ignore_list) || !is_dir($file)) {
        continue;
    }

    $path = $current_dir . '/' . $file;
    $mtime = filemtime($path);

    if (!isset($cache[$file]) || $cache[$file]['mtime'] != $mtime) {
        $size = getDirSize($path);
        $cache[$file] = ['mtime' => $mtime, 'size' => $size];
        $needs_cache_update = true;
    } else {
        $size = $cache[$file]['size'];
    }

    $projects[] = [
        'name' => $file,
        'mtime' => $mtime,
        'size' => $size,
        'mtime_formatted' => date('d M Y, H:i', $mtime),
        'size_formatted' => formatSize($size),
    ];
}

usort($projects, function ($a, $b) {
    return $b['mtime'] - $a['mtime'];
});

if ($needs_cache_update) {
    @file_put_contents(CACHE_FILE, json_encode($cache, JSON_PRETTY_PRINT));
}

$current_path = realpath($current_dir);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Local Projects</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
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

        i.fas {
            font-size: 0.9em;
            width: 16px;
            text-align: center;
        }

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

        .controls {
            padding: 24px 32px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            border-bottom: 1px solid var(--color-border);
            background-color: rgba(0, 0, 0, 0.1);
            justify-content: space-between;
            align-items: center;
        }

        .search-bar {
            flex: 1 1 300px;
            position: relative;
            max-width: 450px;
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
            flex-shrink: 0;
            display: flex;
            gap: 8px;
        }

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

        .project-content {
            padding: 32px;
        }

        #projectGrid {
            display: grid;
            gap: 20px;
        }
        
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
            font-size: 20px;
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
            margin-top: auto;
            font-size: 13px;
        }
        .grid-view .item-meta-size {
            font-size: 13px;
        }
        .grid-view .project-list-header {
            display: none;
        }

        .list-view #projectGrid {
            display: grid;
            gap: 4px;
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
            grid-template-columns: 3fr 1fr 1fr;
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
            font-size: 1em;
        }
        .list-view .item-icon i.fas {
            font-size: 1em;
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
        .list-view .item-meta-modified i.fas,
        .list-view .item-meta-size i.fas {
            display: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: var(--color-text-secondary);
            grid-column: 1 / -1;
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

        .footer {
            text-align: center;
            padding: 24px;
            font-size: 13px;
            color: var(--color-text-secondary);
        }

        .neon-link {
            color: #00d9ff;
            text-decoration: none;
            text-shadow: 
                0 0 5px #00d9ff,
                0 0 10px #00d9ff,
                0 0 15px #00d9ff;
            transition: all 0.3s ease-in-out;
        }

        .neon-link:hover {
            color: #fff;
            text-shadow: 
                0 0 10px #00d9ff,
                0 0 20px #00d9ff,
                0 0 30px #00d9ff;
        }

        @media (max-width: 768px) {
            body { padding: 20px 10px; }
            .header { padding: 20px; flex-direction: column; align-items: stretch; }
            .controls { padding: 20px; }
            .project-content { padding: 20px; }
            
            .grid-view #projectGrid { grid-template-columns: 1fr; }
            
            .list-view .project-list-header { display: none; }
            
            .list-view .project-item {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 16px;
                border: 1px solid var(--color-border);
            }
            .list-view .item-meta-modified i.fas,
            .list-view .item-meta-size i.fas {
                display: inline-block;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <header class="header">
            <div class="header-title">
                <h1>My Local Projects</h1>
                <p><?php echo htmlspecialchars($current_path); ?></p>
            </div>
            <div class="quick-links">
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

        <div class="controls">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari proyek...">
            </div>
            <div class="control-group">
                <select id="sortSelect" class="sort-select" aria-label="Urutkan proyek">
                    <option value="mtime-desc">Terbaru</option>
                    <option value="mtime-asc">Terlama</option>
                    <option value="name-asc">Nama (A-Z)</option>
                    <option value="name-desc">Nama (Z-A)</option>
                    <option value="size-desc">Ukuran (Besar-Kecil)</option>
                    <option value="size-asc">Ukuran (Kecil-Besar)</option>
                </select>
                <div class="view-toggle">
                    <button type="button" id="gridViewBtn" class="active" aria-label="Tampilan Grid">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" id="listViewBtn" aria-label="Tampilan Daftar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <main class="project-content grid-view" id="projectContainer">
            
            <div class="project-list-header">
                <div>Nama Proyek</div>
                <div>Terakhir Diubah</div>
                <div>Ukuran</div>
            </div>

            <div id="projectGrid">
                <?php if (empty($projects)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>Tidak Ada Proyek</h3>
                        <p>Buat folder proyek baru di dalam <code><?php echo htmlspecialchars($current_path); ?></code></p>
                    </div>
                <?php endif; ?>

                <?php
                ?>
                <?php foreach ($projects as $project): ?>
                    <a href="<?php echo rawurlencode($project['name']); ?>/" 
                       target="_blank"
                       class="project-item" 
                       data-name="<?php echo strtolower(htmlspecialchars($project['name'])); ?>"
                       data-mtime="<?php echo $project['mtime']; ?>"
                       data-size="<?php echo $project['size']; ?>">
                        
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
                            <span><?php echo $project['size_formatted']; ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
        </main>
    </div>

    <footer class="footer">
        Crafted with <i class="fas fa-mug-hot" style="color: #e0ac7a;"></i> by 
        <a href="https://totiard.github.io/Profile-New" target="_blank" rel="noopener noreferrer" class="neon-link">Toti Ardiansyah</a> 
        &copy; 2025
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const sortSelect = document.getElementById('sortSelect');
            const projectContainer = document.getElementById('projectContainer');
            const projectGrid = document.getElementById('projectGrid');
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');
            
            const projectItems = Array.from(projectGrid.querySelectorAll('.project-item'));

            function filterProjects() {
                const filter = searchInput.value.toLowerCase();
                
                projectItems.forEach(item => {
                    const name = item.dataset.name;
                    if (name.includes(filter)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
                const currentView = projectContainer.classList.contains('list-view') ? 'list' : 'grid';
                setView(currentView);
            }

            function sortProjects() {
                const sortValue = sortSelect.value;
                const [sortBy, sortDir] = sortValue.split('-');

                projectItems.sort((a, b) => {
                    let valA, valB;

                    if (sortBy === 'name') {
                        valA = a.dataset.name;
                        valB = b.dataset.name;
                    } else {
                        valA = parseInt(a.dataset[sortBy], 10);
                        valB = parseInt(b.dataset[sortBy], 10);
                    }

                    let comparison = 0;
                    if (valA > valB) {
                        comparison = 1;
                    } else if (valA < valB) {
                        comparison = -1;
                    }

                    return (sortDir === 'desc') ? (comparison * -1) : comparison;
                });

                projectItems.forEach(item => projectGrid.appendChild(item));
            }

            function setView(view) {
                if (view === 'grid') {
                    projectContainer.classList.remove('list-view');
                    projectContainer.classList.add('grid-view');
                    gridViewBtn.classList.add('active');
                    listViewBtn.classList.remove('active');
                    projectGrid.style.display = 'grid';
                } else if (view === 'list') {
                    projectContainer.classList.remove('grid-view');
                    projectContainer.classList.add('list-view');
                    gridViewBtn.classList.remove('active');
                    listViewBtn.classList.add('active');
                    projectGrid.style.display = 'grid';
                }
            }

            searchInput.addEventListener('keyup', filterProjects);
            sortSelect.addEventListener('change', sortProjects);
            gridViewBtn.addEventListener('click', () => setView('grid'));
            listViewBtn.addEventListener('click', () => setView('list'));

        });
    </script>

</body>
</html>
