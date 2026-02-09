# 📁 Project Structure

This document explains the organization of the QR Code Based Cafe Project.

## 🗂️ Directory Structure

```
QR_Code_Based_Cafe_Project/
│
├── 📄 index.php                    # Landing/Home page
├── 📄 admin_login.php              # Admin login (redirects to admin/)
├── 📄 setup.sql                    # Database setup script
├── 📄 README.md                    # Main project documentation
│
├── 📁 admin/                       # Admin panel files
│   ├── admin_login.php             # Admin login page
│   ├── admin_dashboard.php         # Admin dashboard
│   ├── generate_qr.php             # QR code generator
│   ├── manage_menu.php             # Menu management
│   ├── edit_item.php               # Edit menu item
│   ├── view_orders.php             # View all orders
│   └── mark_absent.php             # Mark absent staff
│
├── 📁 staff/                       # Staff panel files
│   ├── staff_login.php             # Staff login page
│   └── staff_dashboard.php         # Kitchen display system
│
├── 📁 includes/                    # Shared/Common files
│   └── db_connect.php              # Database connection configuration
│
├── 📁 docs/                        # Documentation files
│   ├── README.md                   # Project documentation (copy)
│   ├── MOBILE_QR_GUIDE.md          # Mobile QR code setup guide
│   ├── TESTING_GUIDE.md            # Testing instructions
│   └── PROJECT_STRUCTURE.md        # This file
│
├── 📁 images/                      # Images and assets
│   └── menu/                       # Menu item images
│
├── 👥 Customer Files (Root Level)
│   ├── menu.php                    # Customer menu page
│   ├── cart.php                    # Shopping cart
│   ├── place_order.php             # Place order handler
│   ├── add_to_cart.php             # Add to cart handler
│   ├── clear_cart.php              # Clear cart handler
│   ├── track_order.php             # Order tracking page
│   ├── call_waiter.php             # Call waiter feature
│   └── simulate_payment.php        # Payment simulation
│
└── 🧪 Testing & Utilities
    ├── mobile_test.php             # Mobile connection test page
    └── logout.php                  # Logout handler
```

## 📂 Folder Descriptions

### `/database/`
Contains all database-related files:
- **setup.sql**: Complete database setup script with tables and sample data

### `/includes/`
Contains shared PHP files used across multiple pages:
- **db_connect.php**: Database connection configuration (MySQL credentials)

### `/docs/`
All project documentation:
- **README.md**: Complete project documentation
- **MOBILE_QR_GUIDE.md**: Instructions for mobile QR code setup
- **TESTING_GUIDE.md**: Testing procedures and checklists

### `/admin/`
Contains all admin panel files with proper authentication and management features.

### `/staff/`
Contains all staff panel files for kitchen order management.

### `/images/`
Stores all image assets:
- **menu/**: Menu item images (named by item_id)
- **placeholder.png**: Default image for items without photos

## 🎯 File Categories

### 🔐 Admin Access URLs
- `http://localhost/QR_Code_Based_Cafe_Project/admin/admin_login.php`
- `http://localhost/QR_Code_Based_Cafe_Project/admin/admin_dashboard.php`
- `http://localhost/QR_Code_Based_Cafe_Project/admin/generate_qr.php`
- `http://localhost/QR_Code_Based_Cafe_Project/admin/manage_menu.php`
- `http://localhost/QR_Code_Based_Cafe_Project/admin/view_orders.php`

### 👨‍🍳 Staff Access URLs
- `http://localhost/QR_Code_Based_Cafe_Project/staff/staff_login.php`
- `http://localhost/QR_Code_Based_Cafe_Project/staff/staff_dashboard.php`

### 👥 Customer Access URLs
- `http://localhost/QR_Code_Based_Cafe_Project/` (Home)
- `http://localhost/QR_Code_Based_Cafe_Project/menu.php?table_id=1`
- `http://localhost/QR_Code_Based_Cafe_Project/cart.php`
- `http://localhost/QR_Code_Based_Cafe_Project/track_order.php`

## 🔄 Include Path Updates

All PHP files now use the updated include path:
```php
include 'includes/db_connect.php';
```

## 📝 Key Files Explained

### Core Configuration
- **includes/db_connect.php**: Database connection settings

### Customer Flow
1. **index.php**: Landing page
2. **menu.php**: Browse menu items
3. **add_to_cart.php**: Add items to session cart
4. **cart.php**: View cart and place order
5. **place_order.php**: Process order submission
6. **simulate_payment.php**: Demo payment processing
7. **track_order.php**: Track order status

### Kitchen Flow
1. **staff_login.php**: Staff authentication
2. **staff_dashboard.php**: View and manage orders

### Admin Flow
1. **admin_login.php**: Admin authentication
2. **admin_dashboard.php**: Analytics and management
3. **manage_menu.php**: CRUD operations for menu
4. **view_orders.php**: Order history and filtering
5. **generate_qr.php**: Create printable QR codes

## 🎨 Future Improvements

### Suggested Refactoring (Optional)
Move files into logical folders:

```
admin/
  ├── login.php
  ├── dashboard.php
  ├── generate_qr.php
  ├── manage_menu.php
  └── view_orders.php

staff/
  ├── login.php
  └── dashboard.php

customer/
  ├── menu.php
  ├── cart.php
  ├── track_order.php
  └── handlers/
      ├── add_to_cart.php
      ├── place_order.php
      └── clear_cart.php

includes/
  ├── db_connect.php
  ├── config.php
  └── functions.php

assets/
  ├── css/
  ├── js/
  └── images/
```

**Note**: Current structure keeps all main files in root for simplicity and ease of access during development/presentation.

## 🛠️ Maintenance

### Adding New Features
1. Create new PHP file in root or appropriate folder
2. Include database connection: `include 'includes/db_connect.php';`
3. Follow existing naming conventions
4. Update this document

### Database Changes
1. Update `setup.sql` with new schema
2. Document changes in README.md
3. Update relevant PHP files

### Documentation Updates
1. Edit files in `/docs/` folder
2. Keep README.md in root up to date
3. Update testing guides as needed

## 🎓 For Project Presentation

The current structure is ideal for:
- ✅ Easy navigation during demo
- ✅ Clear file purposes
- ✅ Simple URL structure
- ✅ Quick access to all features
- ✅ Easy troubleshooting

---

**Last Updated**: February 9, 2026  
**Project Type**: Graduation Project  
**Structure Version**: 1.0
