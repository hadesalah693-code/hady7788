#!/bin/sh
set -eu

# Render يحقن PORT (الافتراضي 10000)
: "${PORT:=10000}"
export PORT

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is not set. Generate it with: php artisan key:generate --show" >&2
    exit 1
fi

# Render يوفّر RENDER_EXTERNAL_URL تلقائيًا. استخدمه إن لم يُضبط APP_URL يدويًا،
# حتى تُبنى روابط HTTPS بشكل صحيح دون الحاجة لتعديل القيمة بعد كل نشر.
if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    APP_URL="${RENDER_EXTERNAL_URL}"
    export APP_URL
fi

# اجعل Apache يستمع على منفذ Render
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

# مجلدات التشغيل (تعمل مع الحاوية الجديدة أو قرص Render الدائم)
mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/app/property-images \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# امسح أي كاش إعدادات قديم حتى تُقرأ متغيرات Render الحالية
php artisan config:clear >/dev/null 2>&1 || true

# رابط public/storage (آمن للتكرار، ويُستخدم في التخزين المحلي فقط)
php artisan storage:link --force >/dev/null 2>&1 || true

# ترحيلات اختيارية: تُشغَّل عند الإقلاع إذا كان RUN_MIGRATIONS=true
# (خطة Render المجانية لا تدعم Pre-Deploy Command، لذلك هذا هو المسار الافتراضي)
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo ">>> Running database migrations..."
    if ! php artisan migrate --force --no-interaction; then
        echo "!!! MIGRATION FAILED — aborting startup" >&2
        exit 1
    fi
    echo ">>> Migrations completed successfully."
fi

# إنشاء/تحديث حساب المدير مرة واحدة فقط.
# اجعل RUN_ADMIN_SEEDER=true في أول نشر ثم أعِدها إلى false.
if [ "${RUN_ADMIN_SEEDER:-false}" = "true" ]; then
    php artisan db:seed --class=AdminUserSeeder --force
fi

# كاش الإنتاج (يُبنى وقت التشغيل لأن القيم تأتي من بيئة Render)
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec apache2-foreground
