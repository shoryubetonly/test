<?php
session_start();
require_once 'db.php';

// ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$currentUser = $_SESSION['user'];
$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];

// --- คำนวณสถิติภาพรวม ---
// 1. จำนวนหนังสือและสต็อกรวม
$book_stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total_titles, SUM(stock) as total_stock FROM books"));
$titles_count = $book_stats['total_titles'] ?? 0;
$stock_count = $book_stats['total_stock'] ?? 0;

// 2. จำนวนรายการที่กำลังถูกยืม
$borrow_stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as currently_borrowed FROM borrow WHERE status = 'borrowed'"));
$borrowed_count = $borrow_stats['currently_borrowed'] ?? 0;

// 3. จำนวนสมาชิกทั้งหมด
$user_stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) as total_users FROM users"));
$users_count = $user_stats['total_users'] ?? 0;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ภาพรวมระบบ · LibLight</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ตัวแปรสี Light Theme */
        :root {
            --bg-body: #f8fafc;        /* สีเทาอมฟ้าสว่าง (พื้นหลังหลัก) */
            --bg-surface: #ffffff;     /* สีขาว (การ์ด/เมนู) */
            --border-light: #e2e8f0;   /* สีเส้นขอบอ่อนๆ */
            --text-main: #0f172a;      /* สีข้อความหลัก (ดำเข้ม) */
            --text-muted: #64748b;     /* สีข้อความรอง (เทา) */
            --primary: #6366f1;        /* สีคราม Indigo (สีหลัก) */
            --primary-light: #e0e7ff;  /* สีครามพื้นหลังอ่อน */
            --success: #10b981;        /* สีเขียว */
            --warning: #f59e0b;        /* สีส้มเหลือง */
            --danger: #ef4444;         /* สีแดง */
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--bg-body); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }

        /* ================= Sidebar ================= */
        .sidebar {
            width: 260px;
            background-color: var(--bg-surface);
            border-right: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            box-shadow: 2px 0 10px rgba(0,0,0,0.02);
            z-index: 10;
        }

        .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-menu { display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
        .nav-link {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }
        .nav-link:hover { background-color: #f1f5f9; color: var(--text-main); }
        .nav-link.active { background-color: var(--primary-light); color: var(--primary); font-weight: 600; }

        /* User Profile in Sidebar */
        .user-profile {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background-color: var(--primary); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 1.1rem;
        }
        .user-info { display: flex; flex-direction: column; }
        .user-name { font-size: 0.95rem; font-weight: 600; color: var(--text-main); }
        .user-role { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .logout-btn { margin-left: auto; color: var(--text-muted); text-decoration: none; transition: 0.2s; padding: 5px;}
        .logout-btn:hover { color: var(--danger); }

        /* ================= Main Content ================= */
        .main-content {
            flex: 1; padding: 2.5rem 3rem; overflow-y: auto;
        }

        .page-header { margin-bottom: 2.5rem; }
        .page-title { font-size: 1.8rem; font-weight: 600; margin-bottom: 0.3rem; }
        .page-subtitle { color: var(--text-muted); font-size: 0.95rem; }

        /* Dashboard Cards */
        .grid-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex; flex-direction: column;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        
        .stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;}
        .stat-value { font-size: 2.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem; line-height: 1; }
        .stat-desc { font-size: 0.85rem; color: var(--text-muted); }

        /* Table Card */
        .table-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            display: flex; justify-content: space-between; align-items: center;
        }
        .table-title { font-size: 1.1rem; font-weight: 600; color: var(--text-main); }
        .view-all { color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 500; }
        .view-all:hover { text-decoration: underline; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); background-color: #f8fafc; border-bottom: 1px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px;}
        td { padding: 1rem 1.5rem; font-size: 0.95rem; color: var(--text-main); border-bottom: 1px solid var(--border-light); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f8fafc; }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-borrowed { background-color: #fef3c7; color: #d97706; }
        .badge-returned { background-color: #d1fae5; color: #059669; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">📚 LibLight</div>
        
        <div class="nav-menu">
            <a href="index.php" class="nav-link active"><span>📊</span> ภาพรวมระบบ</a>
            <a href="books.php" class="nav-link"><span>📖</span> คลังหนังสือ</a>
            <a href="borrow.php" class="nav-link"><span>🔄</span> ยืม-คืนหนังสือ</a>
            <a href="history.php" class="nav-link"><span>📜</span> ประวัติรายการ</a>
            <?php if($userRole === 'admin'): ?>
                <a href="users.php" class="nav-link"><span>👥</span> จัดการสมาชิก</a>
            <?php endif; ?>
        </div>

        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper(substr($currentUser, 0, 1)); ?></div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($userFullName); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
            </div>
            <a href="logout.php" class="logout-btn" title="ออกจากระบบ">🚪</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">สวัสดี, <?php echo htmlspecialchars($userFullName); ?> 👋</h1>
            <p class="page-subtitle">นี่คือข้อมูลสรุปภาพรวมของห้องสมุดในขณะนี้</p>
        </div>

        <div class="grid-cards">
            <div class="stat-card">
                <div class="stat-header"><span>หนังสือในคลัง (เรื่อง)</span> <span>📘</span></div>
                <div class="stat-value"><?php echo number_format($titles_count); ?></div>
                <div class="stat-desc">รวมจำนวนทั้งหมด <?php echo number_format($stock_count); ?> เล่ม</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span>กำลังถูกยืม</span> <span>⏱️</span></div>
                <div class="stat-value" style="color: var(--warning);"><?php echo number_format($borrowed_count); ?></div>
                <div class="stat-desc">รายการที่รอการนำมาคืน</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span>สมาชิกในระบบ</span> <span>👥</span></div>
                <div class="stat-value" style="color: var(--primary);"><?php echo number_format($users_count); ?></div>
                <div class="stat-desc">ผู้ใช้งานระบบทั้งหมด</div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div class="table-title">ความเคลื่อนไหวล่าสุด (Recent Activity)</div>
                <a href="history.php" class="view-all">ดูทั้งหมด &rarr;</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>รหัสรายการ</th>
                        <th>ชื่อหนังสือ</th>
                        <th>ผู้ทำรายการ</th>
                        <th>วันที่ทำรายการ</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ดึงประวัติการทำรายการล่าสุด 5 รายการ
                    $sql_recent = "SELECT borrow.id, books.title, users.fullname, borrow.borrow_date, borrow.status 
                                   FROM borrow 
                                   JOIN books ON borrow.book_id = books.id 
                                   JOIN users ON borrow.user_id = users.id 
                                   ORDER BY borrow.id DESC LIMIT 5";
                    $recent_query = mysqli_query($conn, $sql_recent);
                    
                    if(mysqli_num_rows($recent_query) > 0):
                        while($row = mysqli_fetch_assoc($recent_query)):
                    ?>
                    <tr>
                        <td style="color: var(--text-muted);">#<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['borrow_date'])); ?></td>
                        <td>
                            <?php if($row['status'] == 'borrowed'): ?>
                                <span class="badge badge-borrowed">กำลังยืม</span>
                            <?php else: ?>
                                <span class="badge badge-returned">คืนแล้ว</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">ยังไม่มีประวัติการทำรายการในระบบ</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>