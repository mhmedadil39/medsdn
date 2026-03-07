# Bank Transfer Payment Method for MedSDN

A comprehensive payment method package that enables customers to pay via bank transfer by uploading payment proof, with a complete admin review and approval workflow. Built specifically for MedSDN eCommerce platform with enterprise-grade security and multi-language support.

## Features

### Customer Features
- 💳 View configured bank account details at checkout
- 📤 Upload payment proof (JPG, PNG, WEBP, PDF up to 4MB)
- 🔢 Add optional transaction reference number
- 🖱️ Drag-and-drop file upload with preview
- 📋 Copy bank account details to clipboard
- 🌐 Multi-language support (English & Arabic)
- 📱 Responsive design for mobile devices

### Admin Features
- 📊 DataGrid listing of all bank transfer payments
- 🔍 Filter by status (pending, approved, rejected)
- 🔎 Search by order number or customer name
- 👁️ View payment proof securely with authentication
- ✅ Approve payments with optional notes
- ❌ Reject payments with required reason
- 📧 Automatic email notifications to customers
- 📝 Complete audit logging for compliance
- 🔐 Permission-based access control

### Security Features
- 🔒 Secure file storage outside public web root
- 🛡️ Comprehensive MIME type validation using finfo_file()
- 🚫 Path traversal prevention and filename sanitization
- 🔐 Authentication and authorization for file access
- ⏱️ Rate limiting on uploads (5 per minute per user)
- 🛡️ CSRF protection on all forms
- 📊 Content-based file validation (image/PDF headers)
- 🚨 Suspicious content detection
- 📝 Security audit logging with IP tracking
- 🔍 File integrity validation

## Installation

### Prerequisites
- PHP 8.2 or higher
- Laravel 11.0 or higher
- MedSDN 2.3 or higher
- MySQL 8.0 or higher
- Composer 2.0 or higher

### Step 1: Install Package

The package is included in MedSDN core. If installing manually:

```bash
# Navigate to your MedSDN root directory
cd /path/to/medsdn

# The package should already exist at packages/Webkul/BankTransfer/
# If not, clone or copy the package to this location
```

### Step 2: Register Package (if not already registered)

1. Add to `composer.json` autoload section:
```json
{
    "autoload": {
        "psr-4": {
            "Webkul\\BankTransfer\\": "packages/Webkul/BankTransfer/src"
        }
    }
}
```

2. Add to `config/concord.php` modules array:
```php
'modules' => [
    // ... other modules
    \Webkul\BankTransfer\Providers\ModuleServiceProvider::class,
],
```

### Step 3: Run Migrations

```bash
# Regenerate autoload files
composer dump-autoload

# Run database migrations
php artisan migrate

# Clear all caches
php artisan optimize:clear

# Publish assets (if needed)
php artisan vendor:publish --tag=bank-transfer-assets
```

### Step 4: Configure Storage

Ensure the private storage disk is configured in `config/filesystems.php`:

```php
'disks' => [
    // ... other disks
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
    ],
],
```

Create the storage directory:
```bash
mkdir -p storage/app/private/bank-transfers
chmod 755 storage/app/private/bank-transfers
```

## Configuration

### Admin Panel Configuration

1. Log in to the **Admin Panel**
2. Navigate to **Configuration → Sales → Payment Methods → Bank Transfer**
3. Configure the following settings:

#### Basic Settings
- **Active**: Enable/disable the payment method
- **Title**: Display name for customers (supports multi-language)
- **Description**: Brief description shown at checkout (supports multi-language)
- **Sort Order**: Display order among other payment methods
- **Transfer Instructions**: Detailed instructions for customers

#### Bank Account Configuration

Configure up to 3 bank accounts. For each account, provide:

- **Bank Name**: Name of the bank (required)
- **Branch Name**: Branch location (optional)
- **Account Holder Name**: Name on the account (required)
- **Account Number**: Bank account number (required)
- **IBAN**: International Bank Account Number (optional)

**Note**: At least one bank account must be fully configured for the payment method to be available at checkout.

### Example Configuration

