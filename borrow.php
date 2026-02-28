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

// --- 1. ระบบบันทึกการยืม ---
if (isset($_POST['borrow_submit'])) {
    $book_id = mysqli_real_escape_string($conn, $_POST['book_id']);
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $borrow_date = date('Y-m-d');

    // บันทึกการยืม
    $insert_sql = "INSERT INTO borrow (user_id, book_id, borrow_date, status) VALUES ('$user_id', '$book_id', '$borrow_date', 'borrowed')";
    
    // ตัดสต็อกหนังสือ
    $update_stock_sql = "UPDATE books SET stock = stock - 1 WHERE id = '$book_id' AND stock > 0";

    if (mysqli_query($conn, $insert_sql) && mysqli_query($conn, $update_stock_sql)) {
        header("Location: borrow.php");
        exit();
    }
}

// --- 2. ระบบการคืนหนังสือ (เฉพาะ Admin) ---
if (isset($_GET['return_id']) && $userRole === 'admin') {
    $borrow_id = (int)$_GET['return_id'];
    $book_id = (int)$_GET['book_id'];
    $return_date = date('Y-m-d');

    // อัปเดตสถานะการยืมเป็น "คืนแล้ว"
    mysqli_query($conn, "UPDATE borrow SET return_date = '$return_date', status = 'returned' WHERE id = '$borrow_id'");
    
    // คืนสต็อกหนังสือ
    mysqli_query($conn, "UPDATE books SET stock = stock + 1 WHERE id = '$book_id'");
    
    header("Location: borrow.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืม-คืนหนังสือ · LibLight</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ตัวแปรสี Light Theme */
        :root {
            --bg-body: #f8fafc; --bg-surface: #ffffff; --border-light: #e2e8f0;
            --text-main: #0f172a; --text-muted: #64748b;
            --primary: #6366f1; --primary-light: #e0e7ff;
            --success: #10b981; --success-light: #d1fae5;
            --warning: #f59e0b; --warning-light: #fef3c7;
            --danger: #ef4444; 
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--bg-body); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar */
        .sidebar { width: 260px; background-color: var(--bg-surface); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; padding: 1.5rem; box-shadow: 2px 0 10px rgba(0,0,0,0.02); z-index: 10; }
        .brand { font-size: 1.5rem; font-weight: 700; color: var(--text-main); margin-bottom: 2.5rem; display: flex; align-items: center; gap: 10px; }
        .nav-menu { display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
        .nav-link { padding: 0.8rem 1rem; border-radius: 8px; color: var(--text-muted); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease; }
        .nav-link:hover { background-color: #f1f5f9; color: var(--text-main); }
        .nav-link.active { background-color: var(--primary-light); color: var(--primary); font-weight: 600; }
        .user-profile { margin-top: auto; padding-top: 1.5rem; border-top: 1px solid var(--border-light); display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1rem; }
        .user-info { display: flex; flex-direction: column; }
        .user-name { font-size: 0.95rem; font-weight: 600; color: var(--text-main); }
        .user-role { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .logout-btn { margin-left: auto; color: var(--text-muted); text-decoration: none; transition: 0.2s; padding: 5px;}
        .logout-btn:hover { color: var(--danger); }

        /* Main Content */
        .main-content { flex: 1; padding: 2.5rem 3rem; overflow-y: auto; }
        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 1.8rem; font-weight: 600; margin-bottom: 0.3rem; }

        /* Quick Action Panel (ดีไซน์ใหม่แนวนอน) */
        .action-panel {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        /* เส้นขอบสีครามตกแต่งด้านซ้ายของการ์ด */
        .action-panel::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background-color: var(--primary);
        }

        .action-title { font-size: 1.15rem; font-weight: 600; color: var(--text-main); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;}
        
        .form-row {
            display: flex;
            gap: 1.5rem;
            align-items: flex-end;
        }
        
        .form-group { flex: 1; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;}
        
        select { 
            width: 100%; padding: 0.85rem 1rem; 
            border: 1px solid #cbd5e1; border-radius: 8px; 
            font-family: 'Sarabun'; font-size: 1rem; color: var(--text-main); 
            background-color: var(--bg-body); transition: 0.2s; cursor: pointer; 
        }
        select:focus { outline: none; border-color: var(--primary); background-color: var(--bg-surface); box-shadow: 0 0 0 4px var(--primary-light); }
        
        .btn-primary { 
            padding: 0.85rem 2rem; background: var(--primary); color: white; 
            border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; 
            cursor: pointer; transition: 0.2s; font-family: 'Sarabun'; 
            white-space: nowrap; height: 47px;
        }
        .btn-primary:hover { background: #4f46e5; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); }

        /* Table Card (กางเต็มพื้นที่) */
        .table-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .table-header { padding: 1.5rem; border-bottom: 1px solid var(--border-light); font-size: 1.1rem; font-weight: 600; color: var(--text-main); }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem 1.5rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); background-color: #f8fafc; border-bottom: 1px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 1.2rem 1.5rem; font-size: 0.95rem; color: var(--text-main); border-bottom: 1px solid var(--border-light); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f8fafc; }
        
        .btn-return { background: var(--success-light); color: var(--success); text-decoration: none; padding: 6px 16px; border-radius: 50px; font-weight: 600; font-size: 0.85rem; transition: 0.2s; display: inline-block; border: 1px solid transparent; }
        .btn-return:hover { background: #a7f3d0; color: #065f46; border-color: #34d399;}
        .text-wait { color: var(--warning); font-size: 0.85rem; font-weight: 600; background: var(--warning-light); padding: 6px 14px; border-radius: 50px;}
        
        .book-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 8px; background: var(--primary-light); color: var(--primary); margin-right: 12px;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">📚 LibLight</div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link"><span>📊</span> ภาพรวมระบบ</a>
            <a href="books.php" class="nav-link"><span>📖</span> คลังหนังสือ</a>
            <a href="borrow.php" class="nav-link active"><span>🔄</span> ยืม-คืนหนังสือ</a>
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
            <h1 class="page-title">ระบบยืม-คืนหนังสือ</h1>
        </div>

        <div class="action-panel">
            <div class="action-title">✨ ทำรายการยืมใหม่ (Quick Borrow)</div>
            <form method="POST" class="form-row">
                <div class="form-group">
                    <label>📖 เลือกหนังสือ (เฉพาะที่มีสต็อก)</label>
                    <select name="book_id" required>
                        <option value="">-- กรุณาค้นหาและเลือกหนังสือ --</option>
                        <?php
                        $books_avail = mysqli_query($conn, "SELECT id, title, stock FROM books WHERE stock > 0 ORDER BY title ASC");
                        while($b = mysqli_fetch_assoc($books_avail)) {
                            echo "<option value='{$b['id']}'>{$b['title']} (คงเหลือ {$b['stock']})</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>👤 ระบุข้อมูลผู้ยืม</label>
                    <select name="user_id" required>
                        <?php if($userRole === 'admin'): ?>
                            <option value="">-- เลือกสมาชิกที่ทำรายการ --</option>
                            <?php
                            $users_list = mysqli_query($conn, "SELECT id, fullname FROM users ORDER BY fullname ASC");
                            while($u = mysqli_fetch_assoc($users_list)) {
                                echo "<option value='{$u['id']}'>{$u['fullname']}</option>";
                            }
                            ?>
                        <?php else: ?>
                            <?php 
                            $u_res = mysqli_query($conn, "SELECT id, fullname FROM users WHERE username = '$currentUser'");
                            $u_data = mysqli_fetch_assoc($u_res);
                            ?>
                            <option value="<?php echo $u_data['id']; ?>"><?php echo $u_data['fullname']; ?> (บัญชีของคุณ)</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <button type="submit" name="borrow_submit" class="btn-primary">+ ยืนยันการยืม</button>
            </form>
        </div>

        <div class="table-card">
            <div class="table-header">📚 รายการหนังสือที่ยังไม่ได้รับคืน (Active Loans)</div>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อหนังสือ</th>
                        <th>ผู้ยืม</th>
                        <th>วันที่ทำรายการยืม</th>
                        <th style="text-align: right;">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_borrow = "SELECT borrow.id as bid, books.id as bookid, books.title, users.fullname, borrow.borrow_date 
                                   FROM borrow 
                                   JOIN books ON borrow.book_id = books.id 
                                   JOIN users ON borrow.user_id = users.id 
                                   WHERE borrow.status = 'borrowed'";
                    
                    if ($userRole !== 'admin') {
                        $sql_borrow .= " AND users.username = '$currentUser'";
                    }
                    $sql_borrow .= " ORDER BY borrow.borrow_date DESC";
                    
                    $borrow_list = mysqli_query($conn, $sql_borrow);
                    
                    if(mysqli_num_rows($borrow_list) > 0):
                        while($row = mysqli_fetch_assoc($borrow_list)):
                    ?>
                    <tr>
                        <td style="font-weight: 500;">
                            <span class="book-icon">📘</span>
                            <?php echo htmlspecialchars($row['title']); ?>
                        </td>
                        <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td style="font-size: 0.9rem;"><?php echo date('d / m / Y', strtotime($row['borrow_date'])); ?></td>
                        <td style="text-align: right;">
                            <?php if($userRole === 'admin'): ?>
                                <a href="borrow.php?return_id=<?php echo $row['bid']; ?>&book_id=<?php echo $row['bookid']; ?>" 
                                   class="btn-return" onclick="return confirm('ยืนยันว่าได้รับหนังสือเล่มนี้คืนแล้ว?')">รับคืนหนังสือ</a>
                            <?php else: ?>
                                <span class="text-wait">รอเจ้าหน้าที่รับคืน</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 4rem 1rem; color: var(--text-muted);">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">✨</div>
                            ไม่มีรายการหนังสือที่ค้างส่งในระบบ
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>