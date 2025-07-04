<?php
require_once 'config.php';
requireLogin();

// Sistem bilgileri
$system_info = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Bilinmiyor',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Bilinmiyor',
    'disk_free' => disk_free_space('.') ? round(disk_free_space('.') / 1024 / 1024 / 1024, 2) . ' GB' : 'Bilinmiyor',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time') . ' saniye'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Dashboard</title>
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
        
        .logo h1 {
            font-size: 1.5rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .card-header i {
            font-size: 2rem;
            margin-right: 1rem;
            color: #667eea;
        }
        
        .card-header h3 {
            color: #333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .stat-card h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table th,
        .info-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .info-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .info-table td {
            color: #666;
        }
        
        .welcome-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .welcome-message h2 {
            margin-bottom: 0.5rem;
        }
        
        .quick-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .action-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .action-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <h1><i class="fas fa-tachometer-alt"></i> <?php echo SITE_NAME; ?></h1>
            </div>
            <div class="user-info">
                <span><i class="fas fa-user"></i> Hoş geldiniz, <?php echo $_SESSION['admin_username']; ?></span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2><i class="fas fa-rocket"></i> Yönetim Paneline Hoş Geldiniz!</h2>
            <p>SON-ARE yönetim sistemi başarıyla çalışıyor. Aşağıdaki menülerden istediğiniz işlemi gerçekleştirebilirsiniz.</p>
            <div class="quick-actions">
                <a href="files.php" class="action-btn">
                    <i class="fas fa-folder"></i> Dosya Yöneticisi
                </a>
                <a href="system.php" class="action-btn">
                    <i class="fas fa-cog"></i> Sistem Ayarları
                </a>
                <a href="logs.php" class="action-btn">
                    <i class="fas fa-list"></i> Loglar
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-server"></i>
                <h4>Sunucu Durumu</h4>
                <p>Aktif ve Çalışıyor</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h4>Çalışma Süresi</h4>
                <p><?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-memory"></i>
                <h4>Bellek Limiti</h4>
                <p><?php echo $system_info['memory_limit']; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-hdd"></i>
                <h4>Boş Disk Alanı</h4>
                <p><?php echo $system_info['disk_free']; ?></p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Sistem Bilgileri</h3>
                </div>
                <table class="info-table">
                    <tr>
                        <th>PHP Sürümü</th>
                        <td><?php echo $system_info['php_version']; ?></td>
                    </tr>
                    <tr>
                        <th>Sunucu Yazılımı</th>
                        <td><?php echo $system_info['server_software']; ?></td>
                    </tr>
                    <tr>
                        <th>Sunucu Adı</th>
                        <td><?php echo $system_info['server_name']; ?></td>
                    </tr>
                    <tr>
                        <th>Maksimum Çalışma Süresi</th>
                        <td><?php echo $system_info['max_execution_time']; ?></td>
                    </tr>
                </table>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Hızlı İstatistikler</h3>
                </div>
                <table class="info-table">
                    <tr>
                        <th>Toplam Dosya</th>
                        <td><?php echo count(glob('*')); ?> dosya</td>
                    </tr>
                    <tr>
                        <th>Son Giriş</th>
                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                    </tr>
                    <tr>
                        <th>Panel Sürümü</th>
                        <td>v<?php echo SITE_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th>Durum</th>
                        <td><span style="color: #28a745;">✓ Aktif</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>