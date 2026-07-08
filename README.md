# istedalis

istedalis, Iskenderun Teknik Universitesi Denizcilik Teknolojileri MYO icin dinamik dalis kayit sistemidir. Ogrenciler dalis kaydi girer, yetkili personel rapor alir, admin hesabi ise ogrenci verilerini manuel veya otomatik JSON/CSV yedekleri olarak indirebilir.

## Teknoloji

- Laravel 12
- PHP 8.2+
- SQLite yerel gelistirme, PostgreSQL Render uretim
- DomPDF ile ogrenci dalis raporu
- Render Docker web servisi, Render Postgres ve Render cron job

## Yerel kurulum

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Yerel varsayilan hesaplar:

| Rol | No | Sifre |
| --- | --- | --- |
| Ogrenci | 2024001 | 1234 |
| Ogrenci | 2024002 | 1234 |
| Staff | 1001 | admin123 |
| Staff | 2001 | dalis123 |
| Admin | 3001 | yonetici123 |

Uretimde varsayilan zayif sifre kullanilmaz. Render ortaminda `ADMIN_PASSWORD` mutlaka girilmelidir.

## Render deploy

1. Bu klasoru GitHub reposuna push edin.
2. Render Dashboard uzerinden `New > Blueprint` secin ve GitHub reposunu baglayin.
3. Render `render.yaml` dosyasini okuyup su kaynaklari olusturur:
   - `istedalis` Docker web servisi
   - `istedalis-db` PostgreSQL veritabani
   - `istedalis-storage` kalici disk
   - `istedalis-auto-backup` gunluk cron yedegi
4. Render sizden su gizli degerleri isteyecek:
   - `APP_KEY`: yerelde `php artisan key:generate --show` ile uretin.
   - `APP_URL`: Render servis adresi, ornek `https://istedalis.onrender.com`.
   - `ADMIN_PASSWORD`: admin hesabinin guclu sifresi.
5. Deploy tamamlandiginda migration calisir ve admin hesabi otomatik hazirlanir.

## Yedekleme

Admin hesabi `Staff Girisi` ekranindan giris yapar. `Yedekleme` ekraninda:

- Anlik JSON yedegi indirilebilir.
- Tum veriler, ogrenci listesi veya dalis kayitlari CSV indirilebilir.
- Manuel saklanan JSON yedegi olusturulabilir.
- Otomatik cron yedekleri indirilebilir.

Otomatik yedekleme her gun `0 0 * * *` UTC saatinde calisir. Bu Turkiye saatiyle 03:00'tur. Yedekler `storage/app/private/backups` altinda kalici diskte saklanir ve `BACKUP_RETENTION_DAYS` kadar tutulur.

## Guvenlik notlari

- `.env`, SQLite veritabani ve `storage/` icindeki ozel dosyalar Git ve Docker imajindan dislanir.
- Uretimde HTTPS zorlanir.
- Oturum cookie'leri Render'da `secure`, `encrypted` ve `same_site=strict` olarak ayarlanir.
- Login denemeleri rate limit ile sinirlanir.
- Yedekleme ekranlari sadece `role_name=admin` olan staff hesabina aciktir.
- Otomatik yedek endpoint'i `AUTO_BACKUP_TOKEN` ile korunur.
- Temel guvenlik header'lari uygulama seviyesinde eklenir.

## Faydali komutlar

```bash
php artisan admin:ensure --force
php artisan backup:students --type=manual
php artisan about-dalis
```
