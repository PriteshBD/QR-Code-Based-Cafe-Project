# Phase 5: Setup & Testing Guide

## 🚀 Quick START - Complete the following in order:

### Step 1: Execute Database Migration
Your system needs the new database tables and columns for Phase 5 features. 

**Option A: Using phpMyAdmin (Easiest)**
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select your `cafe_project` database
3. Go to the **SQL** tab
4. Open file: `database/PHASE5_UPDATES.sql`
5. Copy all content and paste into the SQL tab
6. Click **Execute**
7. ✅ Verify: Should see confirmation messages for all tables/columns

**Option B: Using Command Line (Terminal)**
```bash
cd c:\xampp\htdocs\QR_Code_Based_Cafe_Project
mysql -u root cafe_project < database/PHASE5_UPDATES.sql
```

**Option C: Using XAMPP Control Panel**
1. Click "Shell" in XAMPP Control Panel
2. Navigate to project folder:
   ```
   cd c:\xampp\htdocs\QR_Code_Based_Cafe_Project
   ```
3. Run:
   ```
   mysql -u root cafe_project < database/PHASE5_UPDATES.sql
   ```

---

## 📋 Phase 5 Features Overview

### 1. **💳 Manager Payment Approval System**
**Location:** `/staff/payment_approval.php`
**Access:** Staff login required (Manager role)
**Purpose:** Verify online payments and confirm cash receipts

**Workflow:**
```
Customer Places Order
     ↓
Payment Online → Status: "Paid" | OR Cash → Status: "Pending"
     ↓
Manager Reviews in Payment Approval Interface
     ↓
Approve (✓) → "Confirmed" | Reject (✗) → "Failed" | Cash Received (💵) → "Confirmed"
```

**Testing Steps:**
1. Login to `/staff/staff_login.php`
2. Click **"💳 Payment Approvals"** from Manager Dashboard
3. View pending and paid orders with amounts
4. Test three actions:
   - **Approve Payment** - Mark online payment as confirmed
   - **Reject Payment** - Mark payment as failed
   - **Cash Received** - Confirm cash collection

**Expected Results:**
- Pending orders show with red badge
- Approved orders update to green "Confirmed"
- Statistics auto-refresh every 10 seconds
- Notes panel shows payment history

---

### 2. **🎯 Kitchen Display System (KDS)**
**Location:** `/kitchen_display_system.php`
**Access:** Public (no login required - display on kitchen screen)
**Purpose:** Large-screen order queue for kitchen staff

**Features:**
- ✅ Auto-refresh every 5 seconds
- ✅ Order wait time calculation
- ✅ Critical alert when wait > 15 minutes (pulsing red)
- ✅ Pending vs Cooking status filtering
- ✅ Item count with veg/non-veg indicators
- ✅ Optimized for 23"+ monitors

**Testing Steps:**
1. Open in new browser window/tab: `http://localhost/QR_Code_Based_Cafe_Project/kitchen_display_system.php`
2. Keep on kitchen display screen (full screen recommended)
3. Place orders from another browser/device
4. Watch orders appear in Pending Pending section
5. Test responsiveness at different screen sizes:
   - Desktop: 1920px (large fonts)
   - Tablet: 1200px (medium fonts)
   - Mobile: 768px (compact layout)

**Expected Results:**
- Orders sorted by wait time (oldest first)
- Pending orders pulsing (attract attention)
- Wait time displayed in MM:SS format
- Critical threshold (15+ min) shows red background
- Order statistics updated in header

---

### 3. **📋 Temporary Order History (2-Hour Auto-Expire)**
**Location:** `/order_history.php`
**Access:** Public (no login - table_id based)
**Purpose:** Customers see recent orders for 2 hours, then auto-delete

**Features:**
- ✅ 2-hour auto-expiration via SQL
- ✅ No login required (table-based access)
- ✅ Shows served orders only
- ✅ Privacy-first design (transparent about expiration)
- ✅ Order summary with items and pricing

**Testing Steps:**
1. Complete an order and place it
2. Access order history (after order is marked "Served"):
   ```
   http://localhost/QR_Code_Based_Cafe_Project/order_history.php?table_id=1
   ```
