# الإمداد العقارية — منصة عقارية مصرية

منصة عقارية عربية (RTL) لعرض العقارات في مصر، مبنية على Laravel + Livewire + Filament + PostgreSQL.

المرحلة الأولى (المنفذة حاليًا) تشمل: الأساس التقني، قاعدة البيانات، لوحة تحكم مبدئية، الصفحة الرئيسية الاحترافية، صفحة عرض العقارات مع الفلاتر، وصفحة تفاصيل العقار.

---

## التقنيات

| الطبقة | التقنية |
| --- | --- |
| الإطار | Laravel 13 |
| الواجهات التفاعلية | Livewire 4 |
| لوحة التحكم | Filament 5 |
| قاعدة البيانات | PostgreSQL 18 (فقط) |
| التنسيق | Tailwind CSS 4 + Vite |
| الخط | Cairo (محلي داخل `public/fonts`) |

---

## المتطلبات

- PHP 8.3 أو أحدث مع الإضافات: `pdo_pgsql`, `pgsql`, `zip`, `mbstring`, `openssl`
- Composer 2
- Node.js 20+ و npm
- PostgreSQL 16+ (يعمل محليًا على المنفذ `5433` في بيئة التطوير الحالية)

> هذا المشروع لا يستخدم SQLite إطلاقًا.

---

## تشغيل قاعدة البيانات محليًا

المشروع يستخدم عنقود PostgreSQL معزول داخل مجلد المستخدم لتجنب تعارض خدمة النظام:

```powershell
# تشغيل
& "C:\Program Files\PostgreSQL\18\bin\pg_ctl.exe" `
  -D "C:\Users\<USER>\AppData\Local\aqari\pgsql-data" `
  -l "C:\Users\<USER>\AppData\Local\aqari\pgsql-data\server.log" `
  -o "-p 5433 -c listen_addresses=127.0.0.1" start

# إيقاف
& "C:\Program Files\PostgreSQL\18\bin\pg_ctl.exe" `
  -D "C:\Users\<USER>\AppData\Local\aqari\pgsql-data" stop
```

قواعد البيانات المطلوبة: `aqari` و `aqari_testing`.

---

## التهيئة

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

عدّل `.env` بما يناسب بيئتك:

```dotenv
APP_NAME="الإمداد العقارية"
APP_URL=http://127.0.0.1:8000
APP_LOCALE=ar
APP_TIMEZONE=Africa/Cairo

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=aqari
DB_USERNAME=postgres
DB_PASSWORD=

# كلمة مرور حساب المدير التجريبي — ضع قيمة قوية هنا
ADMIN_PASSWORD=
```

ثم:

```powershell
php artisan migrate:fresh --seed
npm install
npm run build      # أو npm run dev أثناء التطوير
php artisan serve
```

الموقع: <http://127.0.0.1:8000>

---

## لوحة التحكم

- الرابط: <http://127.0.0.1:8000/admin>
- البريد: `admin@emdad.test`
- كلمة المرور: القيمة الموجودة في `ADMIN_PASSWORD` داخل ملف `.env` (لا تُحفظ في الكود أو المستودع).

الدخول مسموح فقط للمستخدمين الذين لديهم `is_admin = true`.

---

## الصفحات المتاحة

| المسار | الوصف |
| --- | --- |
| `/` | الصفحة الرئيسية: بحث سريع، إحصائيات، أنواع العقارات، عقارات مميزة، محافظات، خطوات الاستخدام |
| `/properties` | قائمة العقارات مع فلاتر (بحث، غرض، نوع، محافظة) وترتيب وترقيم صفحات — عبر Livewire |
| `/properties/{slug}` | تفاصيل العقار: معرض صور، بيانات، موقع، بيانات منظمة JSON-LD، عقارات مشابهة |

---

## الاختبارات

```powershell
php artisan test
```

تغطي الاختبارات: الصفحة الرئيسية، قائمة العقارات والفلاتر والترقيم، تفاصيل العقار وعدّ المشاهدات، وصلاحيات لوحة التحكم.

---

## بنية المشروع (مختصر)

```
app/
├── Enums/                     # PropertyPurpose, PropertyStatus
├── Http/Controllers/          # HomeController, PropertyController
├── Livewire/Properties/       # PropertyList (فلاتر + ترقيم)
├── Models/                    # Property, PropertyImage, Governorate, City, District, PropertyType
└── Providers/Filament/        # إعداد لوحة التحكم
database/
├── factories/                 # بيانات تجريبية
├── migrations/                # المخطط الأساسي
└── seeders/                   # المحافظات، الأنواع، المدير، 18 عقارًا تجريبيًا
lang/ar/                       # ملفات الترجمة العربية
resources/
├── css/app.css                # ثيم Tailwind + الخط + المكونات
└── views/
    ├── home.blade.php
    ├── properties/show.blade.php
    ├── livewire/properties/property-list.blade.php
    └── components/            # التخطيط، الهيدر، الفوتر، كارت العقار، الأيقونات
```

---

## المراحل القادمة

1. حسابات المستخدمين والوسطاء والشركات.
2. إضافة وتعديل العقارات ورفع الصور من لوحة التحكم.
3. فلاتر متقدمة (السعر، المساحة، عدد الغرف) وحفظ البحث.
4. المفضلة والاستفسارات ولوحة إدارة كاملة.
5. التقارير، الأمان المتقدم، والنشر.