```
Bank Account 1:
- Bank Name: National Bank
- Branch Name: Main Branch
- Account Holder: MedSDN Store
- Account Number: 1234567890
- IBAN: SA1234567890123456789012

Transfer Instructions:
Please transfer the exact order amount to one of the bank accounts listed above.
After completing the transfer, upload your payment receipt or bank slip.
Include your order number in the transfer reference if possible.
Your order will be processed after payment verification (usually within 24 hours).
```

### Permission Configuration

Grant admin users access to bank transfer management:

1. Navigate to **Settings → Roles**
2. Edit the desired role
3. Enable permission: **Sales → Bank Transfers**

### Email Configuration

Ensure your email settings are configured in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Configuration (Recommended)

For better performance, configure queue workers:

```env
QUEUE_CONNECTION=database
```

Run queue worker:
```bash
php artisan queue:work --queue=default
```

## Usage

### Customer Workflow

1. **Checkout**: Customer selects "Bank Transfer" as payment method
2. **View Bank Details**: Customer sees configured bank account information
3. **Make Transfer**: Customer transfers money to one of the provided accounts
4. **Upload Proof**: Customer uploads payment receipt/slip (JPG, PNG, WEBP, or PDF)
5. **Add Reference** (Optional): Customer enters transaction reference number
6. **Submit Order**: Order is created with "pending" payment status
7. **Confirmation**: Customer receives order confirmation email

### Admin Workflow

1. **Notification**: Admin receives email about new payment submission
2. **Review**: Admin navigates to **Sales → Bank Transfers**
3. **View Details**: Admin clicks on payment to view order and proof
4. **Verify**: Admin downloads and verifies the payment proof
5. **Decision**:
   - **Approve**: Payment is marked as approved, order status updated, customer notified
   - **Reject**: Payment is marked as rejected with reason, customer notified
6. **Audit**: All actions are logged for compliance

### File Upload Guidelines for Customers

**Accepted Formats:**
- Images: JPG, JPEG, PNG, WEBP
- Documents: PDF

**File Size:**
- Maximum: 4MB per file

**Best Practices:**
- Ensure the image is clear and readable
- Include all relevant transaction details
- Avoid uploading edited or modified receipts
- Use original bank-generated receipts when possible

### Admin Review Guidelines

**Approval Criteria:**
- Payment amount matches order total
- Transaction date is recent and reasonable
- Bank account matches configured accounts
- Receipt appears genuine and unaltered

**Rejection Reasons:**
- Incorrect payment amount
- Unclear or unreadable receipt
- Suspicious or altered document
- Payment to wrong account
- Expired or old transaction

## Package Structure

