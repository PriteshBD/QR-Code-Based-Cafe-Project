# P&S Cafe - File Structure & Organization

## 📂 Project Structure

```
QR_Code_Based_Cafe_Project/
│
├── 📄 index.php                          # Main entry point / Landing page
├── 📄 README.md                          # Main documentation
│
├── 📁 ROOT FILES (Customer-Facing Pages)
│   ├── add_to_cart.php                   # Add items to cart
│   ├── bill.php                          # Generate bill
│   ├── call_waiter.php                   # Call waiter service
│   ├── cart.php                          # View shopping cart
│   ├── clear_cart.php                    # Clear cart items
│   ├── menu.php                          # Browse menu
│   ├── order_history.php                 # View past orders
│   ├── place_order.php                   # Confirm and place order
│   ├── payment.php                       # Payment processing (CASH only)
│   ├── verify_payment.php                # Payment verification
│   ├── track_order.php                   # Track order status
│   ├── kitchen_display_system.php        # KDS display for kitchen
│   └── logout.php                        # User logout
│
├── 📁 admin/                             # Admin Panel & Management
│   ├── admin_login.php                   # Admin login
│   ├── admin_dashboard.php               # Admin dashboard
│   ├── manage_menu.php                   # Add/Edit/Delete menu items
│   ├── edit_item.php                     # Edit specific menu item
│   ├── generate_qr.php                   # Generate QR codes for tables
│   ├── view_orders.php                   # View all orders
│   ├── staff_management.php              # Manage staff members
│   └── mark_absent.php                   # Mark staff attendance
│
├── 📁 staff/                             # Staff Dashboards & Operations
│   ├── staff_login.php                   # Staff login
│   ├── staff_dashboard.php               # Main staff dashboard
│   ├── staff_profile.php                 # Staff profile settings
│   ├── barista_dashboard.php             # Barista order console
│   ├── chef_dashboard.php                # Chef order console
│   ├── waiter_dashboard.php              # Waiter task management
│   ├── manager_dashboard.php             # Manager overview
│   ├── payment_approval.php              # Approve payments
│   └── service_requests.php              # Handle service requests
│
├── 📁 api/                               # Backend API Endpoints
│   └── get_notifications.php             # Real-time notifications
│
├── 📁 utils/                             # Setup & Utility Scripts
│   ├── setup_images.php                  # Initialize image directory
│   ├── generate_placeholder_images.php   # Create placeholder images
│   ├── generate_missing_images.php       # Generate missing product images
│   ├── simulate_payment.php              # Test payment system
│   └── mobile_test.php                   # Mobile responsiveness tester
│
├── 📁 database/                          # Database & Schema
│   └── database_complete.sql             # Complete database setup (ALL phases)
│
├── 📁 docs/                              # Documentation
│   ├── README.md                         # Documentation index
│   ├── QUICK_START.md                    # Quick start guide
│   ├── PROJECT_STRUCTURE.md              # Project architecture
│   ├── TESTING_GUIDE.md                  # Testing procedures
│   ├── DEMO_PAYMENT_GUIDE.md             # Payment system demo
│   ├── MOBILE_QR_GUIDE.md                # QR code usage guide
│   ├── FEATURES_GUIDE.md                 # Feature overview
│   ├── IMAGE_SETUP_GUIDE.md              # Image configuration
│   ├── IMPLEMENTATION_SUMMARY.md         # Implementation notes
│   ├── STAFF_IMPROVEMENTS_GUIDE.md       # Staff feature guide
│   ├── PHASE4_CHANGELOG.md               # Phase 4 changes
│   ├── PHASE5_ARCHITECTURE.md            # Phase 5 architecture
│   ├── PHASE5_SETUP_GUIDE.md             # Phase 5 setup
│   ├── PHASE5_QUICK_REFERENCE.md         # Phase 5 quick reference
│   ├── PHASE5_SUMMARY.md                 # Phase 5 summary
│   ├── PHASE5_COMPLETION_REPORT.md       # Phase 5 completion report
│   ├── PHASE5_READY.md                   # Phase 5 ready checklist
│   └── WHATS_NEW_PHASE5.md               # Phase 5 new features
│
├── 📁 includes/                          # PHP Includes & Shared Code
│   └── db_connect.php                    # Database connection
│
├── 📁 images/                            # Product Images
│   └── menu/                             # Menu item images
│
├── 📁 js/                                # JavaScript Files
│   └── notifications.js                  # Real-time notifications script
│
├── 📁 .git/                              # Git version control
└── 📁 .gitignore                         # Git ignore rules
```

