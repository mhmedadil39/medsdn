# MedSDN Installation Notes

## Current Status

عملية إعادة التسمية من Bagisto إلى MedSDN تمت بنجاح. المشروع جاهز للتثبيت والتشغيل.

## Known Issues & Solutions

### 1. PHP Extensions Warnings

**المشكلة:**
```
PHP Warning: Unable to load dynamic library 'mysqli'
PHP Warning: Unable to load dynamic library 'pdo_mysql'
```

**الحل:**
هذه تحذيرات فقط وليست أخطاء قاتلة. إذا كان المشروع مثبتاً مسبقاً ويعمل، يمكن تجاهلها. لإصلاحها بشكل كامل:

```bash
# على Ubuntu/Debian
sudo apt-get install php8.2-mysql php8.2-mysqli

# إعادة تشغيل PHP-FPM إذا كنت تستخدمه
sudo systemctl restart php8.2-fpm
```

### 2. Composer Dependencies

**المشكلة:**
تم تغيير اسم الحزمة في composer.json لكن composer.lock لم يتم تحديثه.

**الحل:**
تم إرجاع اسم الحزمة `bagisto/laravel-datafaker` للحفاظ على التوافق مع composer.lock الموجود.

## Installation Steps

### الخطوة 1: تثبيت Dependencies

```bash
# تثبيت PHP dependencies
composer install

# إذا واجهت مشاكل، استخدم:
composer install --ignore-platform-reqs
```

### الخطوة 2: إعداد البيئة

ملف `.env` موجود بالفعل مع الإعدادات التالية:
- APP_NAME=MedSDN
- قاعدة البيانات: sawa_db
- المستخدم: sawa
- كلمة المرور: StrongPass@123

### الخطوة 3: توليد Application Key

```bash
# إذا لم يكن APP_KEY موجوداً
php artisan key:generate
```

### الخطوة 4: تشغيل Migration
php artisan serve

```bash
# تشغيل migrations
php artisan migrate

# أو إعادة إنشاء قاعدة البيانات
php artisan migrate:fresh --seed
```

### الخطوة 5: تثبيت Frontend Dependencies

```bash
npm install
npm run build
```

### الخطوة 6: تشغيل المشروع

```bash
# تشغيل Laravel development server
php artisan serve

# الوصول إلى المشروع
# Frontend: http://localhost:8000
# Admin: http://localhost:8000/admin
```

## Alternative: Using Docker

إذا كنت تفضل استخدام Docker:

```bash
# تثبيت Laravel Sail
composer require laravel/sail --dev

# نشر Sail
php artisan sail:install

# تشغيل Containers
./vendor/bin/sail up -d

# تشغيل migrations
./vendor/bin/sail artisan migrate
```

## Troubleshooting

### مشكلة: vendor/autoload.php not found

```bash
composer install
```

### مشكلة: Permission Denied

```bash
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

### مشكلة: Database Connection Error

تحقق من:
1. MySQL يعمل: `sudo systemctl status mysql`
2. قاعدة البيانات موجودة: `mysql -u sawa -p -e "SHOW DATABASES;"`
3. بيانات الاتصال في `.env` صحيحة

### مشكلة: Assets Not Loading

```bash
npm run build
php artisan storage:link
```

## Post-Installation

بعد التثبيت الناجح:

1. **تحديث GitNexus Index**
   ```bash
   npx gitnexus analyze --force
   ```

2. **تنظيف Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

3. **اختبار المشروع**
   - زيارة الصفحة الرئيسية
   - تسجيل الدخول إلى لوحة التحكم
   - التحقق من عدم وجود أخطاء

## Medical Features Configuration

لتفعيل الميزات الطبية، أضف إلى `.env`:

```env
# Prescription Management
MEDSDN_PRESCRIPTION_REQUIRED=true
MEDSDN_PRESCRIPTION_UPLOAD=true
MEDSDN_PRESCRIPTION_VERIFICATION=true

# License Verification
MEDSDN_LICENSE_VERIFICATION=true

# Product Features
MEDSDN_BATCH_TRACKING=true
MEDSDN_EXPIRY_MANAGEMENT=true
MEDSDN_EXPIRY_ALERT_DAYS=90

# Compliance
MEDSDN_AUDIT_LOGGING=true
MEDSDN_DATA_ENCRYPTION=true
```

## Next Steps

1. راجع `REBRANDING.md` للتفاصيل الكاملة
2. راجع `README_MEDSDN.md` للوثائق
3. راجع `QUICKSTART_MEDSDN.md` للبدء السريع
4. راجع `.kiro/steering/medical-features.md` للميزات الطبية

## Support

- Email: support@medsdn.com
- Documentation: docs.medsdn.com (قريباً)
- Issues: GitHub Issues

---

**آخر تحديث**: 5 مارس 2026
