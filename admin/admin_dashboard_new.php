<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// --- HANDLE ALL POST REQUESTS ---

// Add Staff
if (isset($_POST['add_staff'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = $conn->real_escape_string($_POST['role']);
    $salary = (float)$_POST['salary'];
    $join_date = $_POST['join_date'];
    
    $sql = "INSERT INTO staff (name, phone, role, salary, join_date) VALUES ('$name', '$phone', '$role', '$salary', '$join_date')";
    $conn->query($sql);
}

// Update Staff
if (isset($_POST['update_staff'])) {
    $staff_id = (int)$_POST['staff_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = $conn->real_escape_string($_POST['role']);
    $salary = (float)$_POST['salary'];
    $join_date = $_POST['join_date'];
    
    $sql = "UPDATE staff SET name='$name', phone='$phone', role='$role', salary=$salary, join_date='$join_date' WHERE staff_id=$staff_id";
    $conn->query($sql);
}

// Delete Staff
if (isset($_GET['delete_staff'])) {
    $staff_id = (int)$_GET['delete_staff'];
    $conn->query("DELETE FROM staff WHERE staff_id=$staff_id");
}

// Mark Attendance
if (isset($_POST['mark_attendance'])) {
    $staff_id = $_POST['staff_id'];
    $status = $_POST['mark_attendance'];
    $date = date('Y-m-d');
    $conn->query("INSERT INTO attendance (staff_id, date, status) VALUES ('$staff_id', '$date', '$status') ON DUPLICATE KEY UPDATE status='$status'");
}

// Mark all absent staff
if (isset($_POST['mark_all_absent'])) {
    $date = date('Y-m-d');
    $staff_result = $conn->query("SELECT staff_id FROM staff");
    while($staff = $staff_result->fetch_assoc()) {
        $staff_id = $staff['staff_id'];
        $check = $conn->query("SELECT * FROM attendance WHERE staff_id = $staff_id AND date = '$date'");
        if($check->num_rows == 0) {
            $conn->query("INSERT INTO attendance (staff_id, date, status) VALUES ($staff_id, '$date', 'Absent')");
        }
    }
}

// --- FETCH DATA ---

// Analytics
$rev_res = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status='Paid'");
$total_revenue = $rev_res->fetch_assoc()['total'];

$top_items = $conn->query("SELECT m.name, COUNT(oi.item_id) as count FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id GROUP BY oi.item_id ORDER BY count DESC LIMIT 5");
$item_names = [];
$item_counts = [];
while($row = $top_items->fetch_assoc()) {
    $item_names[] = $row['name'];
    $item_counts[] = $row['count'];
}

// Staff stats
$stats = $conn->query("SELECT COUNT(*) as total_staff, SUM(salary) as total_salary, COUNT(DISTINCT role) as roles FROM staff")->fetch_assoc();

// Fetch all staff
$staff_result = $conn->query("SELECT * FROM staff ORDER BY name ASC");

// Fetch orders
$orders_result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");

// Menu items
$menu_result = $conn->query("SELECT * FROM menu_items ORDER BY category")
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | P&S Cafe</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; }
        
        .admin-container { min-height: 100vh; }

        .top-bar {
            background: #343a40;
            color: white;
            padding: 16px 20px;
        }

        .top-bar h2 { color: #ff9800; margin-bottom: 12px; }

        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tab-button {
            background: transparent;
            color: #ccc;
            border: 1px solid #4b545c;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .tab-button:hover, .tab-button.active {
            background: #494e53;
            color: white;
        }

        .top-links {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .top-link {
            color: #ccc;
            text-decoration: none;
            border: 1px solid #4b545c;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .top-link:hover { background: #494e53; color: white; }

        /* Content */
        .content {
            padding: 20px;
        }
        
        /* Sections */
        .section { display: none; }
        .section.active { display: block; }
        
        /* Cards & Tables */
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-left: 5px solid #28a745; }
        .stat-card h3 { color: #666; margin-bottom: 10px; }
        .stat-card h1 { color: #007bff; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #343a40; color: white; }
        tr:hover { background: #f8f9fa; }
        
        /* Forms */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; }
        label { margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: all 0.3s; }
        button:hover { background: #0056b3; }
        
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        
        .btn-sm { padding: 6px 12px; font-size: 0.85em; }
        
        h1 { color: #333; margin-bottom: 20px; }
        h2 { color: #555; margin: 20px 0 10px 0; }
        
        .success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        
        @media (max-width: 768px) {
            .tabs { gap: 8px; }
            .tab-button { flex: 1; text-align: center; }
            .top-links { gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="top-bar">
            <h2>P&S Admin</h2>
            <div class="tabs" role="tablist" aria-label="Admin Sections">
                <button class="tab-button active" data-section="dashboard" role="tab" aria-selected="true">📊 Dashboard</button>
                <button class="tab-button" data-section="orders" role="tab" aria-selected="false">📦 View Orders</button>
                <button class="tab-button" data-section="menu" role="tab" aria-selected="false">📋 Manage Menu</button>
                <button class="tab-button" data-section="staff" role="tab" aria-selected="false">👥 Manage Staff</button>
            </div>
            <div class="top-links">
                <a href="../menu.php" class="top-link" target="_blank">📱 View Live Menu</a>
                <a href="../staff/staff_login.php" class="top-link" target="_blank">👨‍🍳 Kitchen View</a>
                <a href="../logout.php" class="top-link">🚪 Logout</a>
            </div>
        </div>

        <div class="content">
            <!-- DASHBOARD SECTION -->
            <div id="dashboard" class="section active">
                <h1>Dashboard Overview</h1>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Revenue</h3>
                        <h1>₹<?php echo $total_revenue ? $total_revenue : '0'; ?></h1>
                    </div>
                    <div class="stat-card">
                        <h3>Total Staff</h3>
                        <h1><?php echo $stats['total_staff']; ?></h1>
                    </div>
                    <div class="stat-card">
                        <h3>Staff Roles</h3>
                        <h1><?php echo $stats['roles']; ?></h1>
                    </div>
                    <div class="stat-card">
                        <h3>Top Seller</h3>
                        <h1><?php echo !empty($item_names) ? $item_names[0] : 'N/A'; ?></h1>
                    </div>
                </div>

                <div class="card">
                    <h2>📈 Best Selling Items</h2>
                    <canvas id="salesChart" style="max-height: 300px;"></canvas>
                </div>

                <div class="card">
                    <h2>👥 Staff Management & Attendance</h2>
                    <form method="POST" style="margin-bottom: 15px;">
                        <button type="submit" name="mark_all_absent" class="btn-success" onclick="return confirm('Mark all unmarked staff as Absent?');">📅 Mark Remaining as Absent</button>
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
                                    <form method="POST" style="display: flex; gap: 5px;">
                                        <input type="hidden" name="staff_id" value="<?php echo $s['staff_id']; ?>">
                                        <button type="submit" name="mark_attendance" value="Present" class="btn btn-sm btn-success">P</button>
                                        <button type="submit" name="mark_attendance" value="Absent" class="btn btn-sm btn-danger">A</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ORDERS SECTION -->
            <div id="orders" class="section">
                <h1>📦 View Orders</h1>
                
                <div class="card">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Table</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders_result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
                            while($order = $orders_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo $order['table_id']; ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span style="background: #e3f2fd; padding: 5px 10px; border-radius: 5px;"><?php echo $order['order_status']; ?></span></td>
                                <td><?php echo $order['payment_status']; ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MENU SECTION -->
            <div id="menu" class="section">
                <h1>📋 Manage Menu</h1>
                
                <div class="card">
                    <h2>All Menu Items</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Available</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $menu_result = $conn->query("SELECT * FROM menu_items ORDER BY category");
                            while($item = $menu_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $item['item_id']; ?></td>
                                <td><?php echo $item['name']; ?></td>
                                <td><?php echo $item['category']; ?></td>
                                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['is_available'] ? '✓ Yes' : '✗ No'; ?></td>
                                <td><?php echo $item['stock_quantity']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- STAFF SECTION -->
            <div id="staff" class="section">
                <h1>👥 Manage Staff</h1>
                
                <div class="card">
                    <h2>➕ Add New Staff Member</h2>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" required>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" name="role" placeholder="e.g. Chef, Waiter" required>
                            </div>
                            <div class="form-group">
                                <label>Salary (₹/Day)</label>
                                <input type="number" step="0.01" name="salary" required>
                            </div>
                            <div class="form-group">
                                <label>Join Date</label>
                                <input type="date" name="join_date" required>
                            </div>
                        </div>
                        <button type="submit" name="add_staff" class="btn btn-success" style="width: 100%;">Add Staff Member</button>
                    </form>
                </div>

                <div class="card">
                    <h2>📋 Current Staff</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Salary</th>
                                <th>Join Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $staff_result = $conn->query("SELECT * FROM staff ORDER BY name ASC");
                            while($staff = $staff_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $staff['staff_id']; ?></td>
                                <td><?php echo $staff['name']; ?></td>
                                <td><?php echo $staff['phone']; ?></td>
                                <td><?php echo $staff['role']; ?></td>
                                <td>₹<?php echo number_format($staff['salary'], 2); ?></td>
                                <td><?php echo $staff['join_date']; ?></td>
                                <td>
                                    <a href="?delete_staff=<?php echo $staff['staff_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this staff member?')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));

            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });

            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Initialize chart if dashboard is shown
            if (sectionId === 'dashboard') {
                initChart();
            }
        }

        function initChart() {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;
            
            // Destroy existing chart if any
            if (window.salesChart) {
                window.salesChart.destroy();
            }
            
            window.salesChart = new Chart(ctx, {
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
        }

        // Wire up tab buttons
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.addEventListener('click', () => {
                showSection(btn.dataset.section);
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
            });
        });

        // Initialize chart on page load
        window.addEventListener('load', () => {
            initChart();
        });
    </script>

</body>
</html>
