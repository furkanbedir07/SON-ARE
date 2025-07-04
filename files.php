<?php
require_once 'config.php';
requireLogin();

$current_dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
$current_dir = realpath($current_dir) ?: '.';

// Güvenlik kontrolü - sadece proje dizini içinde gezinmeye izin ver
$base_dir = realpath('.');
if (strpos($current_dir, $base_dir) !== 0) {
    $current_dir = $base_dir;
}

$files = [];
$dirs = [];

if (is_dir($current_dir)) {
    $items = scandir($current_dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $full_path = $current_dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($full_path)) {
            $dirs[] = [
                'name' => $item,
                'path' => $full_path,
                'size' => '-',
                'modified' => date('Y-m-d H:i:s', filemtime($full_path))
            ];
        } else {
            $files[] = [
                'name' => $item,
                'path' => $full_path,
                'size' => round(filesize($full_path) / 1024, 2) . ' KB',
                'modified' => date('Y-m-d H:i:s', filemtime($full_path))
            ];
        }
    }
}

// Dosya görüntüleme
$view_file = '';
if (isset($_GET['view']) && file_exists($_GET['view'])) {
    $view_file = $_GET['view'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dosya Yöneticisi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .breadcrumb {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .file-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .file-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .file-table th,
        .file-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .file-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .file-table tr:hover {
            background: #f8f9fa;
        }
        
        .file-icon {
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .file-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 3px;
            text-decoration: none;
            font-size: 0.8rem;
            cursor: pointer;
        }
        
        .btn-view {
            background: #007bff;
            color: white;
        }
        
        .btn-download {
            background: #28a745;
            color: white;
        }
        
        .file-viewer {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .file-content {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-folder"></i> Dosya Yöneticisi</h1>
            </div>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="breadcrumb">
            <i class="fas fa-folder-open"></i> 
            <strong>Konum:</strong> <?php echo htmlspecialchars($current_dir); ?>
        </div>

        <div class="file-table">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-file"></i> Dosya/Klasör</th>
                        <th><i class="fas fa-weight"></i> Boyut</th>
                        <th><i class="fas fa-clock"></i> Değiştirilme</th>
                        <th><i class="fas fa-cogs"></i> İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($current_dir !== $base_dir): ?>
                    <tr>
                        <td>
                            <i class="fas fa-level-up-alt file-icon"></i>
                            <a href="?dir=<?php echo urlencode(dirname($current_dir)); ?>">.. (Üst Klasör)</a>
                        </td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($dirs as $dir): ?>
                    <tr>
                        <td>
                            <i class="fas fa-folder file-icon" style="color: #ffc107;"></i>
                            <a href="?dir=<?php echo urlencode($dir['path']); ?>"><?php echo htmlspecialchars($dir['name']); ?></a>
                        </td>
                        <td><?php echo $dir['size']; ?></td>
                        <td><?php echo $dir['modified']; ?></td>
                        <td>-</td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td>
                            <i class="fas fa-file file-icon" style="color: #6c757d;"></i>
                            <?php echo htmlspecialchars($file['name']); ?>
                        </td>
                        <td><?php echo $file['size']; ?></td>
                        <td><?php echo $file['modified']; ?></td>
                        <td>
                            <div class="file-actions">
                                <a href="?view=<?php echo urlencode($file['path']); ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> Görüntüle
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($view_file): ?>
        <div class="file-viewer">
            <h3><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars(basename($view_file)); ?></h3>
            <div class="file-content">
<?php 
if (filesize($view_file) > 1024 * 1024) { // 1MB'dan büyükse
    echo "Dosya çok büyük, görüntülenemiyor.";
} else {
    echo htmlspecialchars(file_get_contents($view_file));
}
?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>