<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$currentUser = $_SESSION['user'];
$userRole = $_SESSION['role'];
$userFullName = $_SESSION['fullname'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติรายการ · LibLight</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS หลักสไตล์ Light Minimalist */
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
        
        /* Search */
        .search-form { display: flex; gap: 1rem; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-light); border-radius: 50px; font-family: 'Sarabun'; font-size: 0.95rem; background-color: var(--bg-body); transition: 0.2s; }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: var(--bg-surface); box-shadow: 0 0 0 3px var(--primary-light); }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Sarabun'; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-outline { background: transparent; border: 1px solid var(--border-light); color: var(--text-muted); }
        .btn-outline:hover { background: #f1f5f9; color: var(--text-main); }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); background-color: var(--bg-body); border-bottom: 1px solid var(--border-light); text-transform: uppercase; }
        td { padding: 1rem; font-size: 0.95rem; color: var(--text-main); border-bottom: 1px solid var(--border-light); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f8fafc; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-borrowed { background-color: var(--warning-light); color: #d97706; }
        .badge-returned { background-color: var(--success-light); color: var(--success); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">📚 LibLight</div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link"><span>📊</span> ภาพรวมระบบ</a>
            <a href="books.php" class="nav-link"><span>📖</span> คลังหนังสือ</a>
            <a href="borrow.php" class="nav-link"><span>🔄</span> ยืม-คืนหนังสือ</a>
            <a href="history.php" class="nav-link active"><span>📜</span> ประวัติรายการ</a>
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
            <h1 class="page-title">ประวัติรายการ (Transaction History)</h1>
        </div>

        <div class="card" style="padding: 1.25rem 1.5rem;">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="form-control" placeholder="🔍 ค้นหาชื่อหนังสือ หรือ ผู้ทำรายการ..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary">ค้นหา</button>
                <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
                    <a href="history.php" class="btn btn-outline">ล้างค่า</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="padding: 0;">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left: 1.5rem;">รหัสรายการ</th>
                        <th>ชื่อหนังสือ</th>
                        <?php if($userRole === 'admin') echo "<th>ผู้ทำรายการ</th>"; ?>
                        <th>วันที่ยืม</th>
                        <th>วันที่คืน</th>
                        <th style="padding-right: 1.5rem;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $sql_history = "SELECT borrow.id, books.title, users.fullname, borrow.borrow_date, borrow.return_date, borrow.status 
                                    FROM borrow 
                                    JOIN books ON borrow.book_id = books.id 
                                    JOIN users ON borrow.user_id = users.id ";

                    $conditions = [];
                    if ($userRole !== 'admin') {
                        $conditions[] = "users.username = '$currentUser'";
                    }
                    if ($search !== '') {
                        if ($userRole === 'admin') {
                            $conditions[] = "(books.title LIKE '%$search%' OR users.fullname LIKE '%$search%')";
                        } else {
                            $conditions[] = "(books.title LIKE '%$search%')";
                        }
                    }

                    if (count($conditions) > 0) {
                        $sql_history .= " WHERE " . implode(" AND ", $conditions);
                    }

                    $sql_history .= " ORDER BY borrow.id DESC";
                    $result = mysqli_query($conn, $sql_history);

                    if(mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td style="padding-left: 1.5rem; color: var(--text-muted);">#<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['title']); ?></td>
                        
                        <?php if($userRole === 'admin'): ?>
                            <td style="color: var(--primary); font-weight: 500;"><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <?php endif; ?>
                        
                        <td><?php echo date('d/m/Y', strtotime($row['borrow_date'])); ?></td>
                        <td>
                            <?php echo $row['return_date'] ? date('d/m/Y', strtotime($row['return_date'])) : '<span style="color: var(--text-muted);">-</span>'; ?>
                        </td>
                        <td style="padding-right: 1.5rem;">
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
                        <td colspan="<?php echo ($userRole === 'admin') ? '6' : '5'; ?>" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            ไม่พบประวัติการทำรายการ
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>