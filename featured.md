# Milk Billing Manager (MBS) - Features, Architecture & Packages Specification

---

## 1. Project Overview

**Milk Billing Manager (MBS)** is a dedicated web application and Progressive Web App (PWA) tailored for dairy suppliers, milkmen, and milk distribution businesses in India. The application replaces traditional physical diary logs and error-prone manual arithmetic with a centralized, automated digital billing and ledger system.

- **Primary User**: Admin (Single-tenant administrative management; zero customer accounts)
- **Currency**: Indian Rupee (₹ - INR)
- **Tech Stack**: Laravel (PHP 8.2+), MySQL 8.x / MariaDB, Tailwind CSS, Alpine.js, Service Worker (PWA)
- **Access Channels**: Desktop Web Browser & Installable Mobile PWA (Android / iOS / Tablet)

---

## 2. Core Functional Modules & Feature Breakdown

### 2.1 Admin Authentication & Security
- **Secure Admin Login**: Authentication with email/username and hashed password using Bcrypt/Argon2id.
- **JWT Authentication**: Full JSON Web Token (JWT) support for API and PWA client interactions.
- **Session Management**: Secure HttpOnly cookie-based session alongside token authentication.
- **Forgot Password Flow**: Secure password reset mechanism via email verification tokens.
- **Admin Logout**: Complete token revocation and session invalidation.
- **Protected Perimeter**: Unauthenticated users cannot view customer lists, deliveries, bills, or settings.

### 2.2 Admin Dashboard & Business Analytics
- **KPI Summary Cards**:
  - Total Active Customers vs. Inactive Customers
  - Current Month Estimated Milk Volume (Litres)
  - Current Month Projected Revenue (₹)
  - Total Outstanding Balances across all customers (₹)
- **Live Exception Feed**: Daily logs highlighting holidays, extra milk, and reduced delivery entries.
- **Recent Invoices**: Quick status list of newly generated monthly bills.
- **Quick Action Bar**: One-click shortcuts for registering new customers, quick daily entry, and generating bulk monthly invoices.
- **Instant Search**: Quick-lookup customer bar by name or phone number.

### 2.3 Customer Management
- **Customer Information Fields**:
  - Unique Customer ID / Account Code (e.g., `MBS-001`)
  - Full Name
  - WhatsApp Number (formatted with country code `+91`)
  - Default Daily Milk Quantity (in litres, supporting decimals e.g., 1.5 L)
  - Rate per Litre (₹ / litre)
  - Registration Date (marks the initial billing start date)
  - Previous Pending Balance (carried forward opening balance)
  - Delivery Address (optional street, landmark, floor/flat number)
  - Delivery Notes / Preferences (e.g., "Leave at doorstep", "Morning 6:30 AM")
  - Status (`Active` or `Inactive`)
- **Customer Operations**:
  - Add new customer with initial opening balance and delivery preferences.
  - Edit customer profile, default rate, and default daily quantity.
  - Deactivate / Activate toggle (soft preservation of historical bills and logs).
  - Search and filter by active/inactive status, name, or phone.
  - Customer 360 profile showing complete ledger, calendar, and past invoices.

### 2.4 Calendar-Month Billing Engine & Rules
- **Calendar Month Cycle**: Standard billing starts on day 1 and concludes on the last calendar day (28/29 February, 30 or 31 for other months).
- **First Month Partial Billing**: Customers registering mid-month (e.g., 10th October) are billed strictly from their registration date (10th October) through the month's final day (31st October). Prior dates are marked inactive and billed at ₹0.
- **Subsequent Full Cycles**: All future billing periods automatically run from the 1st through the final day of each calendar month.
- **Automatic Default Quantity**: Days without explicit modifications inherit the customer's default daily milk quantity.
- **Historical Immutability**: Rate changes apply only to future unbilled dates or next cycles, never altering previously finalized bills.

### 2.5 Daily Milk Delivery Calendar & Exceptions
- **Interactive Monthly Grid**: Visual calendar interface for each customer showing every day of the selected month.
- **Visual Status Badges**:
  - Normal Day: Default volume badge (e.g., 2.0 L)
  - Holiday / No Delivery: Red indicator (0.0 L)
  - Reduced Quantity: Amber indicator (e.g., 1.0 L instead of 2.0 L)
  - Extra Quantity: Blue indicator (e.g., 2.0 L + 1.0 L extra = 3.0 L)