```
packages/Webkul/BankTransfer/
├── src/
│   ├── Config/
│   │   ├── acl.php                    # Access control permissions
│   │   ├── menu.php                   # Admin menu configuration
│   │   ├── payment-methods.php        # Payment method registration
│   │   └── system.php                 # System configuration fields
│   ├── Contracts/
│   │   └── BankTransferPayment.php    # Model contract interface
│   ├── Database/
│   │   ├── Factories/
│   │   │   └── BankTransferPaymentFactory.php  # Test data factory
│   │   └── Migrations/
│   │       └── xxxx_create_bank_transfer_payments_table.php
│   ├── DataGrids/
│   │   └── BankTransferDataGrid.php   # Admin listing grid
│   ├── Events/
│   │   ├── PaymentApproved.php        # Payment approved event
│   │   ├── PaymentProofUploaded.php   # Proof uploaded event
│   │   └── PaymentRejected.php        # Payment rejected event
│   ├── Helpers/
│   │   └── FileHelper.php             # File handling utilities
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── BankTransferController.php  # Admin actions
│   │   │   └── Shop/
│   │   │       └── BankTransferController.php  # Customer actions
│   │   ├── Middleware/
│   │   │   └── ProtectPaymentProof.php  # File access protection
│   │   └── Requests/
│   │       ├── PaymentProofRequest.php  # Upload validation
│   │       └── ReviewRequest.php        # Review validation
│   ├── Jobs/
│   │   ├── NotifyAdminNewPayment.php
│   │   ├── SendPaymentApprovedNotification.php
│   │   └── SendPaymentRejectedNotification.php
│   ├── Listeners/
│   │   ├── NotifyAdminListener.php
│   │   ├── SendApprovalEmail.php
│   │   └── SendRejectionEmail.php
│   ├── Mail/
│   │   ├── NewPaymentForReview.php    # Admin notification email
│   │   ├── PaymentApproved.php        # Customer approval email
│   │   └── PaymentRejected.php        # Customer rejection email
│   ├── Models/
│   │   └── BankTransferPayment.php    # Eloquent model
│   ├── Payment/
│   │   └── BankTransfer.php           # Payment method class
│   ├── Providers/
│   │   ├── BankTransferServiceProvider.php  # Main service provider
│   │   ├── EventServiceProvider.php         # Event registration
│   │   └── ModuleServiceProvider.php        # Concord registration
│   ├── Repositories/
│   │   └── BankTransferRepository.php  # Data access layer
│   ├── Resources/
│   │   ├── assets/
│   │   │   └── css/
│   │   │       └── bank-transfer.css  # Package styles
│   │   ├── lang/
│   │   │   ├── ar/
│   │   │   │   └── app.php            # Arabic translations
│   │   │   └── en/
│   │   │       └── app.php            # English translations
│   │   └── views/
│   │       ├── admin/
│   │       │   ├── index.blade.php    # Payment listing
│   │       │   └── view.blade.php     # Payment details
│   │       ├── emails/
│   │       │   ├── admin/
│   │       │   │   └── new-payment.blade.php
│   │       │   └── customer/
│   │       │       ├── payment-approved.blade.php
│   │       │       └── payment-rejected.blade.php
│   │       └── shop/
│   │           └── checkout/
│   │               ├── onepage/
│   │               │   └── banktransfer.blade.php  # Checkout view
│   │               └── upload.blade.php            # Upload page
│   └── Routes/
│       ├── admin-routes.php           # Admin routes
│       └── shop-routes.php            # Shop routes
├── tests/
│   └── Unit/
│       └── FileHelperSecurityTest.php # Security tests
├── composer.json
└── README.md
```

## Security Considerations

### File Upload Security
- **MIME Type Validation**: Server-side validation using `finfo_file()`
- **Extension Whitelist**: Only JPG, JPEG, PNG, WEBP, PDF allowed
- **Size Limit**: Maximum 4MB enforced server-side
- **Filename Sanitization**: Removes special characters, path traversal attempts, null bytes
- **Content Validation**: Checks image/PDF headers and content integrity
- **Malicious Content Detection**: Scans for embedded scripts, suspicious patterns
- **Storage Location**: Files stored outside public web root in `storage/app/private/`

### Access Control
- **Authentication Required**: Only authenticated admins can access payment proofs
- **Permission-Based**: Requires `sales.bank_transfers` permission
- **Middleware Protection**: `ProtectPaymentProof` middleware on file routes
- **Audit Logging**: All file access attempts logged with IP address and user ID

### Rate Limiting
- **Upload Endpoint**: 5 uploads per minute per user
- **HTTP 429**: Returns "Too Many Requests" when limit exceeded

### CSRF Protection
- All forms include CSRF tokens
- Laravel's CSRF middleware validates all POST requests

### Data Protection
- **Sensitive Data**: No sensitive bank account numbers logged
- **Encryption**: Files can be encrypted at rest (optional)
- **Audit Trail**: Complete logging of all approve/reject actions

## License

MIT License

## Support

For issues and feature requests, please contact support@webkul.com


## Troubleshooting

### Payment Method Not Showing at Checkout

**Possible Causes:**
1. Payment method is not enabled in configuration
2. No bank accounts configured
3. Cache not cleared after configuration

**Solutions:**
```bash
# Clear all caches
php artisan optimize:clear

# Verify configuration
php artisan config:show sales.payment_methods.banktransfer
```

### File Upload Fails

**Possible Causes:**
1. File size exceeds 4MB limit
2. Invalid file type
3. Storage directory permissions
4. PHP upload limits

**Solutions:**
```bash
# Check storage permissions
chmod 755 storage/app/private/bank-transfers

# Check PHP limits in php.ini
upload_max_filesize = 4M
post_max_size = 5M
max_file_uploads = 20
```

