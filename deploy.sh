#!/bin/bash

# Đường dẫn thư mục web
DEPLOY_PATH="/home/quanly.pilates.net.vn/html"

# Pull code mới từ GitHub
cd $DEPLOY_PATH
git pull origin main

# Cập nhật quyền
chown -R www-data:www-data .
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Xóa cache
php artisan cache:clear

echo "Deploy completed!" 