# QR Code Based Cafe Project

A comprehensive QR code-based digital ordering system for cafes and restaurants, built with PHP and MySQL.

> 🎓 **Perfect for Degree Projects!** This project includes a **Demo Payment Mode** that works great on mobile phones - no real payment needed for demonstrations! [📖 See Demo Payment Guide](docs/DEMO_PAYMENT_GUIDE.md)

## � Welcome to Phase 4 - Final Release

### 🎉 NEW IN PHASE 4 - Production Ready Features!

✅ **Bill Generation & Receipt System** - Generate, print, and email professional bills  
✅ **Payment Gateway Integration** - Razorpay integration for secure online payments (INR)  
✅ **Real-time Notifications** - Sound + popup alerts for staff with customizable settings  
✅ **Advanced Security** - HMAC signature verification for payments  
✅ **Complete Documentation** - 4 comprehensive guides included

📚 **Quick Links for Phase 4:**
- [QUICK_START.md](QUICK_START.md) - Get started in 5 minutes
- [FEATURES_GUIDE.md](FEATURES_GUIDE.md) - Detailed feature documentation
- [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) - What was implemented

## 📋 Project Overview

This is a graduation-level project that implements a complete digital ordering system where customers scan a QR code on their table to view the menu, place orders, make payments via UPI (or demo mode), and track their order status in real-time.

## ✨ Features

### Customer Features
- 🎯 **QR Code Scanning**: Each table has a unique QR code for ordering (works on mobile!)
- 📱 **Mobile-Friendly Menu**: Fully responsive design for smartphones
- 🛒 **Shopping Cart**: Add/remove items, view cart total
- 💳 **Payment Options**: UPI + **Demo Mode for Presentations** (Cash/Card/UPI simulation)
- 📄 **Bill Generation**: View, print, and email itemized bills (NEW PHASE 4!)
- 💰 **Online Payments**: Razorpay integration for secure card/UPI/wallet payments (NEW PHASE 4!)
- 📊 **Order Tracking**: Real-time order status tracking with estimated time
- 🔔 **Call Waiter**: Request assistance from staff
- 🧾 **Digital Receipt**: Printable order receipt

### Kitchen Staff Features
- 👨‍🍳 **Modern Kitchen Display System**: Beautiful, card-based order interface
- 🔔 **Real-time Notifications**: Sound + popup alerts when orders arrive (NEW PHASE 4!)
- 📊 **Real-time Statistics**: View pending, cooking, and ready order counts
- ⏱️ **Order Management**: Update order status with estimated preparation time
- 📝 **Order Details**: View items with quantities and special instructions
- 🎨 **Color-coded Status**: Visual indicators for order urgency
- ⏰ **Time Tracking**: See how long orders have been waiting
- 🔄 **Auto Refresh**: Dashboard updates every 15 seconds
- ✅ **Auto Attendance**: Automatically marked present upon login

### Barista Features (NEW PHASE 4!)
- ☕ **Beverage Dashboard**: Specialized view for coffee/beverage preparation
- 🔔 **Smart Alerts**: Only gets notified for beverage orders
- 📋 **Drink Queue**: Clean interface for managing drink orders
- ✨ **Status Tracking**: Mark as Preparing/Ready

### Waiter & Delivery Features (NEW PHASE 4!)
- 📦 **Ready Orders Hub**: View all ready orders from kitchen
- 🚀 **Quick Delivery**: One-click to mark order as delivered
- 🔔 **Pickup Notifications**: Alerted when orders are ready
- 📍 **Table Tracking**: See which tables' orders are ready

### Manager Features (NEW PHASE 4!)
- 📞 **Service Request Management**: Handle customer service calls
- 📊 **Revenue Tracking**: Daily revenue dashboard
- 🎯 **KPI Monitoring**: Pending orders, order times, efficiency metrics
- 🔔 **Alert System**: Get notified of service requests immediately

### Admin Features
- 📊 **Analytics Dashboard**: Revenue tracking, top-selling items with charts
- 📋 **Menu Management**: Add, edit, delete, and toggle item availability
- 🎯 **QR Code Generator**: Generate printable QR codes for all tables
- 📦 **Orders Management**: View all orders with filtering by status and date
- 👥 **Staff Management**: Add staff, manage roles and salaries
- ✅ **Smart Attendance System**: Auto-mark present on login, bulk absent marking
- 🎨 **Modern UI**: Professional, responsive admin interface

## 🛠️ Technology Stack

- **Backend**: PHP 7+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Payment**: Razorpay API integration
- **Charts**: Chart.js
- **QR Codes**: QR Server API
- **Audio**: Web Audio API for notifications
- **Server**: XAMPP (Apache + MySQL)