- **Click-to-Log Modal**: Quick tap on any date to open the exception modal:
  - Select delivery type (Normal, Holiday, Reduced, Extra)
  - Input actual quantity or extra volume
  - Add optional daily note
  - Save or Reset to default
- **Live Recalculation**: Projected monthly bill updates immediately whenever a calendar entry changes.
- **Non-Negative Validation**: Validation prevents negative milk volume entries.

### 2.6 Daily Delivery Types & Mathematical Formulations
1. **Normal Delivery**:
   - Volume = Default Daily Quantity
   - Daily Charge = `Default Quantity × Rate per Litre`
2. **Holiday / No Delivery**:
   - Volume = 0 Litres
   - Daily Charge = `₹0.00`
3. **Reduced Milk**:
   - Volume = Actual Delivered Quantity (< Default Quantity)
   - Daily Charge = `Actual Delivered Quantity × Rate per Litre`
4. **Extra Milk**:
   - Volume = `Default Daily Quantity + Extra Quantity`
   - Daily Charge = `(Default Daily Quantity + Extra Quantity) × Rate per Litre`

### 2.7 Bill Calculation & Finalization Engine
- **Monthly Milk Charges**:
  $$\text{Monthly Milk Charges} = \sum_{\text{day}=\text{start}}^{\text{end}} \text{Daily Charge}$$
- **Total Amount Due**:
  $$\text{Total Amount Due} = \text{Monthly Milk Charges} + \text{Previous Outstanding Balance} - \text{Payments/Credits}$$
- **Bill Snapshot Archiving**:
  - Every issued bill stores an immutable JSON snapshot containing itemized daily records, rates applied, quantities, balance carried forward, and calculation metadata.
  - Editing customer defaults afterwards does not alter historical bills.

### 2.8 PDF Invoice Generation
- **Downloadable Bills**: Clean, printable PDF invoice generated on demand.
- **Invoice Contents**:
  - Business / Dairy Name, Contact Information, Address, and UPI QR Code
  - Invoice Number (e.g., `INV-2026-10-0042`) and Generation Date
  - Customer Name, Customer Code, and WhatsApp Number
  - Billing Cycle Start Date and End Date
  - Itemized Summary: Total Normal Days, Total Holidays, Total Reduced Days, Total Extra Days
  - Total Milk Delivered (in litres) and Rate applied
  - Subtotal for Milk Charges
  - Previous Pending Balance
  - Payments / Advances Credited
  - Final Amount Payable (₹)
  - Bill Footer / Payment Instructions / Bank Details

### 2.9 WhatsApp Bill Sharing
- **1-Click Share**: Generates a pre-filled direct WhatsApp link (`https://wa.me/91XXXXXXXXXX?text=...`).
- **Dynamic Message Content**:
  - Customer greeting
  - Billing month & duration
  - Total milk quantity delivered (Litres)
  - Current month milk charges (₹)
  - Previous pending balance (₹)
  - Total amount due (₹)
  - Payment reminder & dairy business contact
- **Direct PDF Workflow**: Admin downloads the generated PDF with one click to attach directly in the opened WhatsApp chat.

### 2.10 Payment Tracking & Ledger Management
- **Record Payments**:
  - Amount Paid (₹)
  - Payment Date
  - Payment Method (`Cash`, `UPI`, `Bank Transfer`, `Cheque`)
  - Reference Number / Transaction ID / Notes
  - Associated Bill ID (optional)
- **Automatic Balance Adjustment**: Recorded payments automatically adjust the customer's outstanding balance without duplicate deductions.
- **Payment History Ledger**: Complete audit trail of all payments received per customer.

### 2.11 Application & Dairy Settings
- Business / Dairy Name (e.g., "Shree Krishna Dairy Farm")
- Primary Business Contact Number & WhatsApp Helpline
- Physical Address & Delivery Route
- UPI ID / VPA & Bank Account Details for customer bill payments
- Custom Invoice Header, Notes, and Bill Footer terms
- Default Currency Configuration (₹ INR)

