#!/bin/bash

# Di chuyển đến thư mục web
cd /usr/local/lsws/quanly.pilates.net.vn/html/

# Backup file config.php hiện tại
if [ -f config.php ]; then
    cp config.php config.php.bak
fi

# Pull code mới từ git
git pull origin main

# Khôi phục file config.php
if [ -f config.php.bak ]; then
    mv config.php.bak config.php
fi

# Cập nhật quyền
chown -R nobody:nobody /usr/local/lsws/quanly.pilates.net.vn/html/
chmod -R 755 /usr/local/lsws/quanly.pilates.net.vn/html/

# Xóa cache
php artisan cache:clear

echo "Deploy completed!" 