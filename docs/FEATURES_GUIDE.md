# P&S Cafe System - Implementation Guide (Phase 4)

## Overview
This guide documents the three major features recently implemented:
1. **Bill Generation & Receipt System** - Digital bills with printing and email capability
2. **Payment Gateway Integration** - Razorpay integration for secure online payments
3. **Real-time Notification System** - Sound and popup alerts for staff dashboards

---

## Feature 1: Bill Generation & Receipt System

### Files Created/Modified
- **New:** `bill.php` - Main bill generation page
- **Modified:** `track_order.php` - Added "View Bill" button

### How It Works

#### Accessing the Bill
1. After order is placed, customer arrives at order tracking page
2. Customer clicks **"💵 View Bill"** button
3. Bill page (`bill.php`) displays itemized receipt with:
   - Order ID and table number
   - List of all items ordered with quantities
   - Subtotal, tax (5%), and total amount
   - Order status and payment method

#### Bill Features

**Display Mode:**
```
Order ID: #123
Table No: 5
Status: Ready
Payment: Card

Items Ordered:
- Cappuccino x2 @ ₹200 = ₹400
- Sandwich x1 @ ₹300 = ₹300

Subtotal: ₹700
Tax (5%): ₹35
TOTAL: ₹735
```

**Action Buttons:**
- **🖨️ Print** - Opens printer dialog to print the bill
- **📧 Email** - Sends bill to email address (requires SMTP configuration)
- **📥 Download** - Downloads bill as PDF (requires PDF library)

#### Email Integration
To enable email sending, update `bill.php` and configure PHP mail or SMTP:

```php
// Simple mail setup (uses server's mail config)
$to = $email;
$subject = "Your Bill - P&S Cafe - Order #" . $order_id;
$headers = "MIME-Version: 1.0\r\n" .
           "Content-type: text/html; charset=UTF-8\r\n";
mail($to, $subject, $bill_html, $headers);
```

**For production:** Use PHPMailer or SwiftMailer with SMTP credentials.

#### Styling & Print Optimization
- Print-friendly CSS with `@media print` rules
- Mobile-responsive design
- Professional cafe branding (☕ P&S CAFE header)
- Monospace font for authentic receipt look

---

## Feature 2: Payment Gateway Integration (Razorpay)

### Files Created/Modified
- **New:** `payment.php` - Payment processing page
- **New:** `verify_payment.php` - Payment verification API

### Setup Instructions

