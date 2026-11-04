# دليل النشر على Render (Docker + Neon + S3-Compatible)

هذا الدليل يشرح نشر منصة «الإمداد العقارية» على Render باستخدام Docker، مع قاعدة بيانات Neon وتخزين صور على S3-Compatible (Cloudflare R2 / AWS S3 / Backblaze B2).

> **قبل البدء:** لا تنفّذ `git push` أو أي تغيير في DNS أو أي خدمة مدفوعة دون أمر صريح.

---

## 1) المتطلبات

- مستودع GitHub يحتوي المشروع (الفرع `main`).
- حساب Render.
- قاعدة بيانات Neon (PostgreSQL 16+).
- حاوية S3-Compatible **خاصة** لصور العقارات.
- حاوية S3-Compatible **عامة** للأفاتار وشعارات الشركات.

---

## 2) إعداد الحاويات (Buckets)

### 2.1 الحاوية الخاصة (صور العقارات)

- الاسم المقترح: `aqari-private`
- الوصول: **خاص** (Private)
- تُستخدم عبر `AWS_BUCKET`
- الصور تُقدَّم عبر `PropertyImageController` وليس برابط مباشر.

### 2.2 الحاوية العامة (الأفاتار والشعارات)

- الاسم المقترح: `aqari-public`
- الوصول: **عام للقراءة** (Public)
- تُستخدم عبر `AWS_PUBLIC_BUCKET`
- يجب ضبط `AWS_PUBLIC_URL` على الرابط العام:
  - Cloudflare R2: رابط `r2.dev` أو دومين مخصص.
  - AWS S3: رابط CloudFront أو رابط الحاوية العام.

### 2.3 مفاتيح الوصول

أنشئ API Token / Access Key بصلاحيات القراءة والكتابة على الحاويتين.

---

## 3) إعداد Neon

1. أنشئ مشروعًا وقاعدة بيانات.
2. استخدم **الاتصال المباشر (Direct)** وليس `-pooler` (لتجنّب مشاكل Prepared Statements).
3. احتفظ بالقيم:
   - Host
   - Database
   - Username
   - Password
4. `DB_SSLMODE=require`.

---

## 4) ترحيل البيانات الحالية

> هذه الخطوة ضرورية للحفاظ على 19 عقارًا و54 صورة الموجودة محليًا.

### 4.1 قاعدة البيانات

```bash
pg_dump -h 127.0.0.1 -p 5433 -U postgres -Fc aqari > aqari.dump
pg_restore --no-owner --no-privileges -d "<NEON_CONNECTION_STRING>" aqari.dump
```

### 4.2 الصور والملفات

1. أضف متغيرات S3 إلى ملف `.env` المحلي مؤقتًا:

```dotenv
PROPERTY_IMAGE_DRIVER=s3
PUBLIC_DISK_DRIVER=s3

AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=aqari-private
AWS_PUBLIC_BUCKET=aqari-public
AWS_PUBLIC_URL=https://pub-xxxxx.r2.dev
AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

2. عاين الملفات أولًا:

```bash
php artisan media:sync-to-s3 --dry-run
```

3. ارفعها:

```bash
php artisan media:sync-to-s3
```

الأمر يرفع:
- `storage/app/property-images` إلى الحاوية الخاصة.
- `storage/app/public` (avatars / company-logos) إلى الحاوية العامة.

> **ملاحظة مهمة عن البيانات الحالية:** الصور الـ54 الموجودة حاليًا كلها صور ديمو داخل `public/images/demo` (متتبعة في Git)، وليست على القرص الخاص. لذلك لا تحتاج إلى رفعها إلى S3، وستبقى تعمل من داخل الصورة. الأمر `media:sync-to-s3` يفيد الصور التي ترفعها محليًا لاحقًا أو أي ملفات موجودة في `storage/app`.

> **ملاحظة:** لا حاجة لتعديل عمود `disk` في قاعدة البيانات؛ اسم القرص بقي `property-images` وتغيّر مشغّله فقط.

4. أعِد قيم `.env` المحلية إلى `local` بعد الانتهاء إن أردت.

---

## 5) إنشاء الخدمة على Render

### الطريقة أ: Blueprint (`render.yaml`)

1. ارفع الكود إلى GitHub (بعد الموافقة).
2. من Render: **New → Blueprint** واختر المستودع.
3. أدخل قيم المتغيرات التي تحمل `sync: false`.

### الطريقة ب: يدويًا

- Type: **Web Service**
- Runtime: **Docker**
- Region: **Frankfurt**
- Dockerfile Path: `./Dockerfile`
- Docker Context: `.`
- Health Check Path: `/up`
- Plan: **Free** (بدون قرص)

---

## 6) متغيرات البيئة

| المتغير | القيمة |
|---|---|
| `APP_NAME` | `الإمداد العقارية` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | لا تضبطها؛ يُشتق تلقائيًا من `RENDER_EXTERNAL_URL` (اضبطها فقط عند استخدام دومين مخصص) |
| `APP_KEY` | `base64:...` |
| `APP_LOCALE` | `ar` |
| `APP_FALLBACK_LOCALE` | `ar` |
| `APP_TIMEZONE` | `Africa/Cairo` |
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `warning` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | Neon host |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | Neon database |
| `DB_USERNAME` | Neon user |
| `DB_PASSWORD` | Neon password |
| `DB_SSLMODE` | `require` |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_ENCRYPT` | `true` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `FILESYSTEM_DISK` | `local` |
| `PROPERTY_IMAGE_DRIVER` | `s3` |
| `PUBLIC_DISK_DRIVER` | `s3` |
| `PROPERTY_IMAGE_MAX_PIXELS` | `12000000` (خطة Free) |
| `AWS_ACCESS_KEY_ID` | مفتاح S3 |
| `AWS_SECRET_ACCESS_KEY` | سر S3 |
| `AWS_DEFAULT_REGION` | `auto` (R2) أو منطقة S3 |
| `AWS_BUCKET` | `aqari-private` |
| `AWS_PUBLIC_BUCKET` | `aqari-public` |
| `AWS_PUBLIC_URL` | رابط الحاوية العامة |
| `AWS_ENDPOINT` | endpoint الخاص بـ R2/S3 |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `true` (R2) |
| `ADMIN_EMAIL` | بريد المدير |
| `ADMIN_PASSWORD` | كلمة مرور قوية |
| `RUN_MIGRATIONS` | `true` |
| `RUN_ADMIN_SEEDER` | `false` (اجعلها `true` في أول نشر فقط) |

