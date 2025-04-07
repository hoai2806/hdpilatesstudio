# HD Pilates Management System

Hệ thống quản lý phòng tập HD Pilates

## Cài đặt

1. Clone repository:
```bash
git clone <repository-url>
```

2. Cấu hình database trong `config.php`

3. Import database từ file `database.sql`

## Phát triển

1. Tạo branch mới cho tính năng:
```bash
git checkout -b feature/ten-tinh-nang
```

2. Commit và push code:
```bash
git add .
git commit -m "Mô tả thay đổi"
git push origin feature/ten-tinh-nang
```

3. Tạo Pull Request để merge vào branch main

## Deploy

- Code sẽ tự động được deploy lên hosting khi push vào branch main
- Kiểm tra status của deployment trong tab Actions trên GitHub 