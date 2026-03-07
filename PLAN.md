# خطة تطوير `BankTransfer` وبناء `Wallet + Payments` داخل MedSDN

## Summary
- سيتم الإبقاء على `packages/medsdn/BankTransfer` كـ package مستقلة تمثل **وسيلة الدفع اليدوية** وتفاصيل الحسابات البنكية ورفع الإشعارات، لكن سيتم نقل منطق الدفع العام والمراجعة والتسوية إلى `packages/medsdn/Payment`.
- سيتم إنشاء package جديدة `packages/medsdn/Wallet` لتكون مصدر الحقيقة للرصيد والـ ledger والحجز والتسويات.
- نطاق `v1` ثابت: **طلبات Orders + شحن المحفظة Wallet Topups فقط**، مع بنية عامة قابلة لربط `services/subscriptions` لاحقًا بدون إعادة تصميم.
- المحفظة في `v1` هي **محفظة واحدة لكل Customer** فقط، وبعملة النظام الأساسية فقط. الدفع من المحفظة يكون على `base_grand_total` فقط، وإذا اختلفت العملة الأساسية للطلب عن عملة المحفظة تُخفى وسيلة الدفع بالمحفظة.
- واجهات `Blade/Admin` و`MedsdnApi` و`GraphQLAPI` كلها تدخل في النطاق، لكن **المراجعة الإدارية اليدوية تبقى من خلال لوحة التحكم فقط** في `v1`، بينما الـ API يغطي تدفقات العميل.

## Current-State Analysis
- `BankTransfer` الحالية قوية من ناحية التكامل السريع: لديها `payment method config`, `system config`, رفع ملف إثبات، `Admin DataGrid`, بريد للإشعارات، وواجهات `shop/admin/api`.
- لكن تصميمها الحالي **order-first**: الـ controller ينشئ الطلب مباشرة ثم ينشئ `bank_transfer_payments` بحالة `pending`. عند اعتماد الأدمن يتم فقط تحديث سجل `bank_transfer_payments` إلى `approved/rejected` بدون تسوية مالية عامة، وبدون ledger، وبدون ضمان idempotency على مستوى business fulfillment.
- `Payment` الحالية ليست payment domain كاملة؛ هي registry/facade لطرق الدفع. هذا يجعلها المكان الصحيح لتوسيع orchestration بدل اختراع package ثالثة للدفع.
- `Sales` تعتمد على `order_payment` كسجل snapshot للطريقة المختارة، وتوليد الـ order نفسه يتم عبر `OrderRepository`. هذا الجدول يجب أن يبقى للتوافق، لكن **لن يكون مصدر الحقيقة المالي** بعد الآن.
- `MedsdnApi` و`GraphQLAPI` يملكان أصلًا surface خاصًا بـ `banktransfer` و`checkout payment method`، لذلك التوسعة يجب أن تحافظ على التوافق وتحوّل الـ endpoints الحالية لتستهلك services عامة جديدة بدل منطق package القديم.

## Key Changes

### 1. Package Boundaries
- `packages/medsdn/Payment` تصبح payment orchestration package:
  - generic `payments` model/domain
  - status lifecycle
  - manual review services
  - order settlement services
  - integration with checkout/order creation
  - shared notifications/events/audit for payment lifecycle
- `packages/medsdn/Wallet` package جديدة:
  - `Wallet`, `WalletTransaction`, `WalletHold`
  - wallet services/actions
  - customer/admin pages
  - policies + notifications
  - REST/GraphQL storefront exposure
- `packages/medsdn/BankTransfer` تبقى method plugin:
  - bank account configuration
  - receipt metadata and file handling
  - compatibility routes/endpoints/views
  - bank-transfer-specific admin/customer presentation
  - delegates review/settlement to `Payment` services بدل repository-local status flips

### 2. Database Design
- إضافة جدول `wallets`:
  - `id`, `customer_id` unique, `currency`, `balance`, `available_balance`, `held_balance`, `status`, timestamps