---

## 🎯 Folder Organization Guide

### **ROOT (Main Application)**
- Entry point: `index.php`
- Core customer-facing pages (order, cart, payment, tracking)
- Direct access from browser

### **admin/**
- Admin login and authentication
- Admin dashboard and controls
- Menu management (CRUD operations)
- QR code generation
- Staff and inventory management

### **staff/**
- Staff login and dashboards
- Role-based dashboards (chef, barista, waiter, manager)
- Order handling and task management
- Payment approval workflow
- Service request handling

### **api/**
- AJAX endpoints for real-time updates
- Notification delivery
- Data synchronization

### **utils/**
- One-time setup scripts
- Image generation utilities
- Testing and development tools
- **Note:** Run these once during setup, not in production

### **database/**
- `database_complete.sql` - Single consolidated database file
- Contains all tables, views, procedures, and sample data
- All phases integrated into one file

### **docs/**
- Complete documentation
- Setup guides and tutorials
- Architecture and design documents
- Feature guides for admin and staff
- Phase migration documentation

### **includes/**
- Shared PHP code
- Database connection configuration
- Reusable functions

### **images/**
- Product/menu images
- Generated QR codes
- System images

### **js/**
- Client-side JavaScript
- Notification handlers
- Real-time update scripts

---

## 🚀 Setup Instructions

### Initial Setup
1. **Database Setup**
   ```bash
   # Import database_complete.sql into phpMyAdmin
   # This creates all tables, views, and sample data
   ```

2. **Image Setup** (Optional)
   ```bash
   # Run once: utils/setup_images.php
   # Run once: utils/generate_placeholder_images.php
   ```

3. **Access Application**
   - Customer: `http://localhost/QR_Code_Based_Cafe_Project/`
   - Admin: `http://localhost/QR_Code_Based_Cafe_Project/admin/admin_login.php`
   - Staff: `http://localhost/QR_Code_Based_Cafe_Project/staff/staff_login.php`

---

## 📋 File Dependencies

### Customer Pages Include:
- `includes/db_connect.php` - Database connection
- `js/notifications.js` - Real-time updates

### Admin Pages Include:
- `includes/db_connect.php` - Database connection
- Database queries for management functions

### Staff Pages Include:
- `includes/db_connect.php` - Database connection
- `api/get_notifications.php` - Real-time notifications
- `js/notifications.js` - Notification display

---

## 🔐 Login Credentials (Default)

### Admin Access
- **URL:** `admin/admin_login.php`
- **Username:** admin
- **Password:** admin123

### Staff Access
- **URL:** `staff/staff_login.php`
- **Roles Available:**
  - Chef
  - Barista
  - Waiter
  - Manager
  - Payment Staff

---

## 💡 Development Notes

- All PHP files use consistent database connection via `includes/db_connect.php`
- Payment system is CASH-only (no external gateways)
- Images are stored in `images/menu/` directory
- Real-time notifications use long-polling from `api/get_notifications.php`
- Kitchen Display System available at `kitchen_display_system.php`

---

## ✅ Verification Checklist

- [x] Database imported with `database_complete.sql`
- [x] Admin login working at `admin/admin_login.php`
- [x] Staff login working at `staff/staff_login.php`
- [x] Customer pages accessible from root
- [x] Images loaded properly from `images/menu/`
- [x] Notifications working in real-time
- [x] Payment processing with cash method
- [x] Order tracking functional
- [x] KDS displaying properly
- [x] All utility scripts tested

---

## 📞 Support & Troubleshooting

If files are not found:
1. Check that all files are in correct directories as shown above
2. Verify paths in PHP includes match current file locations
3. Clear browser cache (Ctrl+F5)
4. Check XAMPP Apache logs for any errors

---

**Last Updated:** February 22, 2026  
**Version:** 5.0 (Final Consolidated)
