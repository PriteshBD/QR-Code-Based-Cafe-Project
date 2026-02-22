# Phase 5 System Architecture & Workflow Diagrams

## 📊 Complete System Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    QR CODE CAFE ORDERING SYSTEM - PHASE 5                │
│                      Complete Architecture Overview                       │
└─────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────┐
                              │   CUSTOMER  │
                              └──────┬──────┘
                                     │
                    ┌────────────────┼────────────────┐
                    │                │                │
            ┌───────▼────────┐  ┌───▼────────┐  ┌───▼─────────────┐
            │   Menu & Cart  │  │  Checkout  │  │  Payment Setup  │
            │   Selection    │  │  (Total)   │  │  (Online/Cash)  │
            └────────────────┘  └────────────┘  └────────┬────────┘
                                                         │
                        ┌────────────────────────────────┼────────────────┐
                        │                                │                │
                  ┌─────▼─────┐                   ┌─────▼────────┐  ┌───▼───┐
                  │   ONLINE   │                   │    CASH      │  │ QR    │
                  │  PAYMENT   │                   │  PAYMENT     │  │DISPLAY│
                  └─────┬──────┘                   └──────┬───────┘  └───┬───┘
                        │                                 │              │
                  ┌─────▼──────────────────────────────────▼──────────────▼─────┐
                  │                  ORDER CREATED IN SYSTEM                    │
                  │              Status: PENDING, Payment: PENDING              │
                  └─────┬──────────────────────────────────┬─────────────────────┘
                        │                                  │
        ┌───────────────┼──────────────────────────────────┼──────────────────┐
        │               │                                  │                  │
        │         ┌─────▼─────────────┐          ┌─────────▼────────┐   │
        │         │  RAZORPAY GATEWAY │          │   NO ACTION YET  │   │
        │         │  (Payment Success)│          │   (Waiting...)   │   │
        │         └─────┬─────────────┘          └─────┬────────────┘   │
        │               │                              │                │
        │         ┌─────▼──────────────┐        ┌──────▼──────────┐    │
        │         │ Status: PAID       │        │  Status: PENDING│    │
        │         │ (Unconfirmed)      │        │  (Unconfirmed)  │    │
        │         └─────┬──────────────┘        └──────┬──────────┘    │
        │               │                             │                │
        └───────────────┼─────────────────────────────┼────────────────┘
                        │
        ┌───────────────▼────────────────────────────────────────────┐
        │         MANAGER PAYMENT APPROVAL SYSTEM                    │
        │     (/staff/payment_approval.php - Auto-refresh 10s)       │
        │                                                          │
        │  ┌──────────────────────────────────────────────────┐   │
        │  │  PAYMENT STATUS DASHBOARD                        │   │
        │  │  ┌─────────────────────────────────────────────┐ │   │
        │  │  │ Statistics:                                 │ │   │
        │  │  │ • Pending Payments: 5 | ₹2,500            │ │   │
        │  │  │ • Paid Awaiting Confirmation: 3 | ₹1,500  │ │   │
        │  │  │ • Today's Confirmed: ₹15,000             │ │   │
        │  │  └─────────────────────────────────────────────┘ │   │
        │  │                                                  │   │
        │  │  PENDING ORDERS:              PAID ORDERS:       │   │
        │  │  Order #1 (Cash)              Order #4 (Online)  │   │
        │  │  ├─ Amount: ₹500              ├─ Amount: ₹800    │   │
        │  │  ├─ QR: [Display]             ├─ QR: [Display]   │   │
        │  │  └─ [✓Approve][✗Reject]       └─ [✓Approve]       │   │
        │  │                                                  │   │
        │  │  Order #2 (Cash)              Order #5 (Online)  │   │
        │  │  ├─ Amount: ₹750              ├─ Amount: ₹600    │   │
        │  │  ├─ QR: [Display]             ├─ QR: [Display]   │   │
        │  │  └─ [💵Cash Received]          └─ [✓Approve]       │   │
        │  │                                                  │   │
        │  └──────────────────────────────────────────────────┘   │
        │                                                          │
        └───────────────┬────────────────────────────────────────┘
                        │
        ┌───────────────┴─────────────────────────────────────────┐
        │                                                         │
    ┌───▼─────────────┐                            ┌────────▼────┐
    │  APPROVED ✓     │                            │  REJECTED ✗  │
    │ Status:CONFIRMED│                            │ Status:FAILED │
    │                 │                            │               │
    │ ✓ Order Valid   │                            │ ✗ Payment     │
    │ ✓ Can Proceed   │                            │   Invalid     │
    │ ✓ Auto-notify   │                            │ ✓ Notify      │
    │   Customer      │                            │   Customer    │
    └────────┬────────┘                            └───────────────┘
             │
             └──────────────────┬──────────────────┐
                                │                  │
                    ┌───────────▼──────────┐       │
                    │   KITCHEN OPERATION  │       │
                    └───────────┬──────────┘       │
                                │                  │
    ┌───────────────────────────▼──────────────────▼─────────────┐
    │         KITCHEN DISPLAY SYSTEM (KDS)                       │
    │      (/kitchen_display_system.php - Auto-refresh 5s)      │
    │                                                          │
    │  ┌──────────────────────────────────────────────────┐  │
    │  │ ORDER QUEUE - PENDING & COOKING ORDERS          │  │
    │  │ Stats: Total: 5 | Pending: 3 | Cooking: 2      │  │
    │  │                                                │  │
    │  │ 🔵 Order #PC123 (PENDING - Pulsing)           │  │
    │  │    Table: 5                                    │  │
    │  │    Wait Time: 08:45 ⏱️                         │  │
    │  │    Items: Biryani(2), Chai(1)                 │  │
    │  │    Notes: Extra spicy, no onion               │  │
    │  │                                                │  │
    │  │ 🔵 Order #PC124 (PENDING - Pulsing)           │  │
    │  │    Table: 7                                    │  │
    │  │    Wait Time: 06:30                            │  │
    │  │    Items: Dosa(3), Sambar(3)                  │  │
    │  │    Notes: —                                    │  │
    │  │                                                │  │
    │  │ 🟠 Order #PC125 (COOKING)                      │  │
    │  │    Table: 3                                    │  │
    │  │    Wait Time: 14:15                            │  │
    │  │    Items: Paratha(2), Raita(1)               │  │
    │  │    Notes: Ghee paratha                        │  │
    │  │                                                │  │
    │  │ 🔴 Order #PC126 (COOKING - CRITICAL! >15min) │  │
    │  │    Table: 2                                    │  │
    │  │    Wait Time: 16:20 ⚠️ URGENT                │  │
    │  │    Items: Paneer(2), Rice(1)                 │  │
    │  │    Notes: —                                    │  │
    │  │                                                │  │
    │  └──────────────────────────────────────────────────┘  │
    │                                                          │
    └───────────────┬──────────────────────────────────────────┘
                    │
        ┌───────────▼────────────┐
        │  KITCHEN STAFF MARKS   │
        │  "READY FOR DELIVERY"  │
        │  (Via Admin System)    │
        └───────────┬────────────┘
                    │
    ┌───────────────▼───────────────────────────────────────────┐
    │            PARALLEL PROCESSES                             │
    │                                                            │
    │  ┌──────────────────────┐    ┌──────────────────────┐     │
    │  │  ORDER HISTORY       │    │  INVENTORY SYSTEM    │     │
    │  │  System              │    │                      │     │
    │  └──────────────────────┘    └──────────────────────┘     │
    │                                                            │
    │  Customer Views Recent     Admin Tracks Stock:            │
    │  Orders (2-hour window)    • Initial: 100 units          │
    │  (/order_history.php)      • After Order: 98 units       │
    │                            • Status: Sufficient (Green)    │
    │  Auto-Expires After        • Can Restock if Needed       │
    │  2 Hours (Privacy)         (/admin/inventory_tracking    │
    │                             .php)                         │
    └───────────────────────────────────────────────────────────┘