#### Step 1: Get Razorpay Keys
1. Sign up at [https://razorpay.com](https://razorpay.com)
2. Go to **Dashboard → Settings → API Keys**
3. Copy your **Key ID** and **Key Secret**

#### Step 2: Update Configuration
In `payment.php` (lines 20-22), replace test keys with your actual keys:

```php
$razorpay_key = 'your_key_id_here';        // Get from dashboard
$razorpay_secret = 'your_key_secret_here'; // KEEP PRIVATE!
```

In `verify_payment.php` (line 19), use the same secret:

```php
$razorpay_secret = 'your_key_secret_here';
```

#### Step 3: Update Database (One-time)
Create payment logs table:

```sql
CREATE TABLE IF NOT EXISTS payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2),
    status VARCHAR(50),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);
```

### How Payments Work

#### User Flow
1. Customer clicks **"Pay"** button (or manually goes to `/payment.php?order_id=123`)
2. Payment page displays order summary with total amount
3. Customer selects payment method:
   - 💳 Debit/Credit Card
   - 📱 UPI (Google Pay, PhonePe, etc.)
   - 💰 Wallet (PayTM, Amazon Pay, Mobikwik)
   - 🏦 Net Banking

4. Clicks **"Pay ₹[Amount]"** button
5. Razorpay popup opens with selected payment method
6. Payment processing happens securely by Razorpay
7. On success, order is marked as "Paid" and moved to kitchen

#### Payment Statuses
- **Pending** - Awaiting payment (default state)
- **Paid** - Payment successful, order sent to kitchen
- **Failed** - Payment declined or cancelled

#### Backend Flow
```
payment.php (form) 
    ↓
[User enters payment details in Razorpay popup]
    ↓
Razorpay validates and processes payment
    ↓
verify_payment.php (verification endpoint)
    ↓
Signature verification using HMAC-SHA256
    ↓
Update orders table: payment_status = 'Paid'
    ↓
Track order page shows updated status
```

#### Security
- **HTTPS only** - Never use HTTP for payments
- **Signature verification** - Ensures payment is authentic
- **Keys Security**:
  - Key ID: Safe to expose (used in frontend)
  - Key Secret: ALWAYS keep private (backend only)

#### Testing
Use Razorpay test keys for development:
- **Test Cards:** Available in [Razorpay docs](https://razorpay.com/docs/payments/payments-gateway/test-card-numbers/)
- **Test UPI:** Use any UPI ID format (e.g., test@okhdfcbank)

### Demo Fallback
For degree projects without live payment processing, track_order.php includes demo payment buttons:
- 💵 Pay with Cash (Demo)
- 💳 Pay with Card (Demo)
- 📱 Pay with UPI (Demo)

These buttons call `simulate_payment.php` to mark order as paid without actual payment.

---

## Feature 3: Real-time Notification System

### Files Created/Modified
- **New:** `js/notifications.js` - Notification engine
- **Modified:** `staff/chef_dashboard.php` - Integrated notifications
- **Modified:** `staff/barista_dashboard.php` - Integrated notifications
- **Modified:** `staff/waiter_dashboard.php` - Integrated notifications
- **Modified:** `staff/manager_dashboard.php` - Integrated notifications

### How It Works

#### Architecture
```
Notification System (js/notifications.js)
    ↓
Periodic API calls to api/get_notifications.php
    ↓
[Role-based filtering for orders]
    ↓
Sound Alert (Web Audio API)
Toast Notification (Custom)
Browser Notification (Native if allowed)
```

#### Features

**1. Real-time Alerts**
- Chef gets alerts for **cooking orders**
- Barista gets alerts for **beverage orders**
- Waiter gets alerts for **ready orders**
- Manager gets alerts for **service requests**

**2. Sound Alert**
- Automatic notification sound when new orders arrive
- Uses Web Audio API to generate pleasant "ding" sound
- Can be toggled on/off in settings

**3. Popup Notifications**
- **Browser Notification** (if permission granted):
  - Desktop notification with order details
  - Requires initial permission from user
- **Toast Notification** (fallback):
  - Slide-in toast message in bottom-right corner
  - Auto-dismisses after 8 seconds

**4. Settings Panel**
- Click **🔔 Notifications** button in top-right
- Toggle sound alerts on/off
- Toggle popup notifications on/off
- Adjust check interval (3-60 seconds)
- Settings persist in browser localStorage

#### Implementation Details

**Initialization:**
```javascript
// Automatically starts on page load for each staff role
const cafeNotifications = new CafeNotificationManager('Chef');
cafeNotifications.start();
```

**Check Interval:**
- Chef: 8 seconds (frequent checks for food orders)
- Barista: 8 seconds (frequent checks for beverages)
- Waiter: 10 seconds (ready orders)
- Manager: 10 seconds (service requests)

**API Endpoint:**
```
GET /api/get_notifications.php?staff_role=Chef&last_check=1708608000
```

Returns:
```json
{
  "success": true,
  "notifications": [
    {
      "id": 15,
      "table": 3,
      "title": "🔥 New Cooking Order!",
      "message": "Table #3",
      "timestamp": 1708608000
    }
  ],
  "timestamp": 1708608000
}
```

#### Browser Permissions
On first visit, the system requests notification permission:
- User can grant or deny
- Denied notifications fall back to toast style
- Can be re-enabled in browser settings

### Testing Notifications

**For Chef:**
1. Log in as Chef (any chef user)
2. Place an order with cooking items
3. Should see green alert badge on 🔔 icon
4. Should hear notification sound
5. Should see browser/toast notification

**For Barista:**
1. Log in as Barista
2. Place an order with beverages (Coffee, Tea, Drinks)
3. Same alert behavior as chef

**For Waiter:**
1. Kitchen marks an order as "Ready"
2. Waiter dashboard receives notification
3. Ready orders appear in their queue

**For Manager:**
1. Customer calls waiter (`call_waiter.php`)
2. Service request is created
3. Manager receives notification

---

## Configuration & Customization

### Notification Sounds
To use custom notification sound instead of Web Audio:

1. Place audio file at `/sounds/notification.mp3`
2. Update `notifications.js`:

```javascript
playNotificationSound() {
    if (!this.soundEnabled) return;
    const audio = new Audio('/sounds/notification.mp3');
    audio.volume = 0.5;
    audio.play().catch(e => console.log('Audio play failed'));
}
```

### Bill Template Customization
Edit `/bill.php` to customize:
- Cafe name and branding
- Tax percentage (currently 5%)
- Logo and header styling
- Footer message

### Payment Methods
To add more payment methods, update `payment.php`:
- Add more method buttons in grid
- Razorpay handles routing to specific options

---

## Troubleshooting

### Bills Not Displaying
- **Issue:** 404 on `bill.php?order_id=X`
- **Fix:** Ensure order exists in database with correct ID

### Email Not Sending
- **Issue:** Bill email button shows error
- **Fix:** 
  - Check PHP mail settings: `php.ini`
  - Or configure SMTP in PHPMailer
  - Test with simple mail() first

### Payment Fails Silently
- **Issue:** User clicks "Pay" but nothing happens
- **Fix:**
  - Check browser console for errors (F12 → Console)
  - Verify Razorpay keys are correct
  - Ensure HTTPS (or localhost for dev)
  - Check `verify_payment.php` logs

### No Notifications Appearing
- **Issue:** Staff dashboards show no alerts even after orders
- **Fix:**
  - Check `api/get_notifications.php` returns data:
    ```php
    // Direct test: http://localhost/QR_Code_Based_Cafe_Project/api/get_notifications.php?staff_role=Chef&last_check=1000000000
    ```
  - Enable browser notifications permission
  - Check browser console for JavaScript errors
  - Verify staff role is correct (case-sensitive)

### Sound Not Playing
- **Issue:** Notification sound disabled on some devices
- **Fix:**
  - User must have tab active (browser autoplay policy)
  - Check notifications settings (🔔 icon)
  - Some browsers block audio on muted tabs

---

## Database Updates Required

### For Payment System
```sql
ALTER TABLE orders ADD COLUMN payment_id VARCHAR(255);

CREATE TABLE IF NOT EXISTS payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_id VARCHAR(255),
    amount DECIMAL(10, 2),
    status VARCHAR(50),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);
```

### For Notifications
- Existing tables are sufficient
- Uses `orders`, `order_items`, `service_requests` tables

---

## Production Deployment Checklist

- [ ] Update Razorpay keys with live keys (not test keys)
- [ ] Configure HTTPS for payment page
- [ ] Set up SMTP for email functionality
- [ ] Test all payment methods before going live
- [ ] Configure PDF library for bill downloads (wkhtmltopdf or similar)
- [ ] Test notifications across different browsers
- [ ] Set up backup for payment logs table
- [ ] Configure error logging for payment failures
- [ ] Test bill printing on actual cafe printers
- [ ] Train staff on new notification system

---

## Support & Future Enhancements

### Potential Improvements
- SMS notifications to customers
- QR code on receipt linking to order tracking
- Digital loyalty/membership integration
- Multiple payment gateway support (Stripe, PayPal)
- Bulk bill export (CSV/Excel)
- Payment reconciliation dashboard for manager
- Advanced notification scheduling
- WhatsApp integration for customer updates

### Support Contact
For issues or questions, refer to:
- `README.md` - Project overview
- `TESTING_GUIDE.md` - Testing procedures
- Database schema in `/database/setup.sql`

---

**Last Updated:** 2026-02-21  
**Version:** 1.0 (Production Ready)  
**Features Implemented:** Bill Generation, Payment Gateway, Real-time Notifications
