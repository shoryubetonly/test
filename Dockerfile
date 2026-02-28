FROM php:8.0-apache

# ติดตั้ง extension สำหรับคุยกับฐานข้อมูล
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# เปิดใช้งานจำลอง URL
RUN a2enmod rewrite

# ให้สิทธิ์ Apache ในการอ่านไฟล์ (แก้ปัญหา Error 403)
RUN chown -R www-data:www-data /var/www/html