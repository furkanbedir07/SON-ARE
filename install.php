<?php
/**
 * Melin CRM Kurulum Dosyası
 * 
 * Bu dosya Melin CRM sistemini hosting ortamınıza kurmak için kullanılır.
 * Kurulum tamamlandıktan sonra bu dosyayı güvenlik nedeniyle silin.
 */

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kurulum tamamlandı mı kontrol et
if (file_exists('.installed')) {
    die('Kurulum zaten tamamlanmış. Güvenlik için install.php dosyasını silin.');
}

// POST isteği geldi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al ve temizle
    $config = [
        'db_host' => isset($_POST['db_host']) ? trim($_POST['db_host']) : '',
        'db_name' => isset($_POST['db_name']) ? trim($_POST['db_name']) : '',
        'db_user' => isset($_POST['db_user']) ? trim($_POST['db_user']) : '',
        'db_pass' => isset($_POST['db_pass']) ? $_POST['db_pass'] : '', // Şifre boş olabilir
        'admin_email' => isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '',
        'admin_password' => isset($_POST['admin_password']) ? trim($_POST['admin_password']) : '',
        'site_url' => isset($_POST['site_url']) ? trim($_POST['site_url']) : ''
    ];
    
    // Debug için form verilerini kontrol et (geliştirme aşamasında)
    // echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
    
    // Zorunlu alanları kontrol et
    $required_fields = [
        'db_host' => 'Veritabanı Host',
        'db_name' => 'Veritabanı Adı', 
        'db_user' => 'Kullanıcı Adı',
        'admin_email' => 'Admin E-posta',
        'admin_password' => 'Admin Şifre',
        'site_url' => 'Site URL'
    ];
    
    $missing_fields = [];
    foreach ($required_fields as $field => $label) {
        if (empty($config[$field])) {
            $missing_fields[] = $label;
        }
    }
    
    if (!empty($missing_fields)) {
        $error = "Lütfen şu zorunlu alanları doldurun: " . implode(', ', $missing_fields);
    }
    
    // E-posta formatını kontrol et
    if (!isset($error) && !filter_var($config['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi girin.";
    }
    
    // URL formatını kontrol et
    if (!isset($error) && !filter_var($config['site_url'], FILTER_VALIDATE_URL)) {
        $error = "Geçerli bir URL girin (http:// veya https:// ile başlamalı).";
    }
    
    if (!isset($error)) {
        try {
            // Veritabanı bağlantısını test et
            $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_TIMEOUT => 10
            ];
            
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
            
            // .env dosyasını oluştur
            $envContent = "# Melin CRM Environment Variables
# Database Configuration
DB_HOST={$config['db_host']}
DB_NAME={$config['db_name']}
DB_USER={$config['db_user']}
DB_PASS={$config['db_pass']}

# Supabase Configuration (Production)
VITE_SUPABASE_URL=https://your-project.supabase.co
VITE_SUPABASE_ANON_KEY=your-anon-key

# Admin Configuration
ADMIN_EMAIL={$config['admin_email']}
ADMIN_PASSWORD={$config['admin_password']}

# Site Configuration
SITE_URL={$config['site_url']}
SITE_NAME=Melin CRM

# SMTP Configuration (Optional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SMTP_ENCRYPTION=TLS

# Security
JWT_SECRET=" . bin2hex(random_bytes(32)) . "
APP_KEY=" . bin2hex(random_bytes(16)) . "
";
            
            if (!file_put_contents('.env', $envContent)) {
                throw new Exception('.env dosyası oluşturulamadı. Yazma izinlerini kontrol edin.');
            }
            
            // Veritabanı tablolarını oluştur
            $sql = "
            -- Users table
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                phone VARCHAR(20),
                role ENUM('admin', 'user', 'sales', 'coordinator') DEFAULT 'user',
                smtp_host VARCHAR(255),
                smtp_port INT DEFAULT 587,
                smtp_username VARCHAR(255),
                smtp_password VARCHAR(255),
                signature_text TEXT,
                signature_image VARCHAR(255),
                signature_width INT DEFAULT 300,
                signature_height INT DEFAULT 80,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- Exhibition statuses table
            CREATE TABLE IF NOT EXISTS exhibition_statuses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                color VARCHAR(7) DEFAULT '#3B82F6',
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- Company statuses table
            CREATE TABLE IF NOT EXISTS company_statuses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                color VARCHAR(7) DEFAULT '#3B82F6',
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- Quotation statuses table
            CREATE TABLE IF NOT EXISTS quotation_statuses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                color VARCHAR(7) DEFAULT '#3B82F6',
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- Exhibitions table
            CREATE TABLE IF NOT EXISTS exhibitions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                date DATE NOT NULL,
                end_date DATE,
                category VARCHAR(100) NOT NULL,
                city VARCHAR(100) NOT NULL,
                venue VARCHAR(255),
                description TEXT,
                status_id INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (status_id) REFERENCES exhibition_statuses(id) ON DELETE SET NULL
            );

            -- Companies table
            CREATE TABLE IF NOT EXISTS companies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                exhibition_id INT,
                name VARCHAR(255) NOT NULL,
                contact_person VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                website VARCHAR(255),
                address TEXT,
                notes TEXT,
                status_id INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (exhibition_id) REFERENCES exhibitions(id) ON DELETE CASCADE,
                FOREIGN KEY (status_id) REFERENCES company_statuses(id) ON DELETE SET NULL
            );

            -- Quotations table
            CREATE TABLE IF NOT EXISTS quotations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT,
                title VARCHAR(255) NOT NULL,
                type VARCHAR(100) NOT NULL,
                amount DECIMAL(10,2) DEFAULT 0,
                currency VARCHAR(3) DEFAULT 'TRY',
                status_id INT DEFAULT 1,
                sent_date TIMESTAMP NULL,
                valid_until DATE,
                notes TEXT,
                attachments JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
                FOREIGN KEY (status_id) REFERENCES quotation_statuses(id) ON DELETE SET NULL
            );

            -- Email templates table
            CREATE TABLE IF NOT EXISTS email_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                subject VARCHAR(255),
                content TEXT NOT NULL,
                type ENUM('email', 'whatsapp') DEFAULT 'email',
                category VARCHAR(100) DEFAULT 'Genel',
                variables JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- Personnel table
            CREATE TABLE IF NOT EXISTS personnel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                phone VARCHAR(20),
                position VARCHAR(100),
                role VARCHAR(50) DEFAULT 'Personel',
                smtp_host VARCHAR(255),
                smtp_port INT DEFAULT 587,
                smtp_username VARCHAR(255),
                smtp_password VARCHAR(255),
                signature_text TEXT,
                signature_image VARCHAR(255),
                signature_width INT DEFAULT 300,
                signature_height INT DEFAULT 80,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- Settings table
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_name VARCHAR(255) DEFAULT 'Melin Dizayn',
                company_email VARCHAR(255) DEFAULT 'info@melindizayn.com.tr',
                company_phone VARCHAR(20),
                company_address TEXT,
                company_website VARCHAR(255),
                company_logo VARCHAR(255),
                tax_number VARCHAR(50),
                smtp_host VARCHAR(255),
                smtp_port INT DEFAULT 587,
                smtp_username VARCHAR(255),
                smtp_password VARCHAR(255),
                smtp_encryption VARCHAR(10) DEFAULT 'TLS',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );

            -- Activity logs table
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                entity_type VARCHAR(100),
                entity_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            );

            -- Uploaded files table
            CREATE TABLE IF NOT EXISTS uploaded_files (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size INT NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                category VARCHAR(100) DEFAULT 'Diğer',
                uploaded_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
            );
            ";
            
            // SQL komutlarını tek tek çalıştır
            $statements = explode(';', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Admin kullanıcısını oluştur
            $hashedPassword = password_hash($config['admin_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$config['admin_email'], $hashedPassword, 'Admin', 'User', 'admin']);
            
            // Varsayılan fuar durumlarını ekle
            $exhibitionStatuses = [
                ['Planlama', '#F59E0B', 'Planlama aşamasındaki fuar'],
                ['Aktif', '#10B981', 'Şu anda devam eden fuar'],
                ['Tamamlandı', '#3B82F6', 'Tamamlanmış fuar'],
                ['İptal', '#EF4444', 'İptal edilmiş fuar'],
                ['Ertelendi', '#8B5CF6', 'Ertelenmiş fuar']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO exhibition_statuses (name, color, description) VALUES (?, ?, ?)");
            foreach ($exhibitionStatuses as $status) {
                $stmt->execute($status);
            }
            
            // Varsayılan firma durumlarını ekle
            $companyStatuses = [
                ['Yeni', '#3B82F6', 'Yeni kayıt olan firma'],
                ['Teklif Gönderildi', '#F59E0B', 'Teklif gönderilmiş firma'],
                ['Görüşüldü', '#8B5CF6', 'Görüşme yapılan firma'],
                ['Arandı', '#06B6D4', 'Telefon ile aranan firma'],
                ['Teklif Olumlu', '#10B981', 'Teklifi olumlu karşılayan firma'],
                ['Geri Dönüş Bekleniyor', '#F97316', 'Geri dönüş beklenen firma'],
                ['Anlaşma Yapıldı', '#059669', 'Anlaşma yapılan firma'],
                ['İptal', '#EF4444', 'İptal olan firma']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO company_statuses (name, color, description) VALUES (?, ?, ?)");
            foreach ($companyStatuses as $status) {
                $stmt->execute($status);
            }
            
            // Varsayılan teklif durumlarını ekle
            $quotationStatuses = [
                ['Taslak', '#6B7280', 'Taslak halindeki teklif'],
                ['Gönderildi', '#3B82F6', 'Gönderilmiş teklif'],
                ['Görüldü', '#8B5CF6', 'Görülmüş teklif'],
                ['Değerlendiriliyor', '#F59E0B', 'Değerlendirme aşamasındaki teklif'],
                ['Onaylandı', '#10B981', 'Onaylanmış teklif'],
                ['Reddedildi', '#EF4444', 'Reddedilmiş teklif'],
                ['Revizyon Gerekli', '#F97316', 'Revizyon gereken teklif']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO quotation_statuses (name, color, description) VALUES (?, ?, ?)");
            foreach ($quotationStatuses as $status) {
                $stmt->execute($status);
            }
            
            // Varsayılan ayarları ekle
            $stmt = $pdo->prepare("INSERT INTO settings (company_name, company_email) VALUES (?, ?)");
            $stmt->execute(['Melin Dizayn', $config['admin_email']]);
            
            // Örnek e-posta şablonlarını ekle
            $templates = [
                [
                    'Stand Projesi Teklifi',
                    'Stand Projesi Teklifimiz - {company_name}',
                    'Sayın {contact_person},\n\n{exhibition_name} fuarı için hazırladığımız stand projesi teklifimizi ekte bulabilirsiniz.\n\nDetaylı bilgi için bizimle iletişime geçebilirsiniz.\n\nSaygılarımızla,\nMelin Dizayn',
                    'email',
                    'Teklif'
                ],
                [
                    'Hostes ve Catering Teklifi',
                    'Hostes ve Catering Hizmeti Teklifimiz - {company_name}',
                    'Merhaba {contact_person},\n\n{exhibition_name} fuarı için talep ettiğiniz hostes ve catering hizmeti teklifimizi hazırladık.\n\nDetaylar için ekteki dosyayı inceleyebilirsiniz.\n\nİyi günler,\nMelin Dizayn Ekibi',
                    'email',
                    'Teklif'
                ],
                [
                    'WhatsApp Genel Bilgi',
                    '',
                    'Merhaba {contact_person}, yapmış olduğumuz görüşmeye istinaden sizlere {exhibition_name} fuarı için teklifimizi mail olarak gönderdim. Buradan da sizlere bilgi vermek istedim. Sormanız veya yardıma ihtiyacınız olan herhangi bir durum olursa WhatsApp hattımızdan da bize ulaşabilirsiniz.\n\nMelin Dizayn\n📞 +90 212 555 0000',
                    'whatsapp',
                    'Bildirim'
                ]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO email_templates (name, subject, content, type, category) VALUES (?, ?, ?, ?, ?)");
            foreach ($templates as $template) {
                $stmt->execute($template);
            }
            
            // uploads klasörünü oluştur
            if (!is_dir('uploads')) {
                if (!mkdir('uploads', 0755, true)) {
                    throw new Exception('uploads klasörü oluşturulamadı. Yazma izinlerini kontrol edin.');
                }
            }
            
            // .htaccess dosyası oluştur (güvenlik için)
            $htaccessContent = "# Melin CRM Security Rules
RewriteEngine On

# Prevent access to sensitive files
<Files \".env\">
    Order allow,deny
    Deny from all
</Files>

<Files \".installed\">
    Order allow,deny
    Deny from all
</Files>

# Redirect all requests to index.html for SPA
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.html [L]
";
            file_put_contents('.htaccess', $htaccessContent);
            
            // Kurulum tamamlandı işaretini oluştur
            file_put_contents('.installed', date('Y-m-d H:i:s') . "\nAdmin: " . $config['admin_email']);
            
            $success = "Kurulum başarıyla tamamlandı! Artık sistemi kullanabilirsiniz.";
            
        } catch (PDOException $e) {
            $error = "Veritabanı bağlantı hatası: " . $e->getMessage();
        } catch (Exception $e) {
            $error = "Kurulum hatası: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melin CRM Kurulum</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 600px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #2563eb;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .form-group input:required {
            border-left: 4px solid #ef4444;
        }
        
        .form-group input:required:valid {
            border-left: 4px solid #10b981;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background: #1d4ed8;
        }
        
        .btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-box h3 {
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            color: #1e40af;
            padding-left: 20px;
        }
        
        .info-box li {
            margin-bottom: 5px;
        }
        
        .required {
            color: #ef4444;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Melin CRM</h1>
            <p>Fuarcılık ve Organizasyon Yönetim Sistemi</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <strong>✅ Başarılı!</strong> <?= htmlspecialchars($success) ?>
                <br><br>
                <strong>Giriş Bilgileri:</strong><br>
                E-posta: <?= htmlspecialchars($config['admin_email']) ?><br>
                Şifre: <?= htmlspecialchars($config['admin_password']) ?><br><br>
                <strong>⚠️ Güvenlik Uyarısı:</strong> Kurulum tamamlandıktan sonra install.php dosyasını silin!
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = '/';
                }, 5000);
            </script>
        <?php else: ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <strong>❌ Hata!</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>📋 Kurulum Öncesi Gereksinimler</h3>
                <ul>
                    <li>PHP 7.4 veya üzeri</li>
                    <li>MySQL 5.7 veya üzeri</li>
                    <li>PDO MySQL extension</li>
                    <li>Yazma izinleri (dosya oluşturma için)</li>
                </ul>
            </div>
            
            <form method="POST" id="installForm">
                <h3 style="margin-bottom: 20px; color: #374151;">🗄️ Veritabanı Ayarları</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="db_host">Veritabanı Host <span class="required">*</span></label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label for="db_name">Veritabanı Adı <span class="required">*</span></label>
                        <input type="text" id="db_name" name="db_name" value="melindizayn_crm" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="db_user">Kullanıcı Adı <span class="required">*</span></label>
                        <input type="text" id="db_user" name="db_user" value="melindizayn_crmuser" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Şifre</label>
                        <input type="password" id="db_pass" name="db_pass" value="270381Fb*">
                    </div>
                </div>
                
                <h3 style="margin: 30px 0 20px 0; color: #374151;">👤 Admin Hesabı</h3>
                
                <div class="form-group">
                    <label for="admin_email">Admin E-posta <span class="required">*</span></label>
                    <input type="email" id="admin_email" name="admin_email" value="info@melindizayn.com.tr" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Admin Şifre <span class="required">*</span></label>
                    <input type="password" id="admin_password" name="admin_password" value="270381Fb*" required>
                </div>
                
                <h3 style="margin: 30px 0 20px 0; color: #374151;">🌐 Site Ayarları</h3>
                
                <div class="form-group">
                    <label for="site_url">Site URL <span class="required">*</span></label>
                    <input type="url" id="site_url" name="site_url" value="https://crm.melindizayn.com.tr" required>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">🚀 Kurulumu Başlat</button>
            </form>
            
            <script>
                document.getElementById('installForm').addEventListener('submit', function() {
                    document.getElementById('submitBtn').disabled = true;
                    document.getElementById('submitBtn').textContent = '⏳ Kurulum yapılıyor...';
                });
            </script>
            
        <?php endif; ?>
    </div>
</body>
</html>