<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// --- DATA FOR ANALYTICS ---
// 1. Total Revenue
$rev_res = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status='Paid'");
$total_revenue = $rev_res->fetch_assoc()['total'];

// 2. Top Selling Items (For Graph)
$top_items = $conn->query("SELECT m.name, COUNT(oi.item_id) as count FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id GROUP BY oi.item_id ORDER BY count DESC LIMIT 5");

$item_names = [];
$item_counts = [];
while($row = $top_items->fetch_assoc()) {
    $item_names[] = $row['name'];
    $item_counts[] = $row['count'];
}

// --- STAFF MANAGEMENT HANDLERS ---
// Add Staff
if (isset($_POST['add_staff'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];
    $conn->query("INSERT INTO staff (name, role, phone, salary_per_day) VALUES ('$name', '$role', '$phone', '$salary')");
    header("Location: admin_dashboard.php");
}

// Mark Attendance
if (isset($_POST['mark_attendance'])) {
    $staff_id = $_POST['staff_id'];
    $status = $_POST['status'];
    $date = date('Y-m-d');
    $conn->query("INSERT INTO attendance (staff_id, date, status) VALUES ('$staff_id', '$date', '$status') ON DUPLICATE KEY UPDATE status='$status'");
    header("Location: admin_dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard | P&S Cafe</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f6f9; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: #343a40; color: white; min-height: 100vh; padding: 20px; }
        .sidebar h2 { text-align: center; color: #ff9800; }
        .menu-item { display: block; padding: 15px; color: #ccc; text-decoration: none; border-bottom: 1px solid #4b545c; }
        .menu-item:hover { background: #494e53; color: white; }

        /* Content */
        .content { flex: 1; padding: 20px; }
        
        /* Stats Cards */
        .stats-grid { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 5px solid #28a745; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 30px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #343a40; color: white; }

        /* Forms */
        .form-inline { display: flex; gap: 10px; }
        input, select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>P&S Admin</h2>
        <a href="#" class="menu-item">📊 Dashboard</a>
        <a href="menu.php" target="_blank" class="menu-item">📱 View Live Menu</a>
        <a href="staff_login.php" target="_blank" class="menu-item">👨‍🍳 Kitchen View</a>
        <a href="admin_login.php" class="menu-item">🚪 Logout</a>
    </div>

    <div class="content">
        <h1>Dashboard Overview</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <h1>₹<?php echo $total_revenue ? $total_revenue : '0'; ?></h1>
            </div>
            <div class="stat-card" style="border-color: #17a2b8;">
                <h3>Top Seller</h3>
                <h1><?php echo !empty($item_names) ? $item_names[0] : 'N/A'; ?></h1>
            </div>
        </div>

        <div style="background:white; padding:20px; border-radius:8px; margin-bottom:30px;">
            <h3>📈 Best Selling Items</h3>
            <canvas id="salesChart" style="max-height: 300px;"></canvas>
        </div>

        <div style="background:white; padding:20px; border-radius:8px;">
            <h3>👥 Staff Management & Attendance</h3>
            
            <form method="POST" class="form-inline" style="margin-bottom:20px; background:#eee; padding:15px;">
                <input type="text" name="name" placeholder="Name" required>
                <select name="role"><option>Chef</option><option>Waiter</option><option>Cleaner</option></select>
                <input type="text" name="phone" placeholder="Phone" required>
                <input type="number" name="salary" placeholder="Salary/Day" required>
                <button type="submit" name="add_staff">Add Staff</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Today's Status</th>
                        <th>Mark Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $staff_res = $conn->query("SELECT s.*, a.status as today_status FROM staff s LEFT JOIN attendance a ON s.staff_id = a.staff_id AND a.date = CURDATE()");
                    while($s = $staff_res->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $s['staff_id']; ?></td>
                        <td><?php echo $s['name']; ?></td>
                        <td><?php echo $s['role']; ?></td>
                        <td style="font-weight:bold; color: <?php echo ($s['today_status']=='Present')?'green':'red'; ?>">
                            <?php echo $s['today_status'] ? $s['today_status'] : 'Not Marked'; ?>
                        </td>
                        <td>
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="staff_id" value="<?php echo $s['staff_id']; ?>">
                                <button type="submit" name="mark_attendance" value="Present" style="background:#28a745;">P</button>
                                <button type="submit" name="mark_attendance" value="Absent" style="background:#dc3545;">A</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($item_names); ?>,
                datasets: [{
                    label: 'Units Sold',
                    data: <?php echo json_encode($item_counts); ?>,
                    backgroundColor: ['#ff9800', '#4caf50', '#2196f3', '#e74c3c', '#9b59b6']
                }]
            }
        });
    </script>

</body>
</html>