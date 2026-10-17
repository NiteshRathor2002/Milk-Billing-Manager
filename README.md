# Milk Billing Manager (MBS)

Milk Billing Manager (MBS) is a dedicated web application and Progressive Web App (PWA) tailored for dairy suppliers, milkmen, and milk distribution businesses in India. The application replaces physical diary logs and manual arithmetic with a centralized, automated digital billing and ledger system.

---

## Key Features

- **Admin-Only Management**: Single administrative role with zero customer access overhead. Bills are dispatched directly via WhatsApp and PDF.
- **Calendar-Month Billing Engine**:
  - **First-Month Partial Billing**: New customers are billed strictly from their registration date to the end of that calendar month (zero charges prior to registration).
  - **Full Subsequent Cycles**: Subsequent months run from the 1st day to the final day of each calendar month (leap-year compliant).
- **Interactive Daily Delivery Calendar**:
  - Four distinct delivery states: Normal Delivery (default quantity), Holiday / No Delivery (0L), Reduced Milk, and Extra Milk.
  - Quick click-to-edit exception modal.
  - Live projected bill recalculation on every entry.
- **Immutable Invoice Generation**:
  - Issued bills preserve an immutable JSON snapshot of all itemized delivery records, quantities, and rates.
  - Subsequent customer default rate updates never alter historical invoices.
- **Branded PDF Invoices & WhatsApp Sharing**:
  - Generates downloadable, printable A4 PDF invoices formatted in Indian Rupees (₹) with dairy branding and UPI QR/details.
  - 1-Click WhatsApp sharing with dynamic, pre-formatted message summaries.
- **Payment & Outstanding Balance Ledger**:
  - Record cash, UPI, bank transfer, and cheque receipts with transaction references.
  - Automatic ledger updates and balance carryover.
- **Progressive Web App (PWA)**:
  - Installable to desktop, Android, and iOS home screens.
  - Service worker caching for fast offline access.

---

## Tech Stack

- **Backend**: Laravel (PHP 8.2+)
- **Database**: MySQL 8.x / MariaDB
- **Frontend**: Tailwind CSS & Alpine.js
- **PDF Engine**: Barryvdh DomPDF
- **Authentication**: Stateful Sessions & JWT API Guard (Firebase PHP-JWT)
- **Asset Bundler**: Vite

---

## Installation & Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/NiteshRathor2002/Milk-Billing-Manager.git
   cd Milk-Billing-Manager
   ```

2. **Install PHP and Node dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration** (in `.env`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=milk_billing_manager
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

6. **Build Frontend Assets**:
   ```bash
   npm run build
   ```

7. **Start the Application**:
   ```bash
   php artisan serve
   ```

---

## Default Admin Credentials

- **URL**: `http://127.0.0.1:8000/login`
- **Email**: `admin@milkmanager.com`
- **Password**: `admin123`

---

## Automated Test Suite

Run the full automated test suite covering billing calculations, delivery exceptions, partial calendar months, PDF invoicing, and JWT authentication:

```bash
php artisan test
```