## 📦 Installation

### Prerequisites
- XAMPP (with Apache and MySQL)
- Web browser (Chrome, Firefox, Edge, etc.)

### Setup Steps

1. **Clone or download** this project to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\QR_Code_Based_Cafe_Project
   ```

2. **Start XAMPP**:
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Create Database**:
   - Open browser and go to `http://localhost/phpmyadmin`
   - Click "Import" tab
   - Choose the `database/setup.sql` file from the project folder
   - Click "Go" to execute

4. **Access the Application**:
   - Open browser and go to: `http://localhost/QR_Code_Based_Cafe_Project/`

## 🔐 Default Credentials

### Admin Login
- **Username**: admin
- **Password**: admin123
- **Access**: `admin/admin_login.php`

### Staff Login (Kitchen)
- **Username**: Ahmed
- **Password**: 123456789 (phone number)
- **Access**: `staff/staff_login.php`

Other staff credentials:
- Fatima / 123456790
- Hassan / 123456791
- Zainab / 123456792

## 📂 Project Structure

```
QR_Code_Based_Cafe_Project/
├── index.php                 # Landing page
├── database/                 # Database files
│   └── setup.sql             # Database setup script
├── includes/                 # Shared files
│   └── db_connect.php        # Database connection
├── admin/                    # Admin panel files
├── staff/                    # Staff panel files
├── docs/                     # Documentation
│   ├── README.md
│   ├── MOBILE_QR_GUIDE.md
│   ├── TESTING_GUIDE.md
│   └── PROJECT_STRUCTURE.md
├── images/                   # Images folder
└── customer files/           # Menu, cart, order tracking, etc.
```

See [docs/PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md) for detailed structure information.

## 🗄️ Database Schema

### Tables
- **admin_users**: Admin credentials
- **menu_items**: Cafe menu with prices and availability
- **orders**: Customer orders with status and payment info
- **order_items**: Items in each order
- **staff**: Staff members with roles and salaries
- **attendance**: Staff attendance records
- **service_requests**: Waiter call requests

## 🎯 Usage Workflow

1. **Admin Setup**:
   - Login as admin
   - Add/manage menu items
   - Generate QR codes for tables
   - Print and place QR codes on tables

2. **Customer Ordering**:
   - Scan QR code on table
   - Browse menu and add items to cart
   - Add special instructions (optional)
   - Place order
   - **Pay via Demo Mode** (for presentations) or Real UPI
   - Track order status in real-time

### 💡 Demo Payment Mode (For Presentations)

Perfect for degree project demonstrations on mobile devices!

**How to use:**
1. Place an order from the menu
2. On the order tracking page, scroll to **"Demo Payment Options"**
3. Click one of:
   - 💵 **Pay with Cash** (Demo)
   - 💳 **Pay with Card** (Demo)  
   - 📱 **Pay with UPI** (Demo)
4. Order is automatically marked as PAID and sent to kitchen
5. Watch it appear on the staff kitchen display!

**Why this is useful:**
- No real payment needed during demonstrations
- Works perfectly on mobile phones
- Shows complete ordering workflow
- Impresses project evaluators! 🎓

**Note:** Real UPI payment is also available for actual deployment.

3. **Kitchen Processing**:
   - Staff login to kitchen display (auto-marked as Present)
   - View pending orders with real-time stats
   - Start cooking and set estimated time
   - Mark orders as ready when done
   - Mark as served when picked up

4. **Admin Monitoring**:
   - View analytics and revenue
   - Monitor all orders with filtering
   - Manage staff attendance
   - Mark remaining staff as absent (end of day)
   - Update menu availability

## 🎯 Smart Attendance System

### How It Works
1. **Automatic Present Marking**: When staff logs into the kitchen dashboard, they are automatically marked as "Present" for today
2. **Manual Override**: Admin can manually mark any staff as Present or Absent from the dashboard
3. **Bulk Absent Marking**: Admin can click "Mark Remaining as Absent" button to automatically mark all staff who haven't logged in as Absent
4. **Daily Tracking**: Each staff member's attendance is tracked per day with timestamps

### Admin Attendance Management
- View today's attendance status for all staff
- Manually mark individual staff present/absent
- Use bulk absent marking at end of day
- Color-coded status indicators (Green = Present, Red = Absent)

## 🚀 Key Features Implementation

### QR Code System
- Each table gets a unique URL: `menu.php?table_id=X`
- QR codes are generated dynamically using QR Server API
- Admin can print QR codes for any number of tables
- **Mobile Support**: Auto-detects IP address for mobile access
- **Two Modes**: Localhost (desktop only) or Network mode (mobile + desktop)
- **Easy Testing**: Test QR codes on your phone before printing
- See [MOBILE_QR_GUIDE.md](MOBILE_QR_GUIDE.md) for complete mobile setup instructions

