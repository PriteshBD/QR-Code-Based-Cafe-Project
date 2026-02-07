<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['last_order_id'])) {
    echo "<p style='font-family:Arial, sans-serif; padding:30px; text-align:center;'>No active order found. <a href=\"menu.php\">Return to menu</a></p>";
    exit();
}

$order_id = (int)$_SESSION['last_order_id']; // ensure integer

// Fetch latest status
$sql = "SELECT * FROM orders WHERE order_id = $order_id";
$result = $conn->query($sql);
$order = $result->fetch_assoc();

if (!$order) {
    echo "<p style='font-family:Arial, sans-serif; padding:30px; text-align:center;'>Order not found. <a href=\"menu.php\">Return to menu</a></p>";
    exit();
}

// UPI Configuration (Replace with your actual UPI ID for the demo)
$my_upi_id = "diwalepritesh-4@oksbi"; 
$amount = number_format((float)$order['total_amount'], 2, '.', '');
$upi_link = "upi://pay?pa=diwalepritesh-4@oksbi" . urlencode($my_upi_id) . "&pn=" . urlencode('PS Cafe') . "&am={$amount}&tn=" . urlencode("Order-$order_id") . "&cu=INR";

// Normalize status
$status_raw = strtolower($order['order_status']);
$status_map = [
    'pending' => 'Pending',
    'cooking' => 'Cooking',
    'ready' => 'Ready',
    'served' => 'Ready',
    'rejected' => 'Rejected'
];
$status = isset($status_map[$status_raw]) ? $status_map[$status_raw] : ucfirst($status_raw);

// Fetch order items (if any)
$order_items_sql = "SELECT oi.*, m.name as item_name, m.price as item_price FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id WHERE oi.order_id = $order_id";
$order_items_res = $conn->query($order_items_sql);

$items = [];
$subtotal = 0.00;
while ($it = $order_items_res->fetch_assoc()) {
    $quantity = (int)$it['quantity'];
    $price = (float)$it['item_price'];
    $line_total = $price * $quantity;
    $it['line_total'] = number_format($line_total, 2, '.', '');
    $it['item_price'] = number_format($price, 2, '.', '');

    // Detect image file for the item (jpg/png/webp) in /images/menu/{item_id}
    $fallback_external = 'https://via.placeholder.com/80?text=No+Image';
    $localBase = __DIR__ . '/images/menu/' . $it['item_id'];
    $relBase = 'images/menu/' . $it['item_id'];
    if (file_exists($localBase . '.jpg')) {
        $it['image'] = $relBase . '.jpg';
    } elseif (file_exists($localBase . '.png')) {
        $it['image'] = $relBase . '.png';
    } elseif (file_exists($localBase . '.webp')) {
        $it['image'] = $relBase . '.webp';
    } else {
        // Try placeholder file first, then external fallback
        $placeholderLocal = __DIR__ . '/images/placeholder.png';
        if (file_exists($placeholderLocal)) {
            $it['image'] = 'images/placeholder.png';
        } else {
            $it['image'] = $fallback_external;
        }
    }

    $items[] = $it;
    $subtotal += $line_total;
}
$subtotal = number_format((float)$subtotal, 2, '.', '');

// Pricing (tax & discounts)
$tax_rate = 0.05; // 5% tax by default — change as needed
$tax = number_format($subtotal * $tax_rate, 2, '.', '');
$discount = isset($order['discount']) ? number_format((float)$order['discount'], 2, '.', '') : '0.00';
$calculated_total = number_format((float)$subtotal + (float)$tax - (float)$discount, 2, '.', '');