- إضافة جدول `wallet_transactions`:
  - `id`, `wallet_id`, `customer_id`, `type`, `direction`, `amount`, `balance_before`, `balance_after`, `status`, `reference_type`, `reference_id`, `source`, `description`, `meta`, `created_by_type`, `created_by_id`, `entry_key` unique, timestamps
- إضافة جدول `wallet_holds`:
  - `id`, `wallet_id`, `customer_id`, `reference_type`, `reference_id`, `amount`, `status`, `expires_at`, `released_at`, `meta`, timestamps
- إضافة جدول `payments` كمصدر الحقيقة العام:
  - `id`, `customer_id`, nullable `payable_type`, nullable `payable_id`, `payment_method`, `purpose`, `amount`, `currency`, `status`, `settlement_key` unique, `external_reference`, `bank_name`, `notes`, `admin_notes`, `reviewed_by`, `reviewed_at`, `approved_at`, `rejected_at`, `paid_at`, `fulfilled_at`, `rejection_reason`, `meta`, timestamps
- عدم إضافة جدول `payment_methods` لأن المشروع يعتمد حاليًا على config-driven registry داخل `Payment` ويجب الحفاظ على هذا النمط.
- إعادة هيكلة `bank_transfer_payments` بدل حذفها:
  - إضافة `payment_id` unique FK إلى `payments`
  - الإبقاء على الحقول الخاصة بالطريقة فقط: `transaction_reference`, `slip_path` أو الأفضل تقسيمها إلى `receipt_disk`, `receipt_path`, `receipt_name`, `receipt_mime`, `receipt_size`, `bank_account_key`
  - ترحيل البيانات الحالية من السجل القديم إلى `payments` عبر migration/backfill، بحيث تتحول السجلات التاريخية الحالية إلى generic payments linked to orders
- عدم كسر `order_payment`; يبقى موجودًا ويُحدَّث `additional` JSON ليحمل `payment_id`, `payment_status`, وبيانات المرجع عند الحاجة للتوافق مع `Sales/Admin/Shop`

### 3. Statuses, Enums, and Idempotency
- Enums إلزامية داخل `Payment` و`Wallet`:
  - `WalletStatus`, `WalletTransactionType`, `WalletTransactionStatus`, `WalletHoldStatus`
  - `PaymentStatus`, `PaymentPurpose`, `PaymentMethodCode`
- حالات `payments` في `v1`:
  - `pending_review` للدفعات اليدوية
  - `approved` كاعتماد إداري قبل fulfillment
  - `paid` بعد نجاح التسوية وتطبيق الأثر المالي/التجاري
  - `rejected`, `failed`, `cancelled`, `refunded`
  - `pending` يُستخدم فقط لدفعات future gateways أو intents غير اليدوية
- منع التكرار يتم عبر:
  - `payments.settlement_key` unique لكل logical payment
  - `wallet_transactions.entry_key` unique لكل ledger entry
  - `lockForUpdate()` على `payments` و`wallets`
  - services ترفض أي settlement إذا كان `fulfilled_at` غير فارغ
- اعتماد اليدوي:
  - `ApproveManualPaymentAction` ينقل الدفع إلى `approved` ثم داخل نفس transaction ينفذ settlement المناسبة ثم يوسمه `paid` و`fulfilled_at`
  - أي failure أثناء fulfillment يRollback بالكامل ولا يترك الدفع في حالة نصفية
- رفض الدفع:
  - `RejectManualPaymentAction` يحدد `rejected` + `rejection_reason` + reviewer metadata بدون أي لمس للمحفظة أو الطلب

### 4. Business Flows
- **Wallet topup via bank transfer**
  - العميل ينشئ topup payment purpose=`wallet_topup`
  - method=`banktransfer`
  - يتم إنشاء `payments.pending_review`
  - يتم حفظ receipt metadata في `bank_transfer_payments`
  - عند اعتماد الأدمن: `CreditWalletAction` + `CreateWalletTransactionAction` + `payments.paid`