### 2.12 Progressive Web App (PWA) Capabilities
- **Web App Manifest (`manifest.json`)**: Configured for standalone full-screen display, branded theme color, background color, and responsive icons (192x192, 512x512).
- **Service Worker**: Caches app shell, stylesheets, scripts, and fonts for instant loading.
- **Offline Fallback**: Displays an offline indicator when network connection drops.
- **Mobile-First UX**: Responsive interface optimized for touch interactions on smartphones and tablets.

---

## 3. Technology Stack & Packages Overview

| Layer | Technology / Package | Version Target | Primary Responsibility |
|---|---|---|---|
| **Backend Framework** | `laravel/framework` | `^10.x` / `^11.x` | Core MVC framework, routing, controllers, Eloquent ORM, middleware, service providers |
| **Language Runtime** | `PHP` | `8.2+` | Backend language runtime (XAMPP environment) |
| **Database** | `MySQL` / `MariaDB` | `8.x` / `10.4+` | Relational database storage and foreign key constraints |
| **Authentication (JWT)** | `php-open-source-saver/jwt-auth` | `^2.x` | JWT token generation, refresh, verification, and API route security |
| **PDF Generation** | `barryvdh/laravel-dompdf` | `^2.x` / `^3.x` | Server-side HTML-to-PDF rendering for branded invoices with ₹ symbol support |
| **Excel / CSV Export** | `maatwebsite/excel` | `^3.1` | Export customer directories, monthly consumption summaries, and ledger sheets |
| **CSS Styling** | `tailwindcss` | `^3.x` | Responsive UI styling, color tokens, layout grids, cards, modals |
| **Frontend Reactivity** | `alpinejs` | `^3.x` | Lightweight reactive components for calendar date modals, live calculation, dropdowns |
| **Icons** | `lucide` / SVG Icons | Latest | Clean vector iconography for delivery status indicators, nav links, buttons |
| **Asset Bundler** | `vite` | Latest | Asset compilation, CSS/JS minification, and hot module replacement |
| **PWA Service Worker** | Custom Service Worker + Manifest | Web Standard | App installation prompt, asset caching, offline fallback screen |

---

## 4. Package Utilization & Detailed Purpose

### 4.1 Backend Packages (PHP / Composer)

#### 1. `laravel/framework`
- **Use Case**: Serves as the central web application foundation.
- **Key Responsibilities**:
  - Handles HTTP routing, middleware pipeline, and CSRF protection.
  - Eloquent ORM for relationship handling (`Customer` has many `DailyDeliveryEntry`, `Customer` has many `Bill`, `Customer` has many `Payment`).
  - Request validation rules for non-negative milk quantities, phone numbers, and dates.
  - Password hashing and database migration versioning.

#### 2. `php-open-source-saver/jwt-auth`
- **Use Case**: Admin authentication token management for stateless REST APIs and PWA synchronization.
- **Key Responsibilities**:
  - Issues signed JSON Web Tokens upon verified admin login.
  - Protects private API endpoints via JWT middleware (`auth:api`).
  - Supports token refresh and blacklisting on logout.

#### 3. `barryvdh/laravel-dompdf`
- **Use Case**: Converting Blade invoice views into downloadable PDF documents.
- **Key Responsibilities**:
  - Generates print-ready A4 invoices with dairy branding.
  - Renders itemized tables, total milk counts, and payment details.
  - Embedded Unicode fonts for rendering the Indian Rupee symbol (`₹`).

#### 4. `maatwebsite/excel`
- **Use Case**: Exporting tabular reports for administrative accounting.
- **Key Responsibilities**:
  - Exports monthly delivery registers per customer or for all customers.
  - Generates downloadable CSV/XLSX summaries for accountant records.

### 4.2 Frontend Libraries & Tooling

#### 1. `tailwindcss`
- **Use Case**: Modern design system and utility-based styling.
- **Key Responsibilities**:
  - Implements a responsive, mobile-first design with high contrast and readable typography.
  - Handles color-coded delivery badges: Holiday (Rose/Red), Reduced (Amber/Yellow), Extra (Sky/Blue), Normal (Emerald/Green).
  - Responsive layouts tailored for desktop screens, tablets, and handheld smartphones.

#### 2. `alpinejs`
- **Use Case**: Client-side reactive interactions without heavy virtual DOM overhead.
- **Key Responsibilities**:
  - Controls date modal popups when tapping calendar cells.
  - Computes real-time bill previews as delivery entries are modified.
  - Manages toast alerts, confirmation dialogs, and mobile navigation toggles.

