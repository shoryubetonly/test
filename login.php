<?php
session_start();
if (isset($_SESSION['user'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ · ระบบห้องสมุดมินิมอล</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f8fafc; /* สีเทาอมฟ้าอ่อนๆ */
            margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center; 
        }
        .login-card {
            background: #ffffff;
            width: 100%; max-width: 380px;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { margin: 0; color: #0f172a; font-size: 1.8rem; font-weight: 600; }
        .header p { margin: 0.5rem 0 0; color: #64748b; font-size: 0.95rem; }
        
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.9rem; font-weight: 500; color: #475569; }
        .form-group input { 
            width: 100%; padding: 0.75rem 1rem; 
            border: 1px solid #cbd5e1; border-radius: 8px; 
            box-sizing: border-box; font-family: 'Sarabun', sans-serif; font-size: 1rem;
            color: #1e293b; background-color: #f8fafc;
            transition: all 0.2s ease;
        }
        .form-group input:focus { 
            border-color: #6366f1; /* สีคราม Indigo */
            outline: none; 
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); 
        }
        
        .btn-login { 
            width: 100%; padding: 0.75rem; 
            background: #6366f1; color: #ffffff; 
            border: none; border-radius: 8px; 
            font-size: 1rem; font-weight: 600; cursor: pointer; font-family: 'Sarabun', sans-serif;
            transition: background-color 0.2s ease;
            margin-top: 1rem;
        }
        .btn-login:hover { background: #4f46e5; }
        
        .test-accounts { 
            text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: #94a3b8; 
            padding-top: 1.5rem; border-top: 1px dashed #e2e8f0;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="header">
            <h1>📚 LibLight</h1>
            <p>ระบบจัดการห้องสมุดยุคใหม่</p>
        </div>

        <form action="check_login.php" method="POST">
            <div class="form-group">
                <label>ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" name="username" required autofocus autocomplete="off">
            </div>
            <div class="form-group">
                <label>รหัสผ่าน (Password)</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
        </form>

        <div class="test-accounts">
            ลองใช้: admin (1234) หรือ user01 (1234)
        </div>
    </div>

</body>
</html>