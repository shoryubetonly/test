<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูลสำหรับ Docker
$host = "db";             // ชี้ไปที่ Service 'db' ในไฟล์ docker-compose.yml
$user = "root";           // Username เริ่มต้นของ MySQL ใน Docker
$pass = "root";           // Password ที่เราตั้งไว้ใน docker-compose.yml
$dbname = "s673190114";   // ชื่อฐานข้อมูลใหม่ของโปรเจกต์นี้

// สร้างการเชื่อมต่อ
$conn = mysqli_connect($host, $user, $pass, $dbname);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    // ถ้าพัง จะโชว์ข้อความ Error แบบคลีนๆ
    die("<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #f87171;'>
            <strong>การเชื่อมต่อฐานข้อมูลล้มเหลว:</strong> <br>" . mysqli_connect_error() . "
         </div>");
}

// ตั้งค่าให้รองรับภาษาไทย (UTF-8) ป้องกันตัวหนังสือเป็นภาษาต่างดาว
mysqli_set_charset($conn, "utf8mb4");

// ถ้าอยากเทสต์ว่าเชื่อมต่อสำเร็จไหม ให้เอาเครื่องหมาย // บรรทัดล่างออกครับ
// echo "✅ เชื่อมต่อฐานข้อมูล s673190114 สำเร็จ!";
?>