- **Order payment via bank transfer**
  - checkout يستمر باستخدام payment method الحالية `banktransfer`
  - order يُنشأ بحالة `pending_payment`
  - generic `payments.pending_review` تُنشأ وترتبط بالـ order
  - لا invoice ولا fulfillment مالي قبل الاعتماد
  - عند اعتماد الأدمن: `MarkOrderPaidAction` + إنشاء invoice عبر `InvoiceRepository` + `payments.paid`
- **Order payment via wallet**
  - وسيلة `wallet` تضاف إلى registry الحالية في `Payment`
  - لا تظهر إلا للعميل الموثق الذي يملك محفظة active ورصيدًا كافيًا
  - عند place order أو عند دفع order غير مدفوع: يتم `DebitWalletAction` + `wallet ledger` + `payments.paid` + إنشاء invoice + تحديث order إلى paid path
  - في حال عدم كفاية الرصيد يفشل الطلب atomically بدون أي خصم جزئي
- **Refund/Reverse readiness**
  - `wallet_transactions` و`payments` تُصمم لتقبل `refund` و`reversal` لاحقًا عبر `reference_type/id` و`entry_key`
  - `wallet_holds` موجودة من الآن لدعم الحجز/الفك والتوسع المستقبلي

### 5. Services / Actions
- في `Wallet`:
  - `WalletService`
  - `CreditWalletAction`
  - `DebitWalletAction`
  - `HoldWalletFundsAction`
  - `ReleaseWalletHoldAction`
  - `AdjustWalletBalanceAction`
  - `CreateWalletTransactionAction`
- في `Payment`:
  - `PaymentService`
  - `CreatePaymentAction`
  - `CreateWalletTopupPaymentAction`
  - `CreateOrderPaymentAction`
  - `ApproveManualPaymentAction`
  - `RejectManualPaymentAction`
  - `PayOrderWithWalletAction`
  - `SettleWalletTopupPaymentAction`
  - `SettleOrderPaymentAction`
- في `BankTransfer`:
  - `StoreBankTransferReceiptAction`
  - `BankTransferConfigResolver`
  - controllers/repositories الحالية تتحول إلى thin adapters تستدعي services العامة

### 6. UI / Admin / Shop
- **Shop Blade**
  - صفحة محفظتي: summary + available/held/current + آخر الحركات
  - صفحة سجل المحفظة paginated
  - نموذج شحن المحفظة مع اختيار `banktransfer`
  - صفحة/جزء لعرض payment status والتفاصيل
  - إضافة خيار `wallet` في checkout بنفس stack الحالية
- **Admin**
  - `sales.payments` DataGrid عامة للفلاتر: status, customer, amount, date, purpose, method
  - الإبقاء على `sales.bank_transfers` كاختصار filtered على method=`banktransfer`
  - شاشة تفاصيل payment فيها: timeline, receipt, payable info, review actions
  - شاشة wallet per customer وسجل ledger وتسويات إدارية ACL-protected
- **Routes**
  - Shop routes للمحفظة والدفعات الجديدة داخل stack `shop/customer`
  - Admin routes داخل `admin` مع ACL جديدة لـ `sales.payments`, `customers.wallets`, `customers.wallet_transactions`, `customers.wallet_adjustments`
  - `BankTransfer` الحالية تبقى متوافقة، لكن منطقها يتحول إلى facade على services الجديدة

### 7. API and GraphQL
- `MedsdnApi` في `v1` يغطي storefront/customer flows:
  - wallet summary
  - wallet transactions
  - create wallet topup payment
  - upload bank transfer receipt
  - list/show customer payments
  - pay order with wallet
- endpoints الحالية تحت `api/bank-transfer/*` تبقى compatibility aliases فوق services الجديدة
- تضاف endpoints generic تحت `/api/wallet/*` و`/api/payments/*`
- `GraphQLAPI` يضيف:
  - queries: `wallet`, `walletTransactions`, `payments`, `payment`
  - mutations: `createWalletTopupPayment`, `uploadBankTransferReceipt`, `payOrderWithWallet`
- admin manual review لا يضاف كـ public API في `v1`; يبقى داخل Admin UI فقط مع shared domain services

