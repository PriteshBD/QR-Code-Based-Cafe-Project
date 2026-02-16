<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin/admin_login.php");
    exit();
}

// Handle Add Staff
if (isset($_POST['add_staff'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $role = $conn->real_escape_string($_POST['role']);
    $salary = (float)$_POST['salary'];
    $join_date = $_POST['join_date'];
    
    $sql = "INSERT INTO staff (name, phone, role, salary, join_date) VALUES ('$name', '$phone', '$role', '$salary', '$join_date')";
    $conn->query($sql);
    header("Location: staff_management.php?success=added");
    exit();
}

// Handle Delete Staff
if (isset($_GET['delete'])) {
    $staff_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM staff WHERE staff_id=$staff_id");
    header("Location: staff_management.php?success=deleted");
    exit();
}

// Fetch all staff
$staff_result = $conn->query("SELECT * FROM staff ORDER BY name ASC");

// Fetch stats
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_staff,
        SUM(salary) as total_salary,
        COUNT(DISTINCT role) as roles
    FROM staff
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 { margin: 0; color: #333; }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2em;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 0.9em;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group {
            display: grid;
            gap: 5px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-add:hover {
            background: #218838;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            font-size: 0.85em;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #343a40;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 0.9em;
            }
            
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👥 Staff Management</h1>
        <a href="../admin/admin_dashboard.php" class="back-btn">← Back to Admin</a>
    </div>
    
    <?php if(isset($_GET['success'])): ?>
        <div class="success-msg">
            ✓ Operation completed successfully!
        </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $stats['total_staff']; ?></h3>
            <p>Total Staff</p>
        </div>
        <div class="stat-card">
            <h3>₹<?php echo number_format($stats['total_salary'], 0); ?></h3>
            <p>Total Salary</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['roles']; ?></h3>
            <p>Different Roles</p>
        </div>
    </div>
    
    <div class="container">
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
                    <input type="text" name="role" placeholder="e.g. Chef, Sous Chef" required>
                </div>
                <div class="form-group">
                    <label>Salary (₹)</label>
                    <input type="number" step="0.01" name="salary" required>
                </div>
            </div>
            <div class="form-group">
                <label>Join Date</label>
                <input type="date" name="join_date" required>
            </div>
            <button type="submit" name="add_staff" class="btn btn-add">Add Staff Member</button>
        </form>
    </div>
    
    <div class="container">
        <h2>📋 Current Staff</h2>
        
        <?php if($staff_result->num_rows == 0): ?>
            <p style="text-align: center; color: #666; padding: 20px;">No staff members yet</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Salary</th>
                        <th>Join Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($staff = $staff_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $staff['staff_id']; ?></td>
                        <td><?php echo htmlspecialchars($staff['name']); ?></td>
                        <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                        <td><?php echo htmlspecialchars($staff['role']); ?></td>
                        <td>₹<?php echo number_format($staff['salary'], 2); ?></td>
                        <td><?php echo date('d M, Y', strtotime($staff['join_date'])); ?></td>
                        <td>
                            <a href="?delete=<?php echo $staff['staff_id']; ?>" 
                               class="btn btn-delete"
                               onclick="return confirm('Delete this staff member?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
