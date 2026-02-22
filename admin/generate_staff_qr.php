<?php
session_start();
include '../includes/db_connect.php';

// Simple authentication: allow if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Auto-detect server IP address - use HTTP_HOST first (what browser is using)
$server_ip = 'localhost'; // Default fallback

if (!empty($_SERVER['HTTP_HOST'])) {
    // Use what the browser is connecting with
    $http_host = $_SERVER['HTTP_HOST'];
    // Remove port if present
    $server_ip = explode(':', $http_host)[0];
} elseif (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '::1' && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
    // Use SERVER_ADDR if it's not localhost
    $server_ip = $_SERVER['SERVER_ADDR'];
}

// Allow manual IP override
$custom_ip = isset($_GET['ip']) ? $_GET['ip'] : '';
$use_ip = $custom_ip ?: $server_ip;

// Configuration
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'ip'; // 'localhost' or 'ip'
if ($mode == 'localhost') {
    $base_url = "http://localhost/QR_Code_Based_Cafe_Project/staff/qr_login.php?token=";
} else {
    $base_url = "http://" . $use_ip . "/QR_Code_Based_Cafe_Project/staff/qr_login.php?token=";
}

// Fetch all staff from database
$staff_result = $conn->query("SELECT staff_id, name, role, phone FROM staff ORDER BY role, name ASC");
$staff_list = [];
while ($row = $staff_result->fetch_assoc()) {
    $staff_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Staff QR Codes | P&S Cafe</title>
    <link rel="stylesheet" href="admin_styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        .page-header::before {
            content: '🎯';
            position: absolute;
            right: 20px;
            top: -10px;
            font-size: 120px;
            opacity: 0.1;
        }
        .page-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .page-header p {
            margin: 0;
            font-size: 1.1em;
            opacity: 0.95;
            font-weight: 300;
        }
        .header-stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .header-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 16px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .header-stat-value {
            font-size: 1.8em;
            font-weight: 700;
            line-height: 1;
        }
        .header-stat-label {
            font-size: 0.85em;
            opacity: 0.9;
            line-height: 1.2;
        }
        .search-filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .search-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-input {
            flex: 1;
            min-width: 250px;
        }
        .search-input input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.2s;
        }
        .search-input input:focus {
            outline: none;
            border-color: #667eea;
        }
        .role-filter {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .role-btn {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 0.9em;
        }
        .role-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }
        .role-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .qr-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 20px;
            margin-top: 25px;
        }
        .qr-card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            text-align: center; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            page-break-inside: avoid;
            border: 2px solid #e0e0e0;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .qr-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        .qr-card h3 { 
            margin: 0 0 8px 0; 
            color: #333; 
            font-size: 1.3em;
        }
        .qr-card .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .role-chef { background: #ffe0b2; color: #e65100; }
        .role-barista { background: #d7ccc8; color: #5d4037; }
        .role-waiter { background: #bbdefb; color: #1565c0; }
        .role-manager { background: #f3e5f5; color: #6a1b9a; }
        .role-payment_staff { background: #c8e6c9; color: #2e7d32; }
        .qr-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 220px;
            background: #f9f9f9;
            border-radius: 8px;
            margin: 15px 0;
        }
        .qr-card .staff-id { 
            font-size: 0.8em; 
            color: #999; 
            margin-top: 8px;
        }
        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .action-btn {
            padding: 8px 12px;
            font-size: 0.85em;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .action-btn:hover {
            background: #f5f5f5;
            border-color: #667eea;
            color: #667eea;
        }
        .action-btn.primary {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .action-btn.primary:hover {
            background: #5568d3;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn 0.2s;
        }
        .modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            position: relative;
            animation: slideUp 0.3s;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5em;
            cursor: pointer;
            color: #999;
            border: none;
            background: none;
        }
        .modal-close:hover {
            color: #333;
        }
        .modal-title {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #333;
        }
        .modal-qr {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        .modal-url {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.85em;
            color: #555;
            margin: 15px 0;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .modal-actions button {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .copy-btn {
            background: #667eea;
            color: white;
        }
        .copy-btn:hover {
            background: #5568d3;
        }
        .test-btn {
            background: #28a745;
            color: white;
        }
        .test-btn:hover {
            background: #1f7e34;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 2000;
            animation: slideIn 0.3s;
        }
        @keyframes slideIn {
            from { transform: translateX(400px); }
            to { transform: translateX(0); }
        }
        .header-controls {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .mode-selector {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .mode-selector label {
            font-weight: 600;
            margin-right: 10px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
        }
        .info-banner {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-banner h4 {
            margin: 0 0 8px 0;
            color: #1565c0;
        }
        .info-banner p {
            margin: 0;
            color: #555;
            line-height: 1.6;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e0e0e0;
        }
        .stat-box h3 {
            margin: 0;
            font-size: 2em;
            color: #2c3e50;
        }
        .stat-box p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            color: #999;
        }
        @media print {
            .search-filters, .header-controls, .info-banner, .no-print, .card-actions { display: none; }
            .qr-card { 
                page-break-inside: avoid; 
                border: 2px solid #333;
            }
        }
    </style>
</head>
<body class="admin-ui">
    <div class="sidebar">
        <div class="logo">
            <h2>🍽️ P&S Cafe</h2>
            <p>Admin Panel</p>
        </div>
        <nav>
            <a href="admin_dashboard.php">📊 Dashboard</a>
            <a href="manage_menu.php">🍕 Manage Menu</a>
            <a href="view_orders.php">📋 View Orders</a>
            <a href="staff_management.php">👥 Staff</a>
            <a href="generate_qr.php">🔲 Table QR Codes</a>
            <a href="generate_staff_qr.php" class="active">🆔 Staff QR Codes</a>
            <a href="inventory_tracking.php">📦 Inventory</a>
        </nav>
        <a href="../logout.php" class="logout-link">🚪 Logout</a>
    </div>

    <div class="content">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h1>🆔 Staff QR Code Generator</h1>
                    <p>Generate secure QR codes for instant staff login & attendance</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
                    <button onclick="window.print()" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s;">🖨️ Print All</button>
                    <a href="staff_management.php" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; text-decoration: none; display: inline-block;">👥 Manage</a>
                </div>
            </div>
            <div class="header-stats">
                <div class="header-stat">
                    <div>
                        <div class="header-stat-value"><?php echo count($staff_list); ?></div>
                        <div class="header-stat-label">Total Staff</div>
                    </div>
                </div>
                <div class="header-stat">
                    <div>
                        <div class="header-stat-value"><?php echo count(array_unique(array_column($staff_list, 'role'))); ?></div>
                        <div class="header-stat-label">Roles</div>
                    </div>
                </div>
                <div class="header-stat">
                    <div>
                        <div class="header-stat-value">✅</div>
                        <div class="header-stat-label">Active System</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-banner">
            <h4>📱 How Staff QR Codes Work</h4>
            <p>Each staff member gets a unique QR code. When scanned with any QR scanner app, it automatically logs them into their dashboard and marks attendance. Print these QR codes and give them to staff members for quick login.</p>
        </div>

        <!-- Search & Filter Section -->
        <div class="search-filters no-print">
            <div class="search-row">
                <div class="search-input">
                    <input type="text" id="staffSearch" placeholder="🔍 Search staff by name..." onkeyup="filterStaff()">
                </div>
                <div class="role-filter" id="roleFilter">
                    <button class="role-btn active" data-role="all" onclick="filterByRole('all')">All Roles</button>
                    <?php foreach (array_unique(array_column($staff_list, 'role')) as $role): ?>
                    <button class="role-btn" data-role="<?php echo strtolower($role); ?>" onclick="filterByRole('<?php echo $role; ?>')">
                        <?php echo htmlspecialchars($role); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="header-controls no-print">
            <div class="mode-selector">
                <label>🌐 URL Mode:</label>
                <select onchange="window.location='?mode='+this.value" style="padding:8px 12px; border-radius:6px; border:1px solid #ddd;">
                    <option value="ip" <?php echo $mode=='ip'?'selected':''; ?>>IP Address (<?php echo $use_ip; ?>)</option>
                    <option value="localhost" <?php echo $mode=='localhost'?'selected':''; ?>>Localhost</option>
                </select>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print All</button>
                <a href="staff_management.php" class="btn btn-secondary">👥 Manage Staff</a>
            </div>
        </div>

        <?php if (count($staff_list) > 0): ?>
            <div class="qr-grid">
                <?php foreach ($staff_list as $staff): 
                    // Generate secure token: base64(staff_id:phone_hash)
                    $phone_trimmed = trim($staff['phone']); // Trim whitespace to ensure consistency
                    $phone_hash = md5($phone_trimmed . 'cafe_secret_2026');
                    $token = base64_encode($staff['staff_id'] . ':' . $phone_hash);
                    $qr_url = $base_url . urlencode($token);
                    
                    // Determine role badge class
                    $role_class = 'role-' . strtolower($staff['role']);
                    $staff_key = 'staff_' . $staff['staff_id'];
                ?>
                    <div class="qr-card" data-staff-name="<?php echo htmlspecialchars(strtolower($staff['name'])); ?>" data-staff-role="<?php echo strtolower($staff['role']); ?>">
                        <h3><?php echo htmlspecialchars($staff['name']); ?></h3>
                        <span class="role-badge <?php echo $role_class; ?>">
                            <?php 
                            $icons = ['Chef' => '👨‍🍳', 'Barista' => '☕', 'Waiter' => '🚶', 'Manager' => '👔', 'Payment Staff' => '💳'];
                            echo isset($icons[$staff['role']]) ? $icons[$staff['role']] . ' ' : '';
                            echo htmlspecialchars($staff['role']); 
                            ?>
                        </span>
                        <div class="qr-container" id="<?php echo $staff_key; ?>"></div>
                        <div class="staff-id">Staff ID: #<?php echo $staff['staff_id']; ?></div>
                        <div style="font-size:0.75em; color:#999; margin-top:8px; word-break:break-all; padding:8px; background:#f9f9f9; border-radius:6px;">
                            Scan to login instantly
                        </div>
                        <div class="card-actions no-print">
                            <button class="action-btn" onclick="showQRModal('<?php echo $staff['staff_id']; ?>', '<?php echo htmlspecialchars($staff['name']); ?>', '<?php echo addslashes($qr_url); ?>')">👁️ Preview</button>
                            <button class="action-btn" onclick="copyToClipboard('<?php echo addslashes($qr_url); ?>')">📋 Copy</button>
                            <a href="<?php echo $qr_url; ?>" target="_blank" class="action-btn primary">🔗 Test</a>
                        </div>
                        <script>
                        (function() {
                            try {
                                new QRCode(document.getElementById('<?php echo $staff_key; ?>'), {
                                    text: '<?php echo $qr_url; ?>',
                                    width: 200,
                                    height: 200,
                                    correctLevel: QRCode.CorrectLevel.H,
                                    colorDark: '#000000',
                                    colorLight: '#ffffff'
                                });
                            } catch(e) {
                                document.getElementById('<?php echo $staff_key; ?>').innerHTML = '<p style="color:red; padding:20px;">⚠️ QR code generation failed</p>';
                            }
                        })();
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:60px 20px; background:white; border-radius:12px;">
                <h2 style="color:#999;">No Staff Members Found</h2>
                <p style="color:#aaa;">Add staff members first to generate QR codes.</p>
                <a href="staff_management.php" class="btn btn-primary" style="margin-top:15px;">👥 Add Staff Members</a>
            </div>
        <?php endif; ?>

        <div class="info-banner" style="margin-top:30px;">
            <h4>💡 Tips</h4>
            <p>
                • Print these QR codes and laminate them for durability<br>
                • Staff can save the QR image on their phone for quick access<br>
                • QR codes are secure with encrypted tokens<br>
                • Each scan automatically marks attendance as "Present"
            </p>
        </div>
    </div>

    <!-- QR Preview Modal -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeQRModal()">&times;</button>
            <h3 class="modal-title" id="modalTitle">QR Code Preview</h3>
            <div class="modal-qr" id="modalQRContainer"></div>
            <div class="modal-url" id="modalURL"></div>
            <div class="modal-actions">
                <button class="copy-btn" onclick="copyFromModal()">📋 Copy URL</button>
                <button class="test-btn" onclick="testQRFromModal()">🔗 Test Login</button>
            </div>
        </div>
    </div>

    <script>
        let currentModalQRUrl = '';
        let currentModalStaffId = '';

        // Filter by search
        function filterStaff() {
            const searchTerm = document.getElementById('staffSearch').value.toLowerCase();
            const cards = document.querySelectorAll('.qr-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const staffName = card.getAttribute('data-staff-name');
                if (staffName.includes(searchTerm)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            showNoResults(visibleCount === 0);
        }

        // Filter by role
        function filterByRole(role) {
            const cards = document.querySelectorAll('.qr-card');
            const buttons = document.querySelectorAll('.role-btn');
            let visibleCount = 0;

            // Update button states
            buttons.forEach(btn => {
                if (btn.getAttribute('data-role') === role.toLowerCase() || role === 'all') {
                    if (role === 'all') {
                        btn.classList.toggle('active');
                    } else {
                        buttons.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                    }
                }
            });

            // Filter cards
            cards.forEach(card => {
                const staffRole = card.getAttribute('data-staff-role');
                if (role === 'all' || staffRole === role.toLowerCase()) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            showNoResults(visibleCount === 0);
        }

        // Show/hide no results message
        function showNoResults(show) {
            const grid = document.querySelector('.qr-grid');
            let noResults = document.querySelector('.no-results');
            
            if (show && !noResults) {
                noResults = document.createElement('div');
                noResults.className = 'no-results';
                noResults.innerHTML = '<h3>❌ No staff members found</h3><p>Try adjusting your search or filters</p>';
                grid.parentElement.insertBefore(noResults, grid.nextSibling);
            } else if (!show && noResults) {
                noResults.remove();
            }
        }

        // Show QR modal
        function showQRModal(staffId, staffName, qrUrl) {
            currentModalQRUrl = qrUrl;
            currentModalStaffId = staffId;

            document.getElementById('modalTitle').textContent = '🆔 ' + staffName + ' QR Code';
            document.getElementById('modalURL').textContent = qrUrl;
            
            const container = document.getElementById('modalQRContainer');
            container.innerHTML = '';
            
            try {
                new QRCode(container, {
                    text: qrUrl,
                    width: 250,
                    height: 250,
                    correctLevel: QRCode.CorrectLevel.H,
                    colorDark: '#000000',
                    colorLight: '#ffffff'
                });
            } catch(e) {
                container.innerHTML = '<p style="color:red;">Failed to generate QR code</p>';
            }

            document.getElementById('qrModal').classList.add('show');
        }

        // Close modal
        function closeQRModal() {
            document.getElementById('qrModal').classList.remove('show');
        }

        // Copy URL to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('✅ URL copied to clipboard!');
            }).catch(() => {
                showToast('❌ Failed to copy');
            });
        }

        // Copy from modal
        function copyFromModal() {
            copyToClipboard(currentModalQRUrl);
        }

        // Test QR from modal
        function testQRFromModal() {
            window.open(currentModalQRUrl, '_blank');
        }

        // Show toast notification
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Close modal when clicking outside
        document.getElementById('qrModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQRModal();
            }
        });
    </script>
</body>
</html>
