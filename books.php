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

// ระบบเพิ่มหนังสือ (เฉพาะ Admin)
if (isset($_POST['add_book']) && $userRole === 'admin') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $stock = (int)$_POST['stock'];

    $sql = "INSERT INTO books (title, author, category, stock) VALUES ('$title', '$author', '$category', $stock)";
    mysqli_query($conn, $sql);
    header("Location: books.php");
    exit();
}

// ระบบลบหนังสือ (เฉพาะ Admin)
if (isset($_GET['delete_id']) && $userRole === 'admin') {
    $id = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM books WHERE id = $id");
    header("Location: books.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คลังหนังสือ · LibLight</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ตัวแปรสี Light Theme เหมือน index.php */
        :root {
            --bg-body: #f8fafc; --bg-surface: #ffffff; --border-light: #e2e8f0;
            --text-main: #0f172a; --text-muted: #64748b;
            --primary: #6366f1; --primary-light: #e0e7ff;
            --success: #10b981; --success-light: #d1fae5;
            --danger: #ef4444; --danger-light: #fee2e2;
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

        /* Cards */
        .card { background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 2rem; }
        .card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-main); margin-bottom: 1.25rem; }

        /* Forms & Inputs */
        .search-form { display: flex; gap: 1rem; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-light); border-radius: 8px; font-family: 'Sarabun'; font-size: 0.95rem; color: var(--text-main); transition: 0.2s; background-color: var(--bg-body); }
        .form-control:focus { outline: none; border-color: var(--primary); background-color: var(--bg-surface); box-shadow: 0 0 0 3px var(--primary-light); }
        .search-input { border-radius: 50px; }
        
        .form-grid { display: grid; grid-template-columns: 2fr 2fr 1.5fr 1fr auto; gap: 1rem; align-items: end; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.85rem; font-weight: 500; }
        
        /* Buttons */
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-family: 'Sarabun'; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-outline { background: transparent; border: 1px solid var(--border-light); color: var(--text-muted); }
        .btn-outline:hover { background: #f1f5f9; color: var(--text-main); }
        .btn-search { border-radius: 50px; }
        .btn-danger-sm { background: var(--danger-light); color: var(--danger); text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; transition: 0.2s; }
        .btn-danger-sm:hover { background: #fecaca; }

        /* Table */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); background-color: var(--bg-body); border-bottom: 1px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 1rem; font-size: 0.95rem; color: var(--text-main); border-bottom: 1px solid var(--border-light); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f8fafc; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background-color: var(--success-light); color: var(--success); }
        .badge-danger { background-color: var(--danger-light); color: var(--danger); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">📚 LibLight</div>
        <div class="nav-menu">
            <a href="index.php" class="nav-link"><span>📊</span> ภาพรวมระบบ</a>
            <a href="books.php" class="nav-link active"><span>📖</span> คลังหนังสือ</a>
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
            <h1 class="page-title">คลังหนังสือ (Book Inventory)</h1>
        </div>

        <div class="card" style="padding: 1.25rem 1.5rem;">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="form-control search-input" placeholder="🔍 ค้นหาชื่อหนังสือ, ผู้แต่ง หรือหมวดหมู่..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary btn-search">ค้นหา</button>
                <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
                    <a href="books.php" class="btn btn-outline btn-search">ล้างค่า</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if($userRole === 'admin'): ?>
        <div class="card">
            <div class="card-title">+ เพิ่มหนังสือใหม่เข้าคลัง</div>
            <form method="POST" class="form-grid">
                <div class="form-group">
                    <label>ชื่อหนังสือ</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>ผู้แต่ง</label>
                    <input type="text" name="author" class="form-control">
                </div>
                <div class="form-group">
                    <label>หมวดหมู่</label>
                    <input type="text" name="category" class="form-control">
                </div>
                <div class="form-group">
                    <label>จำนวน (สต็อก)</label>
                    <input type="number" name="stock" class="form-control" value="1" min="1" required>
                </div>
                <button type="submit" name="add_book" class="btn btn-primary" style="height: 42px;">บันทึก</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="card" style="padding: 0;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="padding-left: 1.5rem;">รหัส</th>
                            <th>ชื่อหนังสือ</th>
                            <th>ผู้แต่ง</th>
                            <th>หมวดหมู่</th>
                            <th>สต็อกคงเหลือ</th>
                            <th>สถานะ</th>
                            <?php if($userRole === 'admin') echo "<th style='padding-right: 1.5rem;'>จัดการ</th>"; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                        $sql_books = "SELECT * FROM books";
                        
                        if ($search != '') {
                            $sql_books .= " WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%'";
                        }
                        
                        $sql_books .= " ORDER BY id DESC";
                        $result = mysqli_query($conn, $sql_books);
                        
                        if(mysqli_num_rows($result) > 0):
                            while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td style="padding-left: 1.5rem; color: var(--text-muted);">#<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['author']); ?></td>
                            <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem;"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td style="font-weight: 600; color: <?php echo ($row['stock'] > 0) ? 'var(--success)' : 'var(--danger)'; ?>;"><?php echo $row['stock']; ?> เล่ม</td>
                            <td>
                                <?php if($row['stock'] > 0): ?>
                                    <span class="badge badge-success">พร้อมยืม</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">หมดสต็อก</span>
                                <?php endif; ?>
                            </td>
                            <?php if($userRole === 'admin'): ?>
                            <td style="padding-right: 1.5rem;">
                                <a href="books.php?delete_id=<?php echo $row['id']; ?>" class="btn-danger-sm" onclick="return confirm('ยืนยันการลบหนังสือเล่มนี้?')">ลบ</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                🔍 ไม่พบข้อมูลหนังสือที่คุณค้นหา
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>