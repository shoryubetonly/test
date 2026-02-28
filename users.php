<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('ไม่มีสิทธิ์เข้าถึงหน้านี้!'); window.location='index.php';</script>";
    exit();
}

$currentUser = $_SESSION['user'];
$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];

// เพิ่มสมาชิกใหม่
if (isset($_POST['add_user'])) {
    $uname = mysqli_real_escape_string($conn, $_POST['username']);
    $upass = mysqli_real_escape_string($conn, $_POST['password']);
    $ufull = mysqli_real_escape_string($conn, $_POST['fullname']);
    $urole = mysqli_real_escape_string($conn, $_POST['role']);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$uname'");
    if(mysqli_num_rows($check) > 0) {
        echo "<script>alert('Username นี้ถูกใช้งานแล้ว!');</script>";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, password, fullname, role) VALUES ('$uname', '$upass', '$ufull', '$urole')");
        header("Location: users.php");
        exit();
    }
}

// ลบสมาชิก
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    if ($_SESSION['user'] !== mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE id=$id"))['username']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    }
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก · LibLight</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS หลักสไตล์ Light Minimalist */
        :root {
            --bg-body: #f8fafc; --bg-surface: #ffffff; --border-light: #e2e8f0;
            --text-main: #0f172a; --text-muted: #64748b;
            --primary: #6366f1; --primary-light: #e0e7ff;
            --admin-color: #8b5cf6; --admin-light: #ede9fe;
            --danger: #ef4444; --danger-light: #fee2e2;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--bg-body); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }

        .sidebar { width: 260px; background-color: var(--bg-surface); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; padding: 1.5rem; box-shadow: 2px 0 10px rgba(0,0,0,0.02); z-index: 10; }
        .brand { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 2.5rem; display: flex; align-items: center; gap: 10px; }
        .nav-menu { display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
        .nav-link { padding: 0.8rem 1rem; border-radius: 8px; color: var(--text-muted); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 12px; transition: 0.2s; }
        .nav-link:hover { background-color: #f1f5f9; color: var(--text-main); }
        .nav-link.active { background-color: var(--primary-light); color: var(--primary); font-weight: 600; }
        .user-profile { margin-top: auto; padding-top: 1.5rem; border-top: 1px solid var(--border-light); display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1rem; }
        .user-info { display: flex; flex-direction: column; }
        .user-name { font-size: 0.95rem; font-weight: 600; color: var(--text-main); }
        .user-role { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .logout-btn { margin-left: auto; color: var(--text-muted); text-decoration: none; transition: 0.2s; padding: 5px;}
        .logout-btn:hover { color: var(--danger); }

        .main-content { flex: 1; padding: 2.5rem 3rem; overflow-y: auto; }
        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 1.8rem; font-weight: 600; margin-bottom: 0.3rem; }

        .card { background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 2rem; }
        .card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-main); margin-bottom: 1.25rem; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem; font-weight: 500; }
        .form-control, select { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-light); border-radius: 8px; font-family: 'Sarabun'; font-size: 0.95rem; background-color: var(--bg-body); transition: 0.2s; }
        .form-control:focus, select:focus { outline: none; border-color: var(--admin-color); background-color: var(--bg-surface); box-shadow: 0 0 0 3px var(--admin-light); }
        .btn-admin { padding: 0.75rem 1.5rem; background: var(--admin-color); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Sarabun'; height: 42px;}
        .btn-admin:hover { background: #7c3aed; }
        .btn-danger-sm { background: var(--danger-light); color: var(--danger); text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: 0.2s; }
        .btn-danger-sm:hover { background: #fecaca; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); background-color: var(--bg-body); border-bottom: 1px solid var(--border-light); text-transform: uppercase; }
        td { padding: 1rem; font-size: 0.95rem; color: var(--text-main); border-bottom: 1px solid var(--border-light); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f8fafc; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-admin { background-color: var(--admin-light); color: var(--admin-color); }
        .badge-user { background-color: #f1f5f9; color: var(--text-muted); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">📚 LibLight</div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link"><span>📊</span> ภาพรวมระบบ</a>
            <a href="books.php" class="nav-link"><span>📖</span> คลังหนังสือ</a>
            <a href="borrow.php" class="nav-link"><span>🔄</span> ยืม-คืนหนังสือ</a>
            <a href="history.php" class="nav-link"><span>📜</span> ประวัติรายการ</a>
            <a href="users.php" class="nav-link active"><span>👥</span> จัดการสมาชิก</a>
        </div>
        <div class="user-profile">
            <div class="avatar" style="background-color: var(--admin-color);"><?php echo strtoupper(substr($currentUser, 0, 1)); ?></div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($userFullName); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
            </div>
            <a href="logout.php" class="logout-btn" title="ออกจากระบบ">🚪</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">จัดการสมาชิก (User Management)</h1>
        </div>

        <div class="card">
            <div class="card-title" style="color: var(--admin-color);">+ เพิ่มสมาชิกใหม่</div>
            <form method="POST" class="form-grid">
                <div class="form-group"><label>ชื่อเข้าใช้งาน (Username)</label><input type="text" name="username" class="form-control" required autocomplete="off"></div>
                <div class="form-group"><label>รหัสผ่าน (Password)</label><input type="password" name="password" class="form-control" required></div>
                <div class="form-group"><label>ชื่อ-นามสกุล (Fullname)</label><input type="text" name="fullname" class="form-control" required></div>
                <div class="form-group">
                    <label>ระดับสิทธิ์ (Role)</label>
                    <select name="role" required>
                        <option value="user">User (ผู้ใช้ทั่วไป)</option>
                        <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn-admin">เพิ่มข้อมูล</button>
            </form>
        </div>

        <div class="card" style="padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left: 1.5rem;">ID</th>
                        <th>Username</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>สิทธิ์การใช้งาน</th>
                        <th style="padding-right: 1.5rem;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td style="padding-left: 1.5rem; color: var(--text-muted);">#<?php echo $row['id']; ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td>
                            <?php if($row['role'] == 'admin'): ?>
                                <span class="badge badge-admin">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-user">User</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding-right: 1.5rem;">
                            <?php if($row['username'] !== $currentUser): ?>
                                <a href="users.php?delete_id=<?php echo $row['id']; ?>" class="btn-danger-sm" onclick="return confirm('ยืนยันการลบสมาชิกรายนี้?')">ลบ</a>
                            <?php else: ?>
                                <span style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">คุณ (ไม่อนุญาตให้ลบ)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>