3. View order details and items
4. Note the privacy message about 2-hour expiration
5. Return after 2 hours - order should disappear

**Expected Results:**
- Recent served orders visible (max 2 hours old)
- Item-by-item breakdown shown
- Total amount displayed
- Privacy notice visible
- Orders older than 2 hours don't appear

**Database Check:**
```sql
-- Run this in phpMyAdmin SQL tab to verify 2-hour window
SELECT * FROM orders 
WHERE table_id = 1 
AND order_status = 'Served' 
AND TIMESTAMPDIFF(HOUR, updated_at, NOW()) < 2
ORDER BY updated_at DESC;
```

---

### 4. **📦 Inventory Tracking System**
**Location:** `/admin/inventory_tracking.php`
**Access:** Admin login required
**Purpose:** Track stock levels and manage inventory

**Features:**
- ✅ Real-time stock display
- ✅ Low-stock threshold configuration
- ✅ Update stock modal dialog
- ✅ Restock modal with quantity input
- ✅ Color-coded status badges:
  - 🟢 **Sufficient:** Above threshold
  - 🟡 **Low:** Below threshold, still available
  - 🔴 **Out of Stock:** Zero quantity
- ✅ Inventory statistics dashboard

**Testing Steps:**
1. Login to Admin: `/admin/admin_login.php`
2. Click **"📦 Inventory Tracking"** from Admin Dashboard
3. View all menu items with stock levels
4. Test three operations:
   - **Update Stock:** Click item → "Update Stock" button → Enter new quantity
   - **Restock:** Click item → "Restock" button → Enter quantity to add
   - **Set Low Threshold:** Click item → Set threshold for alerts
5. Watch statistics update in real-time

**Expected Results:**
- Stock quantities display correctly
- Low-stock items highlighted in yellow
- Out-of-stock items highlighted in red
- Modal dialogs accept numeric input
- Statistics show:
  - Total items in system
  - Items with sufficient stock
  - Items with low stock
  - Out-of-stock items

**Advanced Testing:**
```sql
-- Check inventory_logs table to see history
SELECT * FROM inventory_logs 
ORDER BY created_at DESC 
LIMIT 10;

-- Verify low-stock alerts work
SELECT item_id, name, stock_quantity, low_stock_threshold,
  CASE 
    WHEN stock_quantity > low_stock_threshold THEN 'Sufficient'
    WHEN stock_quantity > 0 THEN 'Low'
    ELSE 'Out of Stock'
  END as status
FROM menu_items
WHERE stock_quantity <= low_stock_threshold;
```

---

## 🔗 Navigation Updates

### Manager Dashboard
Added three new quick-access buttons:
- 💳 **Payment Approvals** → `/staff/payment_approval.php`
- 🎯 **Kitchen Display System** → `/kitchen_display_system.php` (new tab)
- 📋 **Order History** → `/order_history.php`

### Admin Dashboard  
Added three new quick-access buttons:
- 📦 **Inventory Tracking** → `/admin/inventory_tracking.php`
- 💳 **Payment Approvals** → `/staff/payment_approval.php`
- 🎯 **Kitchen Display** → `/kitchen_display_system.php` (new tab)

---

## 🧪 Complete Test Scenario

**Scenario: Complete Order-to-Payment-to-History Flow**

### Step 1: Customer Orders (2 minutes)
1. Open menu: `http://localhost/QR_Code_Based_Cafe_Project/` (as Table 1)
2. Add items to cart
3. Choose payment:
   - **Online:** System shows QR code
   - **Cash:** Shows "Pay Manager" instruction
4. Place order - note the Order ID

### Step 2: Kitchen Preparation (Continuous)
1. Open KDS: `http://localhost/kitchen_display_system.php` (separate window)
2. Watch order appear in Pending section
3. Mark order as "Cooking" in admin
4. Watch move to Cooking section
5. Monitor wait time increase

### Step 3: Manager Approval (If Online Payment)
1. Login as manager
2. Go to Payment Approval page
3. Find recent order
4. Click "Approve Payment" button
5. Verify order status updates

### Step 4: Order Complete
1. Admin marks order as "Served"
2. KDS clears order from queue