```

---

## 🔄 Payment Approval Workflow (Detailed)

```
                          PAYMENT DECISION POINT
                         
                     ┌─────────────────────────────┐
                     │  Order Placed (Total: ₹500) │
                     └──────────────┬──────────────┘
                                    │
                  ┌─────────────────┼─────────────────┐
                  │                 │                 │
            ┌─────▼──────┐    ┌─────▼────┐    ┌─────▼──────┐
            │   ONLINE   │    │   CASH   │    │    QR      │
            │  PAYMENT   │    │ PAYMENT  │    │   CODE     │
            └─────┬──────┘    └─────┬────┘    └────────────┘
                  │                 │
          ┌───────▼─────────────────▼────────┐
          │  ORDER CREATED IN SYSTEM         │
          │  Order Status: PENDING           │
          │  Payment Status: PENDING         │
          └───────┬──────────────────────────┘
                  │
            ┌─────▼─────────────┐
            │  Razorpay Gateway │
            │  (Payment Success)│ ← OR → (Customer pays via Cash)
            └─────┬─────────────┘
                  │
        ┌─────────▼─────────────────────────────────┐
        │  Payment Status Updated to: PAID          │
        │  (Still UNCONFIRMED - needs verification) │
        └─────────┬────────────────────────────────┘
                  │
        ┌─────────▼────────────────────────────────────────────┐
        │  MANAGER VIEWS PAYMENT APPROVAL PAGE                │
        │  (/staff/payment_approval.php)                      │
        │                                                    │
        │  Sees:                                             │
        │  • Order #123: ₹500                              │
        │  • Payment: PAID (Online via Razorpay)           │
        │  • OR Payment: PENDING (Waiting for Cash)         │
        │  • QR Code for reference                         │
        │                                                    │
        │  Decision:                                        │
        │  [✓ APPROVE] [✗ REJECT] [💵 CASH RECEIVED]      │
        └──────────┬─────────────────────────┬──────┬───────┘
                   │                         │      │
    ┌──────────────▼──────┐    ┌────────────▼──┐ ┌▼──────────┐
    │  ✓ APPROVE PAYMENT  │    │  ✗ REJECT     │ │ 💵 CASH   │
    │                     │    │  PAYMENT      │ │ RECEIVED  │
    │  Action:            │    │               │ │           │
    │  • Verify payment   │    │ Action:       │ │ Action:   │
    │    details          │    │ • Mark fake   │ │ • Payment │
    │  • Click Approve    │    │ • Notify      │ │   Status: │
    │    button           │    │ customer:     │ │ CONFIRMED │
    │  • Payment Status:  │    │ "Invalid"     │ │ • Amount  │
    │    CONFIRMED        │    │ • Payment St: │ │   received│
    │                     │    │ FAILED        │ │ • Notify  │
    │  ✓ Order proceeds   │    │               │ │ Customer  │
    │    to kitchen       │    │ ✗ Order       │ │           │
    │  ✓ Amount: ₹500     │    │   cancelled   │ │ ✓ Order   │
    │  ✓ Money received   │    │   (refund if  │ │ proceeds  │
    │                     │    │   needed)     │ │           │
    └─────────┬───────────┘    └────────┬──────┘ └─────┬─────┘
              │                         │              │
              └────────────┬────────────┴──────────────┘
                           │
              ┌────────────▼────────────┐
              │ Status: CONFIRMED       │
              │ (Order can proceed)     │
              │                         │
              │ ✓ Kitchen starts work   │
              │ ✓ Order queue updates   │
              │ ✓ Customer notified     │
              │ ✓ Waiter delivers meal  │
              │ ✓ Order marked SERVED   │
              │                         │
              └────────────┬────────────┘
                           │
              ┌────────────▼────────────┐
              │ ORDER HISTORY CREATED   │
              │ (For 2 hours only)      │
              │                         │
              │ Customer can view:      │
              │ /order_history.php      │
              │                         │
              │ Shows item list &       │
              │ total for audit/proof   │
              │                         │
              │ Auto-expires in 2h      │
              └─────────────────────────┘