// Steps for stepper
$steps = ['Pending', 'Cooking', 'Ready'];
$active_index = array_search($status, $steps);
if ($active_index === false) $active_index = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo htmlspecialchars($order_id); ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg-1: linear-gradient(135deg,#f6d365 0%,#fda085 100%);
            --glass-bg: rgba(255,255,255,0.06);
            /* Increased card opacity for better contrast */
            --card-bg: rgba(255,255,255,0.94);
            --card-text: #111111; /* stronger text on cards */
            --accent: #ff7a59;
            /* Muted color slightly darker for legibility */
            --muted: #6b7280;
            --success: #4CAF50;
            --info: #2196F3;
            --warning: #ff9800;
            --danger: #f44336;
        }
        html,body{height:100%;}
        body{
            margin:0; font-family:'Poppins',system-ui,Arial,sans-serif; background: radial-gradient(circle at 10% 10%, #fff7f1, transparent 10%), var(--bg-1);
            -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; color:#111;
            display:flex; align-items:center; justify-content:center; padding:30px;
        }

        .container{
            width:100%; max-width:920px; background:var(--card-bg); border-radius:16px; padding:28px; box-shadow: 0 8px 30px rgba(2,6,23,0.15);
            backdrop-filter: blur(6px) saturate(120%); border:1px solid rgba(0,0,0,0.06);
            color:var(--card-text);
        }

        .header{
            display:flex; align-items:center; justify-content:space-between; gap:20px;
        }
        .order-id{font-weight:700; font-size:1.25rem;}
        .small{font-size:0.87rem; color:var(--muted);}

        .main{
            display:grid; grid-template-columns: 1fr 320px; gap:24px; margin-top:18px; align-items:start;
        }

        /* Stepper */
        .stepper{background: linear-gradient(180deg, rgba(255,255,255,0.02), transparent); padding:18px; border-radius:12px;}
        .steps{display:flex; gap:12px; justify-content:space-between; align-items:center;}
        .step{flex:1; text-align:center; padding:12px 8px; border-radius:10px; position:relative; transition:transform .2s ease;}
        .step .dot{height:36px; width:36px; border-radius:50%; margin:0 auto 8px; display:flex; align-items:center; justify-content:center; font-weight:600}
        .step.active{transform:translateY(-6px);}
        .step .label{font-size:0.92rem; color:var(--muted); font-weight:600}
        .step.pending .dot{background:var(--warning); color:#fff}
        .step.cooking .dot{background:var(--info); color:#fff}
        .step.ready .dot{background:var(--success); color:#fff}

        /* cards are now high-contrast for readability */
        .card{background: rgba(255,255,255,0.98); border-radius:12px; padding:16px; color:var(--card-text); box-shadow: 0 6px 18px rgba(2,6,23,0.08);}

        /* Payment */
        .pay-panel{text-align:center}
        .qr{background:#fff; padding:12px; border-radius:8px; display:inline-block}
        .pay-amount{font-weight:700; font-size:1.2rem; margin-top:10px}
        .btn{display:inline-block; padding:10px 14px; border-radius:10px; text-decoration:none; color:#111; font-weight:600; margin-top:10px}
        .btn-pay{background:var(--accent)}
        .btn-ghost{background:transparent; border:1px solid rgba(255,255,255,0.08); color:var(--muted)}

        /* Status message */
        .status-banner{padding:14px; border-radius:10px; margin-top:12px; background:linear-gradient(90deg, rgba(255,255,255,0.96), transparent); color:var(--card-text); border:1px solid rgba(0,0,0,0.06); box-shadow:0 4px 12px rgba(2,6,23,0.04);}

        a{color:inherit}

        /* Readability helpers */
        h1,h2{ text-shadow:none; }
        .small{color:var(--muted); font-weight:600}

        /* Responsive */
        @media (max-width:880px){
            .main{grid-template-columns:1fr;}
            .container{padding:18px}
        }

        /* small helper colors for banners */
        .banner-pending{border-left:4px solid var(--warning)}
        .banner-cooking{border-left:4px solid var(--info)}
        .banner-ready{border-left:4px solid var(--success)}
        .banner-rejected{border-left:4px solid var(--danger)}

        /* Print styles */
        .print-area{display:none; background:white; color:#111; padding:20px; width:720px; margin:0 auto;}
        @media print{
            body *{visibility:hidden}
            .print-area, .print-area *{visibility:visible}
            .print-area{position:relative; top:0; left:0; width:100%;}
        }

        /* Theme overrides */
        .theme-light{ --bg-1: linear-gradient(180deg,#ffffff,#f3f4f6); --card-bg: rgba(255,255,255,0.9); color:#111; }
        .theme-dark{ --bg-1: linear-gradient(180deg,#141414,#1f2937); --card-bg: rgba(0,0,0,0.5); color:#e6eef8; }

    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($_SESSION['simulate_msg'])): ?>
            <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:12px;border-radius:8px;margin-bottom:12px;">
                <?php echo htmlspecialchars($_SESSION['simulate_msg']); unset($_SESSION['simulate_msg']); ?>
            </div>
        <?php endif; ?>
        <div class="header">
            <div>
                <div class="order-id">Order #<?php echo htmlspecialchars($order_id); ?> <span class="small">• <?php echo htmlspecialchars($order['order_status']); ?></span></div>
                <div class="small">Placed: <?php echo date('d M Y H:i', strtotime($order['created_at'] ?? date('Y-m-d H:i'))); ?></div>
            </div>

            <div style="text-align:right">
                <div class="small">Total</div>
                <div style="font-weight:700; font-size:1.1rem">₹ <?php echo htmlspecialchars($amount); ?></div>
                <div style="margin-top:8px; display:flex; justify-content:flex-end; gap:8px; align-items:center">
                    <div class="small"><a href="menu.php" class="btn btn-ghost">Back to Menu</a></div>
                    <select id="themeSelect" style="padding:6px 10px; border-radius:8px; background:transparent; color:inherit; border:1px solid rgba(255,255,255,0.06);">
                        <option value="brand">Theme: Brand</option>
                        <option value="light">Theme: Light</option>
                        <option value="dark">Theme: Dark</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="main">
            <div>
                <div class="stepper card">
                    <div class="steps">
                        <?php foreach ($steps as $i => $s):
                            $cls = strtolower($s);
                            $isActive = ($i <= $active_index) ? 'active' : '';
                            $dotLabel = ($i < $active_index) ? '✓' : ($i == $active_index ? '&#9679;' : ($i+1));
                        ?>
                            <div class="step <?php echo htmlspecialchars($cls . ' ' . $isActive); ?>">
                                <div class="dot"><?php echo $dotLabel; ?></div>
                                <div class="label"><?php echo htmlspecialchars($s); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="status-banner <?php echo 'banner-'.strtolower($status); ?>" style="margin-top:16px;">
                        <?php if ($status == 'Pending'): ?>
                            Waiting for confirmation & payment. Estimated confirmation in a few minutes.
                        <?php elseif ($status == 'Cooking'): ?>
                            Your order is being prepared. ETA: <?php echo htmlspecialchars($order['estimated_time'] ?? '—'); ?>
                        <?php elseif ($status == 'Ready'): ?>
                            Your order is ready. Please collect it from the counter. Thank you!
                        <?php elseif ($status == 'Rejected'): ?>
                            Order was canceled. Please approach the counter for assistance.
                        <?php else: ?>
                            Status: <?php echo htmlspecialchars($status); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Optional details -->
                    <div style="margin-top:12px; color:var(--muted); font-size:0.95rem">
                        <strong>Notes:</strong> <?php echo htmlspecialchars($order['notes'] ?? 'No special instructions'); ?>
                    </div>

                    <!-- Itemized order details -->
                    <div class="card" style="margin-top:14px; color:#fff;">
                        <div style="font-weight:700; margin-bottom:10px">Items</div>
                        <?php if (count($items) > 0): ?>
                            <table style="width:100%; border-collapse:collapse; color:#fff;">
                                <thead style="color:var(--muted); font-size:0.9rem; text-align:left">
                                    <tr><th>Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Price</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($items as $it): ?>
                                        <tr style="border-top:1px dashed rgba(255,255,255,0.03);">
                                            <td style="padding:8px 0">
                                                <img src="<?php echo htmlspecialchars($it['image']); ?>" alt="<?php echo htmlspecialchars($it['item_name']); ?>" style="height:48px; width:48px; object-fit:cover; border-radius:8px; vertical-align:middle; margin-right:10px;">
                                                <span style="vertical-align:middle"><?php echo htmlspecialchars($it['item_name']); ?></span>
                                            </td>
                                            <td style="padding:8px 0; text-align:center"><?php echo (int)$it['quantity']; ?></td>
                                            <td style="padding:8px 0; text-align:right">₹ <?php echo htmlspecialchars($it['line_total']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div style="margin-top:12px; text-align:right; color:var(--muted);">
                                <div>Subtotal: ₹ <?php echo htmlspecialchars($subtotal); ?></div>
                                <div>Tax (<?php echo ($tax_rate*100); ?>%): ₹ <?php echo htmlspecialchars($tax); ?></div>
                                <?php if((float)$discount > 0): ?>
                                    <div>Discount: -₹ <?php echo htmlspecialchars($discount); ?></div>
                                <?php endif; ?>
                                <div style="font-weight:700; font-size:1.05rem; margin-top:6px">Total: ₹ <?php echo htmlspecialchars($calculated_total); ?></div>
                                <?php if (abs((float)$calculated_total - (float)$amount) > 0.01): ?>
                                    <div style="font-size:0.86rem; color:var(--muted);">Note: Stored order total is ₹ <?php echo htmlspecialchars($amount); ?> (calculated may differ).</div>
                                <?php endif; ?>
                            </div>

                        <?php else: ?>
                            <div style="color:var(--muted);">No items found for this order.</div>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

            <aside>
                <div class="card pay-panel">
                    <?php if ($status == 'Pending'): ?>
                        <div style="font-size:0.95rem; color:var(--muted);">Payment Required</div>
                        <div class="pay-amount">₹ <?php echo htmlspecialchars($amount); ?></div>

                        <div style="margin-top:12px">
                            <div class="qr">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?php echo urlencode($upi_link); ?>" alt="Scan to Pay" style="display:block; max-width:100%; height:auto;">
                            </div>
                        </div>

                        <div style="margin-top:12px">
                            <a href="#" class="btn btn-pay" id="openUpi">Open in UPI</a>
                            <a href="#" class="btn btn-ghost" id="copyUpi">Copy UPI</a>
                        </div>

                        <?php if (!empty($_SESSION['staff_logged_in']) || (isset($_GET['demo']) && $_GET['demo'] === '1')): ?>
                            <div style="margin-top:10px">
                                <a href="simulate_payment.php?order_id=<?php echo $order_id; ?>&demo=1" class="btn" style="background:#6c757d; color:#fff;" onclick="return confirm('Simulate payment for this order?')">Simulate Payment (Demo)</a>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top:10px; font-size:0.88rem; color:var(--muted)">Scan the QR or open your UPI app to complete payment. Payment will be confirmed automatically.</div>

                    <?php elseif ($status == 'Cooking'): ?>
                        <div style="font-weight:700; font-size:1.05rem">Preparing your order</div>
                        <div class="small" style="margin-top:8px">Estimated time: <?php echo htmlspecialchars($order['estimated_time'] ?? '—'); ?></div>

                    <?php elseif ($status == 'Ready'): ?>
                        <div style="font-weight:700; font-size:1.05rem">Ready for pickup</div>
                        <div class="small" style="margin-top:8px">Please show this screen or your order number at the counter.</div>

                    <?php elseif ($status == 'Rejected'): ?>
                        <div style="font-weight:700; font-size:1.05rem; color:var(--danger)">Order canceled</div>
                        <div class="small" style="margin-top:8px">Contact staff for details.</div>

                    <?php endif; ?>

                </div>

                <div style="margin-top:12px; display:flex; gap:8px; justify-content:center; align-items:center">
                    <button id="printReceipt" class="btn btn-ghost" style="border-radius:10px; padding:8px 12px;">🖨️ Print Receipt</button>
                    <button id="downloadPdf" class="btn btn-pay" style="border-radius:10px; padding:8px 12px;">📥 Download (PDF)</button>
                </div>

                <div style="margin-top:12px; text-align:center; color:var(--muted); font-size:0.9rem">
                    <div>Auto-refreshes every 5s • <a href="menu.php">Back to menu</a></div>
                </div>
            </aside>
        </div>

    </div>

    <!-- Printable receipt (populated server-side for reliability) -->
    <div class="print-area" id="printArea">
        <div style="text-align:center; margin-bottom:10px">
            <h2 style="margin:0">P&S Cafe</h2>
            <div style="font-size:0.9rem; color:#444;">Order Receipt</div>
        </div>

        <div style="margin:10px 0; font-size:0.95rem;">
            <div><strong>Order #<?php echo htmlspecialchars($order_id); ?></strong></div>
            <div>Placed: <?php echo date('d M Y H:i', strtotime($order['created_at'] ?? date('Y-m-d H:i'))); ?></div>
            <div>Status: <?php echo htmlspecialchars($order['order_status']); ?></div>
        </div>

        <table style="width:100%; border-collapse:collapse; font-size:0.95rem">
            <thead>
                <tr><th style="text-align:left; padding:6px 0">Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Amount</th></tr>
            </thead>
            <tbody>
                <?php foreach($items as $it): ?>
                    <tr>
                        <td style="padding:6px 0">
                            <img src="<?php echo htmlspecialchars($it['image']); ?>" alt="<?php echo htmlspecialchars($it['item_name']); ?>" style="height:40px; width:40px; object-fit:cover; border-radius:6px; vertical-align:middle; margin-right:8px;">
                            <span style="vertical-align:middle"><?php echo htmlspecialchars($it['item_name']); ?></span>
                        </td>
                        <td style="text-align:center"><?php echo (int)$it['quantity']; ?></td>
                        <td style="text-align:right">₹ <?php echo htmlspecialchars($it['line_total']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:12px; text-align:right">
            <div>Subtotal: ₹ <?php echo htmlspecialchars($subtotal); ?></div>
            <div>Tax: ₹ <?php echo htmlspecialchars($tax); ?></div>
            <?php if((float)$discount > 0): ?><div>Discount: -₹ <?php echo htmlspecialchars($discount); ?></div><?php endif; ?>
            <div style="font-weight:700; font-size:1.05rem; margin-top:8px">Total: ₹ <?php echo htmlspecialchars($calculated_total); ?></div>
        </div>

        <div style="margin-top:18px; font-size:0.9rem; color:#555">Thank you for ordering from P&S Cafe!</div>
    </div>

    <script>
        (function(){
            const upiLink = '<?php echo addslashes($upi_link); ?>';
            const upiId = '<?php echo addslashes($my_upi_id); ?>';
            const amount = '<?php echo addslashes($amount); ?>';

            // UPI actions
            document.getElementById('openUpi')?.addEventListener('click', function(e){
                e.preventDefault();
                window.location.href = upiLink; // try to open UPI app
            });

            document.getElementById('copyUpi')?.addEventListener('click', function(e){
                e.preventDefault();
                const text = `${upiId} | ₹${amount}`;
                navigator.clipboard?.writeText(text).then(()=>{
                    alert('UPI and amount copied to clipboard');
                }).catch(()=>{ alert('Could not copy to clipboard'); });
            });

            // Theme selector (persisted)
            const themeSelect = document.getElementById('themeSelect');
            function applyTheme(name){
                document.documentElement.classList.remove('theme-light','theme-dark');
                if(name === 'light') document.documentElement.classList.add('theme-light');
                if(name === 'dark') document.documentElement.classList.add('theme-dark');
                localStorage.setItem('ps_theme', name);
            }
            const stored = localStorage.getItem('ps_theme') || 'brand';
            if(stored !== 'brand'){
                applyTheme(stored);
                themeSelect.value = stored;
            }
            themeSelect?.addEventListener('change', function(){ applyTheme(this.value); });

            // Print / Download handlers
            document.getElementById('printReceipt')?.addEventListener('click', function(){
                // show print area (it's always present); direct print
                window.print();
            });

            document.getElementById('downloadPdf')?.addEventListener('click', function(){
                // Trigger print dialog where user can select "Save as PDF"
                window.print();
            });

        })();
    </script>
</body>
</html>