### Smart Attendance System
- **Auto-Present**: Staff automatically marked present on login
- **Manual Override**: Admin can manually adjust attendance
- **Bulk Operations**: Mark all remaining staff absent with one click
- **Visual Indicators**: Color-coded status (Green/Red)
- **Daily Tracking**: Complete attendance history per day

### Modern Kitchen Display
- **Card-based Layout**: Clean, organized order cards
- **Color-coded Status**: Visual differentiation (Yellow/Blue/Green)
- **Real-time Stats**: Dashboard showing order counts by status
- **Time Tracking**: Shows how long each order has been waiting
- **Item Breakdown**: Clear display of items with quantities
- **Special Notes**: Highlighted customer instructions
- **Responsive Design**: Works on tablets and mobile devices
- **Auto-refresh**: Updates every 15 seconds

### Payment Integration
- UPI payment via QR code
- Uses standard UPI deep-link format
- Payment simulation available for demo
- Automatic status update after payment

### Real-time Tracking
- Order status updates instantly
- Estimated preparation time shown
- Auto-refresh on tracking page
- Kitchen display auto-refreshes every 15 seconds

### Analytics
- Revenue calculation from paid orders
- Top-selling items chart
- Staff attendance tracking
- Order filtering by status and date

## 🎨 Design Improvements

### Staff Dashboard Redesign
The staff/kitchen dashboard has been completely redesigned with:
- **Modern Gradient Background**: Professional blue gradient theme
- **Card-based Layout**: Each order in a clean, elevated card
- **Statistics Bar**: Quick view of pending, cooking, and ready orders
- **Color-coded Orders**: Visual status indication (Yellow=Pending, Blue=Cooking, Green=Ready)
- **Better Typography**: Clear hierarchy and readable fonts
- **Hover Effects**: Interactive feedback on cards and buttons
- **Responsive Grid**: Adapts to different screen sizes
- **Time Information**: Shows order age and staff details
- **Enhanced Forms**: Better input fields with icons and spacing
- **Empty State**: Friendly message when no orders exist

### Staff Login Improvements
- **Gradient Background**: Eye-catching purple gradient
- **Animated Icon**: Bouncing chef emoji
- **Better Form Design**: Clear labels and icon-enhanced inputs
- **Focus States**: Visual feedback on input focus
- **Info Box**: Clear indication of auto-attendance feature
- **Improved Error Display**: Better styled error messages

### Admin Dashboard Enhancements
- **Success Notifications**: Visual confirmation of actions
- **Info Boxes**: Helpful tips and information
- **Better Button Styling**: Clear call-to-action buttons
- **Organized Sections**: Clean separation of features

## 🔧 Customization

### Change UPI ID
Edit `track_order.php` line 23:
```php
$my_upi_id = "your-upi-id@bank";
```

### Change Number of Tables
Default is 20 tables. Modify in `generate_qr.php` or use URL parameter:
```
generate_qr.php?tables=50
```

### Change Base URL
Edit `generate_qr.php` line 10 if deploying online:
```php
$base_url = "https://yourdomain.com/menu.php?table_id=";
```

## 🐛 Troubleshooting

### Database Connection Error
- Check if MySQL is running in XAMPP
- Verify database name is `cafe_project` in `db_connect.php`
- Ensure `setup.sql` has been imported

### QR Codes Not Loading
- Check internet connection (uses online QR API)
- Alternative: Install a local QR library

### Orders Not Updating
- Clear browser cache
- Check if form submissions are working
- Verify database connection

## 📝 Future Enhancements

- Online payment gateway integration (Razorpay, Stripe)
- SMS/Email notifications
- Multiple restaurant support
- Mobile app (React Native/Flutter)
- Order history for customers
- Inventory management
- Report generation (PDF)
- Multi-language support

## 👨‍💻 Developer Notes

- All forms use POST method for security
- SQL injection prevention using real_escape_string
- Session management for cart and user authentication
- Responsive design for mobile devices
- Print-friendly layouts for receipts and QR codes

## 📄 License

This is a graduation project. Free to use for educational purposes.

## 👥 Credits

Developed as a final year graduation project.

## 📞 Support

For issues or questions about setup, check:
1. Database is properly imported
2. XAMPP services are running
3. File permissions are correct
4. PHP version is 7.0 or higher

---

**Note**: This is a demo/educational project. For production use, implement proper security measures including password hashing, prepared statements, HTTPS, and secure payment processing. 