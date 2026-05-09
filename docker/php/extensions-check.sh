#!/bin/sh
# Verify all required PHP extensions are installed
# Exit code: 0 = all OK, 1 = missing extensions

REQUIRED="pdo_pgsql pgsql redis imagick gd intl bcmath zip soap exif pcntl opcache"
FAIL=0

echo "=== PHP Extension Verification ==="
echo ""

for ext in $REQUIRED; do
    if php -m 2>/dev/null | grep -qiw "$ext"; then
        echo "  ✅ $ext"
    else
        echo "  ❌ $ext — MISSING"
        FAIL=1
    fi
done

echo ""
if [ $FAIL -eq 0 ]; then
    echo "All extensions OK"
else
    echo "Some extensions are missing!"
fi

exit $FAIL