#### 3. `vite` & `@vitejs/plugin-vue` / Plugins
- **Use Case**: Frontend build pipeline and asset optimization.
- **Key Responsibilities**:
  - Compiles Tailwind CSS and modern JavaScript files.
  - Provides instant hot reloading during local development.

### 4.3 PWA & Native App Experience Components

#### 1. `manifest.json`
- **Use Case**: Web App Manifest defining installation parameters.
- **Key Responsibilities**:
  - Enables "Add to Home Screen" prompt on Android, iOS, and desktop browsers.
  - Configures `display: standalone` to eliminate browser address bars.
  - Defines app icons, launch orientations, and theme colors.

#### 2. `sw.js` (Service Worker)
- **Use Case**: Client-side network proxy and offline cache.
- **Key Responsibilities**:
  - Pre-caches core CSS, JavaScript, and app icons.
  - Provides smooth app loading even over slow 3G or unstable mobile networks.
  - Serves an offline fallback view when connectivity is lost.

---

## 5. Database Schema Architecture (MySQL)

```
admins
  ├── id (BIGINT, PK, AUTO_INCREMENT)
  ├── name (VARCHAR 100)
  ├── email (VARCHAR 150, UNIQUE)
  ├── password (VARCHAR 255)
  ├── remember_token (VARCHAR 100, NULLABLE)
  ├── created_at (TIMESTAMP)
  └── updated_at (TIMESTAMP)

customers
  ├── id (BIGINT, PK, AUTO_INCREMENT)
  ├── customer_code (VARCHAR 30, UNIQUE)
  ├── name (VARCHAR 150)
  ├── whatsapp_number (VARCHAR 20)
  ├── daily_quantity (DECIMAL 5,2)
  ├── rate_per_litre (DECIMAL 8,2)
  ├── registration_date (DATE)
  ├── previous_balance (DECIMAL 10,2, DEFAULT 0.00)
  ├── address (TEXT, NULLABLE)
  ├── notes (TEXT, NULLABLE)
  ├── status (ENUM ['active', 'inactive'], DEFAULT 'active')
  ├── created_at (TIMESTAMP)
  ├── updated_at (TIMESTAMP)
  └── deleted_at (TIMESTAMP, NULLABLE)

daily_deliveries
  ├── id (BIGINT, PK, AUTO_INCREMENT)
  ├── customer_id (BIGINT, FK -> customers.id, CASCADE)
  ├── delivery_date (DATE)
  ├── delivery_type (ENUM ['normal', 'holiday', 'reduced', 'extra'])
  ├── default_quantity (DECIMAL 5,2)
  ├── actual_quantity (DECIMAL 5,2)
  ├── extra_quantity (DECIMAL 5,2, DEFAULT 0.00)
  ├── rate_applied (DECIMAL 8,2)
  ├── daily_charge (DECIMAL 10,2)
  ├── note (VARCHAR 255, NULLABLE)
  ├── created_at (TIMESTAMP)
  └── updated_at (TIMESTAMP)
  └── UNIQUE KEY (customer_id, delivery_date)

bills
  ├── id (BIGINT, PK, AUTO_INCREMENT)
  ├── bill_number (VARCHAR 50, UNIQUE)
  ├── customer_id (BIGINT, FK -> customers.id, RESTRICT)
  ├── billing_month (VARCHAR 7)
  ├── period_start_date (DATE)
  ├── period_end_date (DATE)
  ├── total_milk_quantity (DECIMAL 8,2)
  ├── milk_charges (DECIMAL 10,2)
  ├── previous_balance (DECIMAL 10,2)
  ├── payments_credited (DECIMAL 10,2, DEFAULT 0.00)
  ├── amount_due (DECIMAL 10,2)
  ├── itemized_snapshot (LONGTEXT / JSON)
  ├── status (ENUM ['unpaid', 'partially_paid', 'paid'], DEFAULT 'unpaid')
  ├── generated_at (TIMESTAMP)
  ├── created_at (TIMESTAMP)
  └── updated_at (TIMESTAMP)
  └── UNIQUE KEY (customer_id, billing_month)

payments
  ├── id (BIGINT, PK, AUTO_INCREMENT)
  ├── customer_id (BIGINT, FK -> customers.id, CASCADE)
  ├── bill_id (BIGINT, NULLABLE, FK -> bills.id, SET NULL)
  ├── amount (DECIMAL 10,2)
  ├── payment_date (DATE)
  ├── payment_method (ENUM ['cash', 'upi', 'bank_transfer', 'cheque'])
  ├── transaction_ref (VARCHAR 100, NULLABLE)
  ├── notes (TEXT, NULLABLE)
  ├── created_at (TIMESTAMP)
  └── updated_at (TIMESTAMP)

app_settings
  ├── id (BIGINT, PK, AUTO_INCREMENT)
  ├── business_name (VARCHAR 150)
  ├── business_phone (VARCHAR 30)
  ├── business_email (VARCHAR 100, NULLABLE)
  ├── business_address (TEXT, NULLABLE)
  ├── upi_id (VARCHAR 100, NULLABLE)
  ├── bill_title (VARCHAR 100, DEFAULT 'Milk Delivery Invoice')
  ├── bill_footer (TEXT, NULLABLE)
  ├── currency_symbol (VARCHAR 10, DEFAULT '₹')
  ├── created_at (TIMESTAMP)
  └── updated_at (TIMESTAMP)
```