### Step 5: Customer Views History
1. Same customer/table views: `/order_history.php?table_id=1`
2. Sees order in history
3. Returns after 2+ hours - order auto-expires

---

## ⚙️ Database Verification

After running migration, verify all changes:

```sql
-- Verify new columns exist
DESCRIBE menu_items;  -- Should show: stock_quantity, low_stock_threshold, last_restocked

DESCRIBE orders;  -- Should show: approved_by, approval_notes

-- Verify new tables exist
SHOW TABLES LIKE 'inventory%';  -- Should show: inventory_logs
SHOW TABLES LIKE 'payment%';     -- Should show: payment_approvals

-- Verify new view exists
SHOW TABLES WHERE Table_type='VIEW';  -- Should include: kds_pending_orders

-- Test new procedures exist
SHOW PROCEDURE STATUS WHERE Db='cafe_project';
```

---

## 🐛 Troubleshooting

### Issue: "Unknown column 'stock_quantity'"
**Solution:** Database migration not executed
- [ ] Run PHASE5_UPDATES.sql from Step 1

### Issue: "Access Denied" on payment_approval.php
**Solution:** Not logged in as staff
- [ ] Login at `/staff/staff_login.php`
- [ ] Ensure account has Staff role

### Issue: KDS shows "No orders"
**Solution:** Normal if no pending/cooking orders exist
- [ ] Place a new order
- [ ] Mark it as "Pending" or "Cooking" in admin

### Issue: Inventory_tracking missing items
**Solution:** Cache issue
- [ ] Hard refresh: `Ctrl+Shift+Delete` (Windows) or `Cmd+Shift+Delete` (Mac)
- [ ] Clear browser cache

### Issue: Order history shows no orders
**Solution:** Orders might be older than 2 hours
- [ ] Check that orders are marked "Served"
- [ ] Verify order updated_at is recent

---

## 📊 Business Logic Overview

### Payment Workflow
```
Online Payment Flow:
Order Created → Payment Status: "Pending" (awaiting verification)
              → Razorpay Gateway → Payment Success
              → Payment Status: "Paid" (unconfirmed)
              → Manager Review → Approve/Reject
              → Payment Status: "Confirmed" or "Failed"

Cash Payment Flow:
Order Created → Payment Status: "Pending"
              → Customer pays manager
              → Manager marks "Cash Received"
              → Payment Status: "Confirmed"
```

### Kitchen Workflow
```
Order Placed
    ↓
Status: Pending → KDS Displays in Pending Section (pulsing)
    ↓
Chef Starts Cooking → Status: Cooking → KDS shows in Cooking Section
    ↓
Chef Completes → Status: Ready → Admin marks Served
    ↓
Customer receives → Status: Served → Auto-expires from history in 2 hours
```

### Inventory Workflow
```
Item Stock: 50 units
    ↓ (Used in Orders)
    ↓
Stock: 10 units (Below threshold of 30) → "Low Stock" Alert
    ↓ (Admin restocks)
    ↓
Stock: 50 units (Above threshold) → "Sufficient" Badge
```

---

## ✨ Next Phase Recommendations

**Priority 1 (Immediate):**
- [ ] Run database migration
- [ ] Test complete end-to-end workflow
- [ ] Train staff on new interfaces

**Priority 2 (Week 2):**
- [ ] Enable automatic item deactivation when stock = 0
- [ ] Add low-stock email/SMS alerts for managers
- [ ] Create staff training video for KDS

**Priority 3 (Future):**
- [ ] Mobile app version (React Native/Flutter)
- [ ] Per-table QR code generation system
- [ ] Advanced analytics dashboard
- [ ] Inventory alerts integration
- [ ] Predictive demand forecasting

---

## 📞 Support

For issues or feature requests, check:
- Database logs: `SELECT * FROM inventory_logs ORDER BY created_at DESC LIMIT 20;`
- Payment logs: `SELECT * FROM payment_approvals ORDER BY created_at DESC LIMIT 20;`
- Application logs: Browser console (`F12` → Console tab)

**Last Updated:** February 22, 2026
**Version:** Phase 5 Complete