```

---

## 📦 Inventory Management Flow

```
                    ┌────────────────────────────┐
                    │   ADMIN INVENTORY PAGE     │
                    │                            │
                    │  View: All Menu Items      │
                    │  Filter: Stock Status      │
                    │  Search: By name/category  │
                    └──────────┬─────────────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
         ┌────▼────────┐ ┌────▼───────┐ ┌────▼───────────┐
         │   ITEM A    │ │   ITEM B   │ │    ITEM C      │
         │             │ │            │ │                │
         │ Biryani     │ │ Dosa       │ │ Chai          │
         │             │ │            │ │                │
         │ Stock: 45   │ │ Stock: 8   │ │ Stock: 0      │
         │ Threshold:30│ │ Thresh:15  │ │ Threshold: 5  │
         │             │ │            │ │                │
         │ Status: 🟢  │ │ Status: 🟡 │ │ Status: 🔴    │
         │ Sufficient  │ │ LOW STOCK  │ │ OUT OF STOCK  │
         │             │ │            │ │                │
         │ Actions:    │ │ Actions:   │ │ Actions:      │
         │ [Update]    │ │ [Update]   │ │ [Update]      │
         │ [Restock]   │ │ [Restock]  │ │ [Restock]     │
         │ [Threshold] │ │ [Threshold]│ │ [Threshold]   │
         └──────┬──────┘ └────┬───────┘ └───────┬────────┘
                │             │                 │
         ┌──────▼──────┐ ┌────▼──────┐ ┌───────▼────────┐
         │   ADMIN     │ │   ADMIN   │ │    ADMIN       │
         │   UPDATES   │ │  RESTOCKS │ │  SETS NEW      │
         │   STOCK     │ │   ITEM    │ │  THRESHOLD     │
         │             │ │           │ │                │
         │ Modal Form: │ │ Modal:    │ │ Modal:         │
         │ Enter:45→50 │ │ +10 units │ │ Threshold:5→20 │
         │             │ │ (now: 18) │ │                │
         │ Submit      │ │ Submit    │ │ Submit         │
         └──────┬──────┘ └────┬──────┘ └───────┬────────┘
                │             │                 │
         ┌──────▼──────────────▼─────────────────▼──────────┐
         │   DATABASE UPDATE                                │
         │                                                  │
         │   menu_items table:                              │
         │   • stock_quantity = 50                          │
         │   • last_restocked = NOW()                       │
         │   • low_stock_threshold = 20                     │
         │                                                  │
         │   inventory_logs table (auto-created):           │
         │   • item_id: 1                                   │
         │   • action: 'stock_update'                       │
         │   • quantity: +5                                 │
         │   • notes: 'Manual update'                       │
         │   • created_by: admin_id                         │
         │   • created_at: NOW()                            │
         └──────┬──────────────────────────────────────────┘
                │
         ┌──────▼──────────────────────────┐
         │   REAL-TIME EFFECTS             │
         │                                  │
         │   1. Status Badge Updates:      │
         │      🟢 Item back to Sufficient  │
         │                                  │
         │   2. Menu Item Available:        │
         │      ✓ Item shows in menu        │
         │      ✓ Customers can order       │
         │                                  │
         │   3. Alerts Clear:               │
         │      ✓ Low stock warning gone    │
         │      ✓ Out of stock flag gone    │
         │                                  │
         │   4. Statistics Updated:         │
         │      ✓ Low stock count: 1→0      │
         │      ✓ Sufficient count: 1→2     │
         │      ✓ Out of stock: 1→0         │
         │                                  │
         │   5. History Created:            │
         │      ✓ inventory_logs entry      │
         │      ✓ Audit trail available     │
         │                                  │
         └──────────────────────────────────┘