توليد `APP_KEY`:

```bash
php artisan key:generate --show
```

---

## 7) النشر الأول

1. Render يبني الصورة وينشرها.
2. `entrypoint.sh` يشغّل:
   - `php artisan migrate --force` (لأن `RUN_MIGRATIONS=true`).
   - كاش الإنتاج.
3. بعد نجاح النشر، اجعل `RUN_ADMIN_SEEDER=true` مؤقتًا وأعد النشر لإنشاء حساب المدير، ثم أعِدها `false`.
   - بديل: نفّذ `php artisan db:seed --class=AdminUserSeeder --force` من Render Shell إن كانت متاحة.
4. تحقق من:
   - `https://<service>.onrender.com/up` ⇒ 200
   - `/` ⇒ 200
   - `/admin/login` ⇒ 200
   - تسجيل الدخول بحساب المدير.

---

## 8) التحقق من عدم فقدان الصور

1. ارفع صورة عقار من لوحة التحكم.
2. أعد نشر الخدمة من Render.
3. افتح صفحة العقار وتأكد من ظهور الصورة.
4. كرر بعد إعادة تشغيل الخدمة يدويًا.

> الصور الآن على S3، لذا لا تتأثر بإعادة النشر أو إعادة التشغيل.

---

## 9) قيود خطة Free

- الخدمة تنام بعد ~15 دقيقة خمول، وأول طلب بعد ذلك يستغرق وقتًا أطول.
- لا قرص دائم ⇒ الاعتماد الكامل على S3 للملفات.
- لا `Pre-Deploy Command` ولا Render Shell ⇒ الترحيلات عبر `RUN_MIGRATIONS`.
- الذاكرة 512MB ⇒ سقف معالجة الصور مضبوط على 12MP.
- بعد الترقية إلى خطة بذاكرة 2GB يمكن رفع `PROPERTY_IMAGE_MAX_PIXELS` إلى `36000000`.

---

## 10) النسخ الاحتياطي

- **قاعدة البيانات:** جدول `pg_dump` دوريًا إلى تخزين خارجي (Neon PITR إن كانت الخطة تدعمه).
- **الصور:** فعّل Versioning على حاوية S3 إن كانت تدعمه، أو انسخها دوريًا إلى حاوية أخرى.
- لا تعتمد على نظام ملفات Render كنسخة احتياطية.

---

## 11) ملاحظات أمنية

- لا ترفع `.env` إلى Git (مستبعد أصلًا).
- لا تضع الأسرار في `render.yaml`؛ استخدم `sync: false`.
- `APP_DEBUG=false` و`APP_ENV=production` إلزاميان.
- الكوكيز `Secure` + `HttpOnly` + `SameSite=Lax`.
- `TrustProxies` مضبوط ليعمل HTTPS خلف Proxy الخاص بـ Render.
- DocumentRoot هو `public/` فقط.
- احذف `ADMIN_PASSWORD` من Render بعد إنشاء المدير إن أردت.
