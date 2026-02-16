<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$staff_name = $_SESSION['staff_name'];

// Fetch staff details
$staff = $conn->query("SELECT * FROM staff WHERE staff_id=$staff_id")->fetch_assoc();

// Fetch current shift status
$today = date('Y-m-d');
$attendance = $conn->query("SELECT * FROM attendance WHERE staff_id=$staff_id AND date='$today'")->fetch_assoc();

// Fetch Monthly attendance
$current_month = date('Y-m');
$attendance_records = $conn->query("
    SELECT * FROM attendance 
    WHERE staff_id=$staff_id 
    AND MONTH(date)=MONTH(CURDATE()) 
    AND YEAR(date)=YEAR(CURDATE())
    ORDER BY date DESC
");

// Fetch stats
$stats = $conn->query("
    SELECT 
        SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent_count,
        COUNT(*) as total_days
    FROM attendance 
    WHERE staff_id=$staff_id 
    AND MONTH(date)=MONTH(CURDATE())
    AND YEAR(date)=YEAR(CURDATE())
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
            padding-bottom: 30px;
        }
        
        .header {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .header h1 {
            font-size: 1.5em;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .profile-section {
            background: white;
            color: #333;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .profile-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .profile-info h2 {
            font-size: 2em;
            margin-bottom: 10px;
            color: #333;
        }
        
        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .detail-icon {
            font-size: 1.3em;
            min-width: 25px;
        }
        
        .detail-content {
            flex: 1;
        }
        
        .detail-label {
            font-size: 0.85em;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .detail-value {
            font-weight: bold;
            color: #333;
            font-size: 1.05em;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }
        
        .status-present {
            background: #d4edda;
            color: #155724;
        }
        
        .status-absent {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .stat-card p {
            font-size: 0.85em;
            opacity: 0.9;
        }
        
        .section-title {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .attendance-table thead {
            background: #f8f9fa;
        }
        
        .attendance-table th,
        .attendance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .attendance-table th {
            font-weight: bold;
            color: #333;
        }
        
        .attendance-table tr:hover {
            background: #f8f9fa;
        }
        
        .empty-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .profile-header {
                grid-template-columns: 1fr;
            }
            
            .profile-section {
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👤 My Profile</h1>
        <a href="staff_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <!-- Profile Card -->
        <div class="profile-section">
            <div class="profile-header">
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($staff['name']); ?></h2>
                    <span class="status-badge <?php echo ($attendance && $attendance['status'] == 'Present') ? 'status-present' : 'status-absent'; ?>">
                        <?php echo ($attendance && $attendance['status'] == 'Present') ? '✓ PRESENT TODAY' : 'NOT CHECKED IN'; ?>
                    </span>
                </div>
                
                <div class="profile-details">
                    <div class="detail-row">
                        <span class="detail-icon">👔</span>
                        <div class="detail-content">
                            <div class="detail-label">Role</div>
                            <div class="detail-value"><?php echo htmlspecialchars($staff['role']); ?></div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <span class="detail-icon">📱</span>
                        <div class="detail-content">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value"><?php echo htmlspecialchars($staff['phone']); ?></div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <span class="detail-icon">💰</span>
                        <div class="detail-content">
                            <div class="detail-label">Salary</div>
                            <div class="detail-value">₹<?php echo number_format($staff['salary'], 2); ?></div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <span class="detail-icon">📅</span>
                        <div class="detail-content">
                            <div class="detail-label">Join Date</div>
                            <div class="detail-value"><?php echo date('d M, Y', strtotime($staff['join_date'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Stats -->
            <h3 class="section-title">📊 Attendance Statistics (This Month)</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['present_count'] ?: 0; ?></h3>
                    <p>Days Present</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['absent_count'] ?: 0; ?></h3>
                    <p>Days Absent</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['total_days'] ?: 0; ?></h3>
                    <p>Total Recorded</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['total_days'] > 0 ? round(($stats['present_count'] / $stats['total_days']) * 100) : 0; ?>%</h3>
                    <p>Attendance Rate</p>
                </div>
            </div>
        </div>
        
        <!-- Attendance Records -->
        <div class="profile-section">
            <h3 class="section-title">📋 Attendance History</h3>
            
            <?php if($attendance_records->num_rows == 0): ?>
                <div class="empty-message">
                    No attendance records for this month yet
                </div>
            <?php else: ?>
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Status</th>
                            <th>Marked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($att = $attendance_records->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d M, Y', strtotime($att['date'])); ?></td>
                            <td><?php echo date('l', strtotime($att['date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($att['status']); ?>">
                                    <?php echo $att['status'] == 'Present' ? '✓ Present' : '✗ Absent'; ?>
                                </span>
                            </td>
                            <td><?php echo $att['marked_at'] ? date('h:i A', strtotime($att['marked_at'])) : '-'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