```

---

## 👥 User Role Access Matrix

```
┌──────────────────────────────────────────────────────────┐
│              FEATURE ACCESS BY USER ROLE                 │
├──────────────────────────────────────────────────────────┤
│                                                          │
│                  CUSTOMER  STAFF  MANAGER  ADMIN        │
│                  -------- ------ -------- -----        │
│                                                         │
│  Menu & Orders     ✅      ✅       ✅       ✅          │
│  My Orders         ✅      ❌       ❌       ❌          │
│  Order History     ✅      ❌       ❌       ❌          │
│  (2-hour window)                                       │
│                                                         │
│  Payment Approval  ❌      ❌       ✅       ✅          │
│  (approve/reject)                                      │
│                                                         │
│  Kitchen Display   ❌      ✅       ✅       ✅          │
│  (public access)                                       │
│                                                         │
│  Inventory Track   ❌      ❌       ✅       ✅          │
│  (admin only)                                          │
│                                                         │
│  Staff Manage      ❌      ❌       ❌       ✅          │
│  (admin only)                                          │
│                                                         │
│  Reports           ❌      ❌       ✅       ✅          │
│  (earnings, etc)                                       │
│                                                         │
└──────────────────────────────────────────────────────────┘
```

---

## 🔗 System Component Connections

```
┌─────────────────────────────────────────────────────────┐
│                    WEB SERVER                           │
│        (Apache/PHP on localhost:80)                     │
├─────────────────────────────────────────────────────────┤
│                                                        │
│  ┌────────────────────────────────────────────────┐  │
│  │         Frontend Pages (.php)                  │  │
│  │                                                │  │
│  │  ├─ index.php (Menu & Ordering)               │  │
│  │  ├─ payment_approval.php (Manager)            │  │
│  │  ├─ kitchen_display_system.php (Public)       │  │
│  │  ├─ order_history.php (Customer)              │  │
│  │  ├─ inventory_tracking.php (Admin)            │  │
│  │  │                                             │  │
│  │  └─ dashboards/                               │  │
│  │     ├─ manager_dashboard.php (Manager)        │  │
│  │     ├─ admin_dashboard.php (Admin)            │  │
│  │     └─ staff_dashboard.php (Kitchen Staff)    │  │
│  │                                                │  │
│  └────────────────┬─────────────────────────────┘  │
│                   │                                │
│                   │ Database Queries              │
│                   │ (SELECT, INSERT, UPDATE)      │
│                   │                                │
│  ┌────────────────▼─────────────────────────────┐  │
│  │      Database Connection                     │  │
│  │  (includes/db_connect.php)                   │  │
│  │                                              │  │
│  │  • Host: localhost                          │  │
│  │  • User: root                               │  │
│  │  • DB: cafe_project                         │  │
│  │  • Driver: MySQL/MariaDB                    │  │
│  │                                              │  │
│  └────────────────┬─────────────────────────────┘  │
│                   │                                │
└───────────────────┼────────────────────────────────┘
                    │
