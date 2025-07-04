# Melin CRM - Fuarcılık ve Organizasyon Yönetim Sistemi

Modern ve kullanıcı dostu bir CRM sistemi. Fuar organizasyonları, firma yönetimi, teklif süreçleri ve e-posta otomasyonu için geliştirilmiştir.

## 🚀 Özellikler

### 📊 Dashboard
- Gerçek zamanlı istatistikler
- Aylık performans grafikleri
- Son aktiviteler
- Teklif durumları

### 🏢 Fuar Yönetimi
- Fuar ekleme, düzenleme, silme
- Fuar detayları ve kategorileri
- Firma ve teklif görüntüleme
- Durum takibi

### 🏭 Firma Yönetimi
- Firma bilgileri ve iletişim detayları
- Özelleştirilebilir durum sistemi
- E-posta ve WhatsApp entegrasyonu
- Dosya yükleme desteği

### 📋 Teklif Sistemi
- Stand projesi, hostes & catering, şirket tanıtımı
- E-posta şablonları ile otomatik gönderim
- WhatsApp Web entegrasyonu
- Dosya ekleme ve yönetimi
- Personel imzası otomatik ekleme

### 📧 E-posta Şablonları
- Özelleştirilebilir şablonlar
- Değişken desteği ({company_name}, {contact_person}, vb.)
- E-posta ve WhatsApp şablonları
- Kategori bazlı organizasyon

### 👥 Personel Yönetimi
- Personel bilgileri ve rolleri
- SMTP ayarları
- İmza yönetimi (JPG desteği)
- İstatistik takibi
- Performans raporları

### ⚙️ Ayarlar
- Profil yönetimi
- Şirket bilgileri
- SMTP konfigürasyonu
- Logo ve imza yükleme
- Tema ayarları

## 🛠️ Teknolojiler

- **Frontend**: React 18, TypeScript, Tailwind CSS
- **UI/UX**: Framer Motion, Lucide Icons
- **Backend**: Supabase (PostgreSQL)
- **Grafik**: Recharts
- **Form**: React Hook Form
- **Bildirim**: React Hot Toast
- **Tarih**: date-fns

## 📦 Kurulum

### Hızlı Kurulum (Hosting)

1. Tüm dosyaları hosting'inize yükleyin
2. `install.php` dosyasını çalıştırın
3. Veritabanı bilgilerini girin
4. Admin hesabını oluşturun
5. Kurulum tamamlandıktan sonra `install.php` dosyasını silin

### Manuel Kurulum

1. **Depoyu klonlayın**
```bash
git clone https://github.com/your-repo/melin-crm.git
cd melin-crm
```

2. **Bağımlılıkları yükleyin**
```bash
npm install
```

3. **Çevre değişkenlerini ayarlayın**
```bash
cp .env.example .env
# .env dosyasını düzenleyin
```

4. **Supabase veritabanını kurun**
```bash
# Supabase projenizi oluşturun
# Migration dosyalarını çalıştırın
```

5. **Geliştirme sunucusunu başlatın**
```bash
npm run dev
```

## 🔧 Konfigürasyon

### Veritabanı Ayarları

```env
VITE_SUPABASE_URL=your_supabase_url
VITE_SUPABASE_ANON_KEY=your_supabase_anon_key
```

### Admin Giriş Bilgileri

```
E-posta: info@melindizayn.com.tr
Şifre: 270381Fb*
```

## 📱 Özellik Detayları

### WhatsApp Entegrasyonu
- WhatsApp Web otomatik açılır
- Özelleştirilebilir mesaj şablonları
- Firma bilgileri otomatik doldurulur

### E-posta Sistemi
- SMTP ayarları ile otomatik gönderim
- Personel imzası otomatik eklenir
- Dosya ekleme desteği
- Şablon sistemi

### Dosya Yönetimi
- Drag & drop desteği
- Çoklu dosya yükleme
- Dosya boyutu gösterimi
- Güvenli dosya işleme

### Responsive Tasarım
- Mobil uyumlu
- Tablet desteği
- Modern UI/UX
- Dark mode desteği

## 🔒 Güvenlik

- Row Level Security (RLS)
- JWT token authentication
- CSRF koruması
- XSS koruması
- SQL injection koruması

## 📊 Veritabanı Şeması

### Ana Tablolar
- `users` - Kullanıcı yönetimi
- `exhibitions` - Fuar bilgileri
- `companies` - Firma kayıtları
- `quotations` - Teklif yönetimi
- `email_templates` - E-posta şablonları
- `personnel` - Personel bilgileri
- `settings` - Sistem ayarları
- `activity_logs` - Aktivite kayıtları

## 🚀 Production Deployment

### Netlify
```bash
npm run build
# dist klasörünü Netlify'a yükleyin
```

### Vercel
```bash
npm run build
vercel --prod
```

### Hosting (cPanel)
1. `npm run build` çalıştırın
2. `dist` klasörünün içeriğini public_html'e yükleyin
3. `.env` dosyasını ayarlayın

## 🔄 Güncellemeler

### v1.0.0
- ✅ Temel CRM fonksiyonları
- ✅ Fuar yönetimi
- ✅ Firma yönetimi
- ✅ Teklif sistemi
- ✅ E-posta entegrasyonu
- ✅ WhatsApp entegrasyonu
- ✅ Personel yönetimi
- ✅ Responsive tasarım

## 📞 Destek

Herhangi bir sorun yaşarsanız:

- **E-posta**: info@melindizayn.com.tr
- **Telefon**: +90 212 555 0000
- **Website**: www.melindizayn.com.tr

## 📄 Lisans

Bu proje Melin Dizayn için özel olarak geliştirilmiştir.

---

**Melin CRM** - Fuarcılık ve Organizasyon Yönetiminde Yeni Nesil Çözüm 🎯