### Emails Not Sending

**Possible Causes:**
1. Mail configuration incorrect
2. Queue not running
3. Email service down

**Solutions:**
```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Check queue jobs
php artisan queue:work --once

# Check logs
tail -f storage/logs/laravel.log
```

### Admin Cannot Access Payment Proofs

**Possible Causes:**
1. Missing permission
2. Middleware not applied
3. File not found

**Solutions:**
1. Grant `sales.bank_transfers` permission to admin role
2. Verify middleware in routes
3. Check file exists in storage

## Development

### Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest packages/Webkul/BankTransfer/tests/Unit/FileHelperSecurityTest.php

# Run with coverage
./vendor/bin/pest --coverage
```

### Code Style

```bash
# Fix code style
./vendor/bin/pint packages/Webkul/BankTransfer/
```

### Database

```bash
# Create migration
php artisan make:migration create_bank_transfer_payments_table

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback

# Fresh migration
php artisan migrate:fresh
```

## API Reference

### Events

#### PaymentProofUploaded
Dispatched when a customer uploads payment proof.

**Properties:**
- `BankTransferPayment $payment`
- `Order $order`

#### PaymentApproved
Dispatched when admin approves a payment.

**Properties:**
- `BankTransferPayment $payment`
- `Admin $admin`

#### PaymentRejected
Dispatched when admin rejects a payment.

**Properties:**
- `BankTransferPayment $payment`
- `Admin $admin`
- `string $note`

### Repository Methods

```php
// Create payment record
$payment = $repository->create([
    'order_id' => $orderId,
    'customer_id' => $customerId,
    'method_code' => 'banktransfer',
    'transaction_reference' => 'TXN123',
    'slip_path' => 'path/to/file.jpg',
    'status' => 'pending',
]);

// Find payment
$payment = $repository->find($id);
$payment = $repository->findByOrderId($orderId);

// Get pending payments
$pending = $repository->getPending();

// Approve payment
$repository->approve($id, $adminId, 'Optional note');

// Reject payment
$repository->reject($id, $adminId, 'Required rejection reason');

// Get paginated list
$payments = $repository->getList([
    'status' => 'pending',
    'search' => 'order number',
]);
```

## Changelog

### Version 1.0.0 (Current)
- ✅ Complete payment method implementation
- ✅ Admin review workflow
- ✅ Email notifications
- ✅ Multi-language support (English & Arabic)
- ✅ Enterprise-grade security
- ✅ Comprehensive file validation
- ✅ Audit logging
- ✅ DataGrid with filters
- ✅ Responsive design
- ✅ RTL support for Arabic

## Roadmap

### Version 1.1.0 (Planned)
- [ ] Multiple file uploads per order
- [ ] Automated bank statement reconciliation
- [ ] SMS notifications
- [ ] Payment proof OCR for automatic verification
- [ ] Bulk approve/reject actions
- [ ] Export payments to CSV/Excel
- [ ] Advanced reporting and analytics

### Version 1.2.0 (Planned)
- [ ] Integration with bank APIs for automatic verification
- [ ] QR code generation for bank transfers
- [ ] Partial payment support
- [ ] Payment installments
- [ ] Refund workflow

## Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Add translations for new strings

## License

MIT License

Copyright (c) 2024 Webkul Software

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

## Support

### Documentation
- [MedSDN Documentation](https://medsdn.com/docs)
- [Laravel Documentation](https://laravel.com/docs)

### Community
- [GitHub Issues](https://github.com/webkul/medsdn/issues)
- [Community Forum](https://forums.webkul.com)
- [Discord Channel](https://discord.gg/medsdn)

### Commercial Support
For commercial support, custom development, or enterprise features:
- Email: support@webkul.com
- Website: https://webkul.com

## Credits

Developed by [Webkul](https://webkul.com) for the MedSDN eCommerce platform.

### Contributors
- Development Team: Webkul Software
- Security Review: Webkul Security Team
- Documentation: Webkul Technical Writers

## Acknowledgments

- Laravel Framework
- MedSDN Platform
- Konekt Concord Package Manager
- All contributors and testers

---

**Made with ❤️ by Webkul**