┌───────────────────▼────────────────────────────────┐
│          DATABASE (MySQL/MariaDB)                  │
│                                                   │
│  ┌──────────────────────────────────────────────┐ │
│  │        DATABASE: cafe_project                │ │
│  │                                              │ │
│  │  Core Tables:                               │ │
│  │  • customers                                │ │
│  │  • menu_items (+ stock fields)              │ │
│  │  • orders (+ approval fields)               │ │
│  │  • order_items                              │ │
│  │  • staff (+ attendance)                     │ │
│  │                                              │ │
│  │  Phase 5 New Tables:                        │ │
│  │  • inventory_logs (new)                     │ │
│  │  • payment_approvals (new)                  │ │
│  │                                              │ │
│  │  Views (Optimized Queries):                 │ │
│  │  • kds_pending_orders (new)                 │ │
│  │                                              │ │
│  │  Indexes (Performance):                     │ │
│  │  • idx_stock_quantity                       │ │
│  │  • idx_payment_status                       │ │
│  │  • idx_order_status                         │ │
│  │  • idx_inventory_logs_item_id               │ │
│  │  • idx_payment_approvals_order_id           │ │
│  │  • idx_approval_timestamp                   │ │
│  │                                              │ │
│  │  Stored Procedures:                         │ │
│  │  • check_low_stock()                        │ │
│  │  • log_inventory()                          │ │
│  │  • update_item_availability()               │ │
│  │                                              │ │
│  └──────────────────────────────────────────────┘ │
│                                                   │
└───────────────────────────────────────────────────┘
```

---

## 📡 External Integrations

```
┌─────────────────────────────────────────────────┐
│         EXTERNAL PAYMENT GATEWAY                │
│                                                 │
│  Razorpay API                                   │
│  • Payment Processing                           │
│  • QR Code Generation                           │
│  • Callback Webhooks                            │
│                                                 │
│  ↕️ (Communication)                              │
│                                                 │
│  Our System:                                    │
│  • Store payment status in DB                   │
│  • Display QR for manual verification           │
│  • Require manager approval before confirming   │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## ⏱️ Auto-Refresh Timings

```
Component              Update Interval    Purpose
──────────────────────────────────────────────────────
Payment Approval       10 seconds         Check new payments
Kitchen Display        5 seconds          Real-time order queue
Order History          On demand          Show recent orders
Inventory Dashboard    Manual (on action) Stock updates
Manager Dashboard      Manual refresh     Statistics
KDS Statistics         5 seconds          Live counts
Wait Time Counter      1 second           Persistent display
```

---

## 🎯 Data Flow Summary

```
Customer Action
    ↓
Frontend Form Submit
    ↓
Backend PHP Processing
    ↓
Database Query Execution
    ↓
Result Processing/Storage
    ↓
Auto-Refresh Trigger
    ↓
Updated UI Display
    ↓
Stakeholder Sees Change
    ↓
Next Action (Approve, Update, Serve, etc.)
```

---

**Last Updated:** February 22, 2026  
**System Version:** Phase 5 Complete  
**Diagrams Version:** 1.0 - Production
