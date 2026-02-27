# QR Code Based Cafe Project - Black Book

Date: February 27, 2026

## 1. Project Overview
The QR Code Based Cafe Project is a web-based digital ordering system for cafes and restaurants. Customers scan a QR code at their table, browse the menu, place orders, and track order status. Admin and staff dashboards manage menu items, orders, attendance, and service requests. The system is built with PHP and MySQL and runs on a local XAMPP server.

## 2. Objectives
- Reduce manual order taking and improve order accuracy.
- Provide a fast, mobile-friendly ordering experience.
- Enable role-based staff workflows (chef, barista, waiter, manager, payment).
- Support real-time order tracking and notifications.
- Provide admin analytics, menu control, and staff management.

## 3. Scope
This project covers:
- Customer ordering from a QR code at a table.
- Order lifecycle management from placement to delivery.
- Staff dashboards for kitchen and service roles.
- Admin features for menu and staff management.
- Cash-only payment logging (demo-ready).

## 4. Technology Stack
- Backend: PHP 7+
- Database: MySQL
- Frontend: HTML5, CSS3, JavaScript
- Server: XAMPP (Apache + MySQL)
- Charts: Chart.js
- QR Codes: QR Server API

## 5. System Modules
### 5.1 Customer Module
- Menu browsing (menu.php)
- Cart management (cart.php, add_to_cart.php, clear_cart.php)
- Order placement (place_order.php)
- Payment (payment.php, verify_payment.php)
- Order tracking (track_order.php)
- Bill/receipt (bill.php)
- Call waiter (call_waiter.php)

### 5.2 Kitchen and Staff Module
- Kitchen display system (kitchen_display_system.php)
- Role dashboards: chef, barista, waiter, manager
- Payment approval (payment_approval.php)
- Service requests (service_requests.php)
- Attendance auto-marking on login

### 5.3 Admin Module
- Admin login and dashboard
- Menu management (manage_menu.php, edit_item.php)
- QR code generation (generate_qr.php)
- Order management (view_orders.php)
- Staff management and attendance (staff_management.php, mark_absent.php)

### 5.4 Notifications Module
- Real-time notifications via API endpoint (api/get_notifications.php)
- Client script (js/notifications.js)

## 6. Architecture Overview
High-level flow:
1. Customer scans QR code and opens menu.
2. Customer adds items to cart and places order.
3. Order is stored in MySQL and assigned to staff role.
4. Kitchen and staff dashboards fetch live orders.
5. Staff updates order status; customer tracking updates.
6. Payment logs are stored as cash payments.

Client-Server architecture:
- Browser (customer/staff/admin)
- Apache + PHP application
- MySQL database

## 7. Database Design
Database file: database/database_complete.sql

Core tables:
- admin_users
- menu_items
- orders
- order_items
- staff
- attendance
- service_requests
- payment_logs
- notification_logs
- notification_preferences
- inventory_logs
- payment_approvals
- call_log

View:
- kds_pending_orders (for kitchen display)

Stored procedures:
- check_low_stock()
- log_inventory(...)
- update_availability()
- daily_revenue(p_date)

## 8. Key Features
- QR-based table ordering.
- Mobile-friendly UI.
- Order tracking with status updates.
- Kitchen display system (KDS).
- Role-based staff dashboards.
- Real-time notifications for staff.
- Cash-only payment logging.
- Admin analytics and menu management.
- Attendance tracking and service requests.

## 9. User Workflow
### Admin
1. Login to admin panel.
2. Manage menu items and availability.
3. Generate and print QR codes for tables.
4. Monitor orders and staff attendance.

### Customer
1. Scan QR code at table.
2. Browse menu and add items to cart.
3. Place order and complete cash payment.
4. Track order status and view bill.

### Staff
1. Login to staff dashboard.
2. View assigned orders and update status.
3. Notify readiness and delivery.
4. Handle service requests if assigned.

## 10. Setup and Installation
1. Place project in:
   C:\xampp\htdocs\QR_Code_Based_Cafe_Project
2. Start Apache and MySQL in XAMPP.
3. Import database:
   database/database_complete.sql
4. Optional: Run image utilities in utils/.
5. Open in browser:
   http://localhost/QR_Code_Based_Cafe_Project/

Default credentials:
- Admin: admin / admin123
- Staff (examples): Ahmed / 123456789

## 11. Testing Summary
Suggested tests:
- Menu load and item availability checks.
- Cart add/remove and total calculation.
- Order placement and tracking updates.
- Staff dashboards receive new orders.
- Notifications are delivered to staff.
- Payment log entry created for orders.
- Attendance marking on staff login.

## 12. Security Notes
- Uses server-side PHP sessions.
- Database access centralized in includes/db_connect.php.
- For production, add prepared statements and password hashing.

## 13. Limitations
- Payments are cash-only (no external gateway enabled).
- Demo environment intended for academic use.

## 14. Future Enhancements
- Payment gateway integration.
- SMS/Email notifications.
- Inventory forecasting and alerts.
- Multi-branch support.
- Mobile app integration.

## 15. References
- README.md
- FILE_STRUCTURE.md
- REORGANIZATION_REPORT.md
- STYLING_IMPLEMENTATION_GUIDE.md
- GOLD_BLACK_THEME_SUMMARY.md