---

## 6. Project Directory Architecture

```
MBS/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── DeliveryCalendarController.php
│   │   │   ├── BillingController.php
│   │   │   ├── PaymentController.php
│   │   │   └── SettingsController.php
│   │   ├── Middleware/
│   │   │   └── AdminAuthenticate.php
│   │   └── Requests/
│   │       ├── CustomerRequest.php
│   │       ├── DeliveryEntryRequest.php
│   │       └── PaymentRequest.php
│   ├── Models/
│   │   ├── Admin.php
│   │   ├── Customer.php
│   │   ├── DailyDelivery.php
│   │   ├── Bill.php
│   │   ├── Payment.php
│   │   └── AppSetting.php
│   └── Services/
│       ├── BillingCalculationService.php
│       ├── PdfInvoiceService.php
│       └── WhatsAppShareService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── icons/
│   ├── manifest.json
│   └── sw.js
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── customers/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   └── show.blade.php
│       ├── calendar/
│       │   └── show.blade.php
│       ├── billing/
│       │   ├── index.blade.php
│       │   ├── preview.blade.php
│       │   └── pdf_invoice.blade.php
│       ├── payments/
│       │   └── index.blade.php
│       └── settings/
│           └── index.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── featured.md
└── composer.json
```

---

## 7. Implementation Roadmap

1. **Phase 1: Environment & Base Setup**
   - Initialize Laravel project and environment settings.
   - Configure MySQL database connection in `.env`.
   - Setup Composer dependencies (`jwt-auth`, `dompdf`, `excel`).
   - Setup Tailwind CSS, Alpine.js, and Vite asset bundling.

2. **Phase 2: Authentication & Admin Layout**
   - Implement Admin authentication routes, controllers, and views.
   - Configure JWT authentication guard and session middleware.
   - Setup responsive Admin sidebar, header, and PWA shell.

3. **Phase 3: Customer Management Module**
   - Migrations and models for customers.
   - Full CRUD: Add, list, search, filter, edit, and deactivate.
   - Auto-generated customer codes and validation.

4. **Phase 4: Delivery Calendar & Exception Logging**
   - Interactive calendar month view.
   - Normal, Holiday, Reduced, and Extra delivery handlers.
   - Dynamic modal for recording and updating delivery entries.
   - Live recalculation service.

5. **Phase 5: Billing Engine, PDF & WhatsApp Sharing**
   - Automated billing calculations honoring registration date and calendar months.
   - Immutable snapshot storage upon bill issuance.
   - PDF invoice generation using DomPDF with ₹ symbol styling.
   - WhatsApp message generator with direct chat launch.

6. **Phase 6: Payment Tracking & App Settings**
   - Payment recording and balance ledger.
   - Dairy profile, UPI ID, and bill footer customization.

7. **Phase 7: PWA Integration & Production Polish**
   - Configure `manifest.json` and install banners.
   - Register Service Worker for offline asset caching.
   - End-to-end testing of calendar edge cases and calculations.