### 8. Notifications, Uploads, and Security
- receipts تُخزن على disk خاص غير public مع أسماء آمنة ومسارات مرتبة حسب `payment/{id}/receipts`
- access إلى receipt يكون عبر admin controller/proxy أو signed URL داخلي، وليس direct public path
- تحويل mail jobs الحالية في `BankTransfer` إلى Laravel Notifications موحدة:
  - admin notified on new manual payment
  - customer notified on received / approved / rejected / wallet credited / wallet paid
  - database notifications تستخدم حيث يدعم النظام الحالي، مع mail كقناة رئيسية
- Policies:
  - customer لا يرى إلا محفظته ودفعاته
  - admin actions محكومة ACL + policies
- audit/logging:
  - domain events: `PaymentCreated`, `ManualPaymentSubmitted`, `PaymentApproved`, `PaymentRejected`, `WalletCredited`, `WalletDebited`, `OrderPaidWithWallet`
  - structured logs تشمل actor, status transition, payment id, wallet id, exception context

## Public Interfaces / Contracts
- إضافة payment method جديد `wallet` إلى config registry الحالية في `Payment`
- `BankTransfer` تبقى public method code = `banktransfer`
- `payments` تصبح source of truth للدفع اليدوي والدفع بالمحفظة
- `wallets`, `wallet_transactions`, `wallet_holds` public internal domain contracts جديدة
- `order_payment` لا يُكسر؛ يبقى compatibility snapshot
- الـ REST/GraphQL الحالية لـ `banktransfer` تبقى متوافقة، مع إضافة generic wallet/payment surfaces

## Test Plan
- **Migration and Backfill**
  - ترحيل بيانات `bank_transfer_payments` التاريخية إلى `payments` بدون فقدان status/reviewer/receipt linkage
  - تأكيد uniqueness على `payment_id`, `settlement_key`, `entry_key`
- **Unit**
  - `WalletService`
  - `CreditWalletAction`, `DebitWalletAction`, `Hold/Release`
  - `ApproveManualPaymentAction`, `RejectManualPaymentAction`
  - `PayOrderWithWalletAction`
  - idempotency: منع double approval, double settlement, double spending
- **Feature Web**
  - إنشاء topup bank transfer
  - رفع receipt
  - ظهورها في admin grid
  - اعتمادها يضيف رصيدًا مرة واحدة فقط
  - رفضها لا يضيف رصيدًا ويخزن السبب
  - الدفع من المحفظة لطلب ناجح
  - فشل الدفع عند عدم كفاية الرصيد
- **Feature API**
  - REST wallet summary/history/topup/payment status
  - REST bank-transfer compatibility endpoints still work
  - GraphQL queries/mutations الجديدة
- **Authorization**
  - customer cannot access other customer payments/wallets
  - admin review guarded by ACL
- **Regression**
  - checkout التقليدي لباقي methods لا يتأثر
  - `cashondelivery` و`moneytransfer` invoice generation remain unchanged
  - `banktransfer` الحالية في Shop/Admin/API لا تنكسر على مستوى surface

## Assumptions and Defaults
- `v1` لا يشمل ربط concrete `services/subscriptions`; فقط `orders + wallet_topups` مع generic payable-ready design
- المحفظة `customers only`
- عملة المحفظة واحدة وثابتة = base currency عند إنشاء المحفظة
- wallet method لا تُعرض إلا إذا طابقت عملة الطلب الأساسية عملة المحفظة وكان الرصيد كافيًا
- admin review تبقى من لوحة التحكم فقط في `v1`
- لا يتم إدخال stack جديدة؛ التنفيذ يبقى ضمن `Blade + Vue fragments + DataGrid + MedsdnApi + GraphQLAPI`
- أفضل مسار تنفيذ: يبدأ بتحويل `Payment` إلى orchestration domain، ثم إنشاء `Wallet`, ثم refactor `BankTransfer` فوقها، ثم توصيل `Shop/Admin/API`, ثم backfill